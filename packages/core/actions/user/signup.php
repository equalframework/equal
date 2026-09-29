<?php
use core\Mail;
use core\User;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
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
    'access'        => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'constants'     => ['USER_ACCOUNT_REGISTRATION', 'DEFAULT_LANG'],
    'providers'     => ['context', 'orm', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\orm\ObjectManager             $om
 * @var equal\auth\AuthenticationManager    $auth
 */
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

if(!constant('USER_ACCOUNT_REGISTRATION')) {
    throw new Exception('no_registration', QN_ERROR_NOT_ALLOWED);
}

// cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
[$email, $domain] = explode('@', strtolower(trim($params['email'])));
$email .= '+';
$login = substr($email, 0, strpos($email, '+')).'@'.$domain;

$username = trim($params['username']);

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
    $user = User::id($user_id)->read(['id', 'status', 'login', 'username', 'firstname', 'lastname'])->first(true);
    $message_id = $params['resend'];
    // if message is still in pool : abort
    $send_confirm = !(Mail::isQueued($message_id) || $user['status'] != 'created');
}
else {
    if(count($ids) > 0) {
        throw new Exception('existing_email', QN_ERROR_INVALID_USER);
    }

    // check username uniqueness
    $ids = $om->search(User::getType(), [['username', '=', $username]]);
    if(count($ids) > 0) {
        throw new Exception('existing_username', QN_ERROR_INVALID_USER);
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
    $result = eQual::run('do', 'core_user_send-confirmation', [
        'id'       => $user['id'],
        'password' => $password
    ]);
    $message_id = $result['message_id'];
}

$context->httpResponse()
    ->body(['message_id' => $message_id])
    ->send();
