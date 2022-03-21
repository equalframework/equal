<?php
use equal\html\HtmlTemplate;
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([	
    'description'	=>	"Send password recovery instructions to current user.",
    'params' 		=>	[
        'email' =>  [
            'description'   => 'Email address for password recovery.',
            'type'          => 'string',
            'usage'         => 'email',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],        
    'providers'     => ['context', 'orm', 'spool', 'auth']
]);


// initalise local vars with inputs
list($om, $context, $spool, $auth) = [ $providers['orm'], $providers['context'], $providers['spool'], $providers['auth'] ];

try {
    // we need root privilege
    $auth->su();

    // retrieve by login
    $ids = User::search(['login', '=', $params['email']])->ids();

    if(!count($ids)) { 
        throw new Exception("user_not_found", QN_ERROR_UNKNOWN_OBJECT);
    }

    $user_id = array_shift($ids);

    // we need the user credentials to generate confirmation code in the email
    $user = User::id($user_id)
            ->read(['id', 'login', 'validated', 'firstname', 'language'])
            ->first();

    if(!$user || !$user['validated']) {
        throw new Exception("not_allowed", QN_ERROR_NOT_ALLOWED);
    }

    // generate a token that will be valid for 15 minutes 
    $token = $auth->token($user_id, 60*15);


    // subject of the email should be defined in the template, as a <var> tag holding a 'title' attribute
    $subject = '';
    // read template according to user requested language
    $file = "packages/core/i18n/{$user['language']}/mail_user_pass_recover.html";
    if(!($html = @file_get_contents($file))) {
        throw new Exception("missing_template", QN_ERROR_INVALID_CONFIG);
    }

    // define template `var` nodes parsing callbacks
    $template = new HtmlTemplate($html, [
        'subject'		=>	function ($params, $attributes) use (&$subject) {
                                $subject = $attributes['title'];
                                return '';
                            },
        'username'		=>	function ($params, $attributes) {
                                return $params['user']['firstname'];
                            },
        'recovery_url'	=>	function ($params, $attributes) {
                                $url = ROOT_APP_URL."/auth/#!/reset/{$params['token']}";
                                return "<a href=\"$url\">{$attributes['title']}</a>";
                            },
        'origin'        =>  function ($params, $attributes) {
                                return EMAIL_SMTP_ACCOUNT_DISPLAYNAME;
                            },
        'abuse'         =>  function($params, $attributes) {
                                return "<a href=\"mailto:".EMAIL_SMTP_ABUSE_EMAIL."\">".EMAIL_SMTP_ABUSE_EMAIL."</a>";            
                            }
        ],
        [
            'user'  => $user,
            'token' => $token
        ]        
    );

    // parse template as html
    $body = $template->getHtml();

    // send message            
    $spool->queue($subject, $body, $params['email']);
}
catch(Exception $e) {
    // for security reasons, in case of error no details are relayed to client
    trigger_error("QN_DEBUG_ORM::{$e->getMessage()}", QN_REPORT_ERROR);
}

$context->httpResponse()
        ->body([])
        ->send();