<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Mail;
use core\security\challenge\EmailOtpChallenge;
use core\setting\Setting;
use core\User;
use equal\email\Email;
use equal\html\HtmlTemplate;

[$params, $providers] = eQual::announce([
    'description'   => 'Creates and sends an email OTP challenge for the current user.',
    'params'        => [],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['EMAIL_SMTP_ACCOUNT_DISPLAYNAME', 'EMAIL_SMTP_ABUSE_EMAIL'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                $context
 * @var equal\auth\AuthenticationManager $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$user = User::id($auth->userId())
    ->read(['validated', 'allow_auth', 'login', 'firstname', 'language'])
    ->first();

if(!$user) {
    throw new Exception('user_not_found', EQ_ERROR_INVALID_USER);
}

if(!$user['validated']) {
    throw new Exception('user_not_validated', EQ_ERROR_NOT_ALLOWED);
}

if(!$user['allow_auth']) {
    throw new Exception('not_allowed', EQ_ERROR_NOT_ALLOWED);
}

$email_otp_challenges_count = EmailOtpChallenge::search([
        ['user_id', '=', $user['id']],
        ['created', '>=', time() - 600]
    ])
    ->count();

if($email_otp_challenges_count >= 3) {
    throw new Exception('max_sent_email_reached', EQ_ERROR_NOT_ALLOWED);
}

$digits = Setting::get_value('core', 'security', 'auth.email_otp.digits', 6);
$max = (10 ** $digits) - 1;
$otp_code = str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);

$timeout = Setting::get_value('core', 'security', 'auth.email_otp.timeout', 300);
$expires_at = time() + $timeout;

$subject = '';
$file = EQ_BASEDIR."/packages/core/i18n/{$user['language']}/mail_user_auth_email_otp.html";
if(!($html = @file_get_contents($file))) {
    throw new Exception('missing_template', EQ_ERROR_INVALID_CONFIG);
}

$vars_callbacks = [
    'subject'   => function($data, $attributes) use(&$subject) {
        $subject = $attributes['title'];
        return '';
    },
    'username'  => fn() => $user['firstname'],
    'validity'  => fn() => (int) floor($timeout / 60),
    'otp_code'  => fn() => $otp_code,
    'origin'    => fn() => constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME'),
    'abuse'     => fn() => "<a href=\"mailto:".constant('EMAIL_SMTP_ABUSE_EMAIL')."\">".constant('EMAIL_SMTP_ABUSE_EMAIL')."</a>"
];

$template = new HtmlTemplate($html, $vars_callbacks);
$body = $template->getHtml();

$message = new Email();
$message
    ->setTo($user['login'])
    ->setSubject($subject)
    ->setContentType('text/html')
    ->setBody($body);

$pending_email_otp_challenges = EmailOtpChallenge::search([
    ['user_id', '=', $user['id']],
    ['status', '=', 'pending']
]);

if($pending_email_otp_challenges->count() > 0) {
    $pending_email_otp_challenges
        ->update(['invalidated_reason' => 'superseded'])
        ->transition('invalidate');
}

$email_otp_challenge = EmailOtpChallenge::create([
        'user_id'       => $user['id'],
        'code_hash'     => password_hash($otp_code, PASSWORD_BCRYPT),
        'expires_at'    => $expires_at
    ])
    ->first();

try {
    Mail::send($message);
}
catch(Throwable $exception) {
    EmailOtpChallenge::id($email_otp_challenge['id'])
        ->update(['invalidated_reason' => 'delivery_failed'])
        ->transition('invalidate');

    throw $exception;
}

$context
    ->httpResponse()
    ->status(201)
    ->send();
