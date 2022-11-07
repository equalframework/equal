<?php
if(!file_exists(QN_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php')) {
    throw new Exception("missing_dependency", QN_ERROR_INVALID_CONFIG);
}
require_once QN_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

use \Swift_SmtpTransport as Swift_SmtpTransport;
use \Swift_Message as Swift_Message;
use \Swift_Mailer as Swift_Mailer;
use equal\html\HtmlTemplate;
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempt to register a new user.",
    'params' 		=>	[
        'login' => [
            'description'   => 'Email address of the user.',
            'type'          => 'string',
            'usage'         => 'email',
            'required'      => true
        ],
        'password' =>  [
            'description'   => 'The user chosen password.',
            'type'          => 'string',
            'usage'         => 'password/nist',
            'required'      => true
        ],
        'firstname' => [
            'description'   => 'User\'s firstname.',
            'type'          => 'string',
            'required'      => true
        ],
        'lastname' => [
            'description'   => 'User\'s lastname.',
            'type'          => 'string',
            'default'       => ''
        ],
        'language' => [
            'description'   => 'User\'s preferred language.',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ],
        'send_confirm' => [
            'description'   => 'Flag telling if we need to send a confirmation email.',
            'type'          => 'boolean',
            'default'       => true
        ]
    ],
    'constants'     => ['DEFAULT_LANG', 'AUTH_ACCESS_TOKEN_VALIDITY', 'EMAIL_SMTP_HOST', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME', 'EMAIL_SMTP_ACCOUNT_USERNAME', 'EMAIL_SMTP_ACCOUNT_PASSWORD'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


// initalise local vars with inputs
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

// cleanup provided email (as login): we strip heading and trailing spaces and remove recipient tag, if any
// #memo - email might still be invalid (a validation check is made in User class)
list($username, $domain) = explode('@', strtolower(trim($params['login'])));
$username .= '+';
$login = substr($username, 0, strpos($username, '+')).'@'.$domain;

list($password, $firstname, $lastname, $language, $send_confirm) = [
    $params['password'],
    $params['firstname'],
    $params['lastname'],
    $params['language'],
    $params['send_confirm']                 // set to false only if user is registering through an authentified source (SSO)
];

// try to create a new user account
$json = run('do', 'user_create', [
        'login'         => $login,
        'password'      => $password,
        'firstname'     => $firstname,
        'lastname'      => $lastname,
        'language'      => $language
    ]);


// decode json into an array
$data = json_decode($json, true);

// relay error if any
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) {
        throw new Exception($message, qn_error_code($name));
    }
}

// we received an array describing a User object
$user = $data;
// generate access_token
$access_token = $auth->token($user['id'], constant('AUTH_ACCESS_TOKEN_VALIDITY'));


if($send_confirm) {

    // we need the original password to generate confirmation code in the email
    $user['password'] = $password;

    // subject of the email should be defined in the template, as a <var> tag holding a 'title' attribute
    $subject = '';
    // read template according to user prefered language
    $file = "packages/core/i18n/{$user['language']}/mail_user_confirm.html";
    if(!($html = @file_get_contents($file))) {
        throw new Exception("missing_dependency", QN_ERROR_INVALID_CONFIG);
    }
    $template = new HtmlTemplate($html, [
        'subject'		=>	function ($params, $attributes) use (&$subject) {
                                $subject = $attributes['title'];
                                return '';
                            },
        'username'		=>	function ($params, $attributes) {
                                return $params['firstname'];
                            },
        'confirm_url'	=>	function ($params, $attributes) use($context) {
                                $code = base64_encode($params['login'].':'.$params['password']);
                                $uri = $context->getHttpRequest()->getUri();
                                $url = $uri->getScheme().'://'.$uri->getAuthority();
                                $url = $url."/?do=user_confirm&code={$code}";
                                return "<a href=\"$url\">{$attributes['title']}</a>";
                            },
        'origin'        =>  function ($params, $attributes) {
                                return constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME');
                            }
    ],
    $user);

    // parse template as html
    $body = $template->getHtml();


    // send message
    $transport = new Swift_SmtpTransport(constant('EMAIL_SMTP_HOST'), constant('EMAIL_SMTP_PORT') /*, 'ssl'*/);

    $transport->setUsername(constant('EMAIL_SMTP_ACCOUNT_USERNAME'))
              ->setPassword(constant('EMAIL_SMTP_ACCOUNT_PASSWORD'));

    $message = new Swift_Message();
    $message->setTo($user['login'])
            ->setSubject($subject)
            ->setContentType("text/html")
            ->setBody($body)
            ->setFrom([constant('EMAIL_SMTP_ACCOUNT_USERNAME') => constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME')]);

    $mailer = new Swift_Mailer($transport);
    $result = $mailer->send($message);
}

$context->httpResponse()
        // store token in cookie
        ->cookie('access_token', $access_token)
        ->body(['access_token' => $access_token])
        ->send();