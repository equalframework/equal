<?php
use equal\html\HtmlTemplate;
use equal\email\Email;
use core\Mail;
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempt to register a new user.",
    'params' 		=>	[
        'email' => [
            'description'   => 'Email address of the user.',
            'type'          => 'string',
            'usage'         => 'email',
            'required'      => true
        ],
        'username' => [
            'description'   => 'Nickname of the user.',
            'type'          => 'string',
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
            'default'       => ''
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
        ],
        'resend' => [
            'description'   => 'Previously sent message identifier to resend (must match credentials).',
            'type'          => 'integer',
            'default'       => 0
        ]
    ],
    'constants'     => ['DEFAULT_LANG', 'EMAIL_SMTP_HOST', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME'],
    'access'        => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\orm\ObjectManager             $om
 * @var equal\auth\AuthenticationManager    $auth
 */
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

// cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
list($username, $domain) = explode('@', strtolower(trim($params['email'])));
$username .= '+';
$login = substr($username, 0, strpos($username, '+')).'@'.$domain;

list($password, $firstname, $lastname, $language, $send_confirm) = [
    $params['password'],
    $params['firstname'],
    $params['lastname'],
    $params['language'],
    // set to false only if user is registering through an authenticated source (SSO)
    $params['send_confirm']
];

// unique identifier of the Mail message
$message_id = 0;

// get root privileges
$auth->su();

// check the existence of the user account
$ids = $om->search(User::getType(), [['login', '=', $login]]);

if($params['resend']) {
    if(count($ids) <= 0) {
        throw new Exception('invalid_request', QN_ERROR_INVALID_USER);
    }
    $user_id = reset($ids);
    $user = User::id($user_id)->read(['login', 'username', 'firstname', 'lastname'])->first(true);
    $message_id = $params['resend'];
    // if message is still in pool : abort
    $send_confirm = !(Mail::isQueued($message_id));
}
else {
    if(count($ids) > 0) {
        throw new Exception('existing_user', QN_ERROR_INVALID_USER);
    }
    // #memo - email might still be invalid (a validation check is made in User class)
    $user = User::create([
            'login'     => $params['email'],
            'username'  => $params['username'],
            'password'  => $password,
            'firstname' => $firstname,
            'lastname'  => $lastname
        ])
        ->read(['id', 'login', 'username', 'firstname', 'lastname', 'language'])
        ->adapt('json')
        ->first(true);
}

if($send_confirm) {
    // we need the original password to generate confirmation code in the email
    $user['password'] = $password;
    // subject of the email should be defined in the template, as a <var> tag holding a 'title' attribute
    $subject = '';
    // read template according to user preferred language
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
                                    return $params['username'];
                                },
            'confirm_url'	=>	function ($params, $attributes) use ($context) {
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

    // create message
    $message = new Email();
    $message->setTo($user['login'])
            ->setSubject($subject)
            ->setContentType("text/html")
            ->setBody($body);

    // queue message
    $message_id = Mail::queue($message);
}

$context->httpResponse()
    ->status(200)
    ->body(['message_id' => $message_id])
    ->send();
