<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Mail;
use core\User;
use equal\email\EmailMessage;
use equal\html\HtmlTemplate;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   => "Send username to current user.",
    'params'        => [
        'email' => [
            'type'          => 'string',
            'usage'         => 'email',
            'description'   => 'Email address for username recovery.',
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
    'constants'     => ['BACKEND_URL', 'EMAIL_SMTP_ABUSE_EMAIL', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME'],
    'providers'     => ['context', 'auth']
]);


// initialize local vars with inputs
/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

try {
    // we need root privilege
    $auth->su();

    // retrieve by login
    $ids = User::search(['login', '=', $params['email']])->ids();

    if(!count($ids)) {
        throw new Exception("user_not_found", EQ_ERROR_UNKNOWN_OBJECT);
    }

    $user_id = array_shift($ids);

    // we need the user credentials to generate confirmation code in the email
    $user = User::id($user_id)
            ->read(['id', 'login', 'username', 'validated', 'firstname', 'language'])
            ->first(true);

    if(!$user || !$user['validated']) {
        throw new Exception("not_allowed", EQ_ERROR_NOT_ALLOWED);
    }

    if(empty($user['username'])) {
        throw new Exception("username_not_defined", EQ_ERROR_NOT_ALLOWED);
    }


    // subject of the email should be defined in the template, as a <var> tag holding a 'title' attribute
    $subject = '';
    // read template according to user requested language
    $file = "packages/core/i18n/{$user['language']}/mail_user_username_recover.html";
    if(!($html = @file_get_contents($file))) {
        throw new Exception("missing_template", EQ_ERROR_INVALID_CONFIG);
    }

    // define template `var` nodes parsing callbacks
    $template = new HtmlTemplate($html, [
        'subject'       =>	function ($params, $attributes) use (&$subject) {
                                $subject = $attributes['title'];
                                return '';
                            },
        'firstname'     =>	function ($params, $attributes) {
                                return $params['user']['firstname'];
                            },
        'username'      =>  function ($params, $attributes) {
                                return $params['user']['username'];
                            },
        'origin'        =>  function ($params, $attributes) {
                                return constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME');
                            },
        'abuse'         =>  function($params, $attributes) {
                                return "<a href=\"mailto:".constant('EMAIL_SMTP_ABUSE_EMAIL')."\">".constant('EMAIL_SMTP_ABUSE_EMAIL')."</a>";
                            }
        ],
        [
            'user'  => $user
        ]
    );

    // parse template as html
    $body = $template->getHtml();

    // create message
    $message = new EmailMessage();
    $message
        ->setTo($params['email'])
        ->setSubject($subject)
        ->setContentType("text/html")
        ->setBody($body);

    // send message
    Mail::queue($message);
}
catch(Exception $e) {
    // for security reasons, in case of error no details are relayed to client
    trigger_error("ORM::{$e->getMessage()}", EQ_REPORT_ERROR);
}

$context
    ->httpResponse()
    ->status(204)
    ->send();
