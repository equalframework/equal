<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\html\HtmlTemplate;
use equal\email\EmailMessage;
use core\Mail;
use core\User;

[$params, $providers] = eQual::announce([
    'description' => 'Queue the confirmation email for a newly registered user.',
    'params'      => [
        'id' => [
            'description' => 'Identifier of the user awaiting confirmation.',
            'type'        => 'integer',
            'required'    => true
        ],
        'password' => [
            'description' => 'Plain-text password used to generate the confirmation code.',
            'type'        => 'string',
            'usage'       => 'password/nist',
            'required'    => true
        ]
    ],
    'access' => [
        'visibility' => 'protected'
    ],
    'response' => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants' => ['EQ_ROOT_USER_ID', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME'],
    'providers' => ['context', 'auth']
]);

/**
 * @var equal\php\Context                 $context
 * @var equal\auth\AuthenticationManager $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

if($auth->userId() !== EQ_ROOT_USER_ID) {
    throw new Exception('not_allowed', QN_ERROR_NOT_ALLOWED);
}

$user = User::id($params['id'])
    ->read(['id', 'login', 'username', 'language'])
    ->first(true);

if(!$user) {
    throw new Exception('invalid_user', QN_ERROR_INVALID_USER);
}

// The original password is required to generate the confirmation code in the email.
$user['password'] = $params['password'];

// The email subject is defined by the title attribute of the template's subject node.
$subject = '';
$file = "packages/core/i18n/{$user['language']}/mail_user_confirm.html";
if(!($html = @file_get_contents($file))) {
    throw new Exception('missing_dependency', QN_ERROR_INVALID_CONFIG);
}

$template = new HtmlTemplate($html, [
        'subject' => function($params, $attributes) use (&$subject) {
            $subject = $attributes['title'];
            return '';
        },
        'username' => function($params, $attributes) {
            return $params['username'];
        },
        'confirm_url' => function($params, $attributes) use ($context) {
            $code = rtrim(strtr(base64_encode($params['login'].':'.$params['password']), '+/', '-_'), '=');
            $uri = $context->getHttpRequest()->getUri();
            $url = $uri->getScheme().'://'.$uri->getAuthority();
            $url .= '/?do=user_confirm&code='.rawurlencode($code);
            return "<a href=\"$url\">{$attributes['title']}</a>";
        },
        'origin' => function($params, $attributes) {
            return constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME');
        }
    ],
    $user
);

$message = new EmailMessage();
$message
    ->setTo($user['login'])
    ->setSubject($subject)
    ->setContentType('text/html')
    ->setBody($template->getHtml());

$message_id = Mail::queue($message);

$context->httpResponse()
    ->status(201)
    ->body(['message_id' => $message_id])
    ->send();
