<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Mail;
use core\security\factor\EmailOtpKey;
use core\security\factor\TotpKey;
use core\setting\Setting;
use core\User;
use equal\email\EmailMessage;
use equal\html\HtmlTemplate;

[$params, $providers] = eQual::announce([
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "The user name, username or login.",
            'type'          => 'string',
            'required'      => true
        ],
        'password' =>  [
            'description'   => "The user login.",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'public'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME', 'EMAIL_SMTP_ABUSE_EMAIL'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

// we might have received either a login (email) or a username

// if provided login is an email address, attempt to resolve by login
if(strpos($params['login'], '@') > 0) {
    // cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
    list($username, $domain) = explode('@', strtolower(trim($params['login'])));
    $username .= '+';
    $login = substr($username, 0, strpos($username, '+')).'@'.$domain;
}
// other format: attempt to resolve through username
else {
    // find a user that matches the given username (there should be only one)
    $user = User::search(['username', '=', $params['login']])->read(['login'])->first();
    if(!$user) {
        throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
    }
    $login = $user['login'];
}

// #memo - email might still be invalid (a validation check is made in User class)
$auth->authenticate($login, $params['password']);

$user_id = $auth->userId();

if(!$user_id) {
    // this is a fallback exception, but we should never reach this code, since user has been found by authenticate method
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

$user = User::id($user_id)
    ->read(['validated', 'allow_auth', 'login', 'firstname'])
    ->first(true);

if(!$user || !$user['validated']) {
    throw new Exception('user_not_validated', EQ_ERROR_NOT_ALLOWED);
}

if(!$user['allow_auth']) {
    throw new Exception('not_allowed', EQ_ERROR_NOT_ALLOWED);
}

$totp_required = false;
$email_otp_required = false;

$global_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
$totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled', $global_totp_enabled, ['user_id' => $user['id']]);
if($totp_enabled) {
    $global_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
    $totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required', $global_totp_required, ['user_id' => $user['id']]);
    if(!$totp_required) {
        // check if user configured a totpkey even if it isn't required
        $totpkey = TotpKey::search([
            ['user_id', '=', $user['id']],
            ['type', '=', 'totp'],
            ['status', '=', 'active']
        ])
            ->first();

        if($totpkey) {
            $totp_required = true;
        }
    }
}

// #memo - totp has the priority over email_otp
if(!$totp_required) {
    $global_email_otp_required = Setting::get_value('core', 'security', 'auth.password.email_otp_required');
    $email_otp_required = Setting::get_value('core', 'security', 'auth.password.email_otp_required', $global_email_otp_required, ['user_id' => $user['id']]);
}

$now = time();

if($totp_required) {
    $auth_token = $auth->encodeToken([
        'type'  => 'mfa_challenge',
        'amr'   => ['pwd'],
        'sub'   => $user['id'],
        'iat'   => $now,
        'exp'   => $now + 300
    ]);

    $totpkey = TotpKey::search([
        ['user_id', '=', $user['id']],
        ['type', '=', 'totp'],
        ['status', '=', 'active']
    ])
        ->read(['failed_attempts'])
        ->first();

    $allowed_failed_attempts = Setting::get_value('core', 'security', 'auth.totp.allowed_failed_attempts', 5);

    if($totpkey && $totpkey['failed_attempts'] >= $allowed_failed_attempts) {
        throw new Exception('failled_attempts_reached');
    }

    $context
        ->httpResponse()
        ->body([
            'mfa_required'  => true,
            'auth_token'    => $auth_token
        ])
        ->send();
}
elseif($email_otp_required) {
    EmailOtpKey::search([
        ['user_id', '=', $user['id']],
        ['status', '=', 'active']
    ])
        ->transition('revoke');

    $digits = Setting::get_value('core', 'security', 'auth.email_otp.digits', 6);
    $max = (10 ** $digits) - 1;
    $otp_code = str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);

    $period = Setting::get_value('core', 'security', 'auth.email_otp.period', 600);

    EmailOtpKey::create([
        'code_hash'         => password_hash($otp_code, PASSWORD_BCRYPT),
        'code_expires_at'   => $now + $period
    ]);

    $message = new EmailMessage();

    $subject = '';
    $file = "packages/core/i18n/{$user['language']}/mail_user_pass_recover.html";
    if(!($html = @file_get_contents($file))) {
        throw new Exception("missing_template", QN_ERROR_INVALID_CONFIG);
    }

    $vars_callbacks = [
        // retrieve from template
        'subject'   => function($data, $attributes) use(&$subject) {
            $subject = $attributes['title'];
            return '';
        },
        // inject in template
        'username'  => fn() => $user['firstname'],
        'validity'  => fn() => (int) floor($period / 60),
        'otp_code'  => fn() => $otp_code,
        'origin'    => fn() => constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME'),
        'abuse'     => fn() => "<a href=\"mailto:".constant('EMAIL_SMTP_ABUSE_EMAIL')."\">".constant('EMAIL_SMTP_ABUSE_EMAIL')."</a>"
    ];

    $template = new HtmlTemplate($html, $vars_callbacks);

    $message
        ->setTo($user['login'])
        ->setSubject($subject)
        ->setContentType('text/html')
        ->setBody($template->getHtml());

    Mail::send($message);

    $context
        ->httpResponse()
        ->body(['mfa_required' => true])
        ->send();
}
else {
    $auth_method = [
        'method'    => 'pwd',
        'level'     => 1,
        'exp'       => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY')
    ];

    $jwt = $auth->retrieveAccessToken();
    $token_user_id = (int) $jwt['id'];
    if($jwt && $token_user_id !== $user['id']) {
        throw new Exception('authenticated_user_mismatch', EQ_ERROR_NOT_ALLOWED);
    }

    if($jwt) {
        $access_token = $auth->addAuthMethod($auth_method);
    }
    else {
        $access_token = $auth->token($user['id'], constant('AUTH_ACCESS_TOKEN_VALIDITY'), $auth_method);
    }

    $context
        ->httpResponse()
        ->cookie('access_token',  $access_token, [
            'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS')
        ])
        ->status(204)
        ->send();
}
