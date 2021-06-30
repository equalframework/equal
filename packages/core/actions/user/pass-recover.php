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
            'usage'         => 'uri/url:mailto',
            'required'      => true
        ],
        'lang' => [
            'description'   => 'Lang in which the procedure is requested.',
            'type'          => 'string',
            'usage'         => 'language/iso-639:2',
            'default'       => 'fr'
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


// we need root privilege
$auth->su();

// retrieve by login
$ids = User::search(['login', '=', $params['email']])->ids();

if(!count($ids)) { 
    throw new \Exception("user_not_found", QN_ERROR_UNKNOWN_OBJECT);
}

$user_id = array_shift($ids);

// we need the user credentials to generate confirmation code in the email
$user = User::id($user_id)
        ->read(['id', 'login', 'firstname'])
        ->first();

// generate a token that will be valid for 1 hour (will need a refresh)
$token = $auth->token($user_id, 1);



// subject of the email should be defined in the template, as a <var> tag holding a 'title' attribute
$subject = '';
// read template according to user requested language
$file = "packages/core/i18n/{$params['lang']}/mail_user_pass_recover.html";
if(!($html = @file_get_contents($file, FILE_TEXT))) {
    throw new Exception("missing_template", QN_ERROR_INVALID_CONFIG);
}

$template = new HtmlTemplate($html, [
                            'subject'		=>	function ($params, $attributes) use (&$subject) {
                                                    $subject = $attributes['title'];
                                                    return '';
                                                },
                            'username'		=>	function ($params, $attributes) {
                                                    return $params['firstname'];
                                                },
                            'recovery_url'	=>	function ($params, $attributes) use($token) {
                                                    $url = WHARN_APP_URL."/recover/update?c={$token}";
                                                    return "<a href=\"$url\">{$attributes['title']}</a>";
                                                },
                            'origin'        =>  function ($params, $attributes) {
                                                    return EMAIL_SMTP_ACCOUNT_DISPLAYNAME;
                                                }
                            ], 
                            $user);
// parse template as html
$body = $template->getHtml();

// send message

//         ->setFrom([EMAIL_SMTP_ACCOUNT_EMAIL => EMAIL_SMTP_ACCOUNT_DISPLAYNAME]);

        
$spool->queue($subject, $body, $params['email']);

$context->httpResponse()
        ->body([])
        ->send();