<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\http\HttpRequest;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempt to auth a user from an external social network.",
    'params' 		=>	[
        'network_name'  =>  [
            'description'   => 'name of the social network to address oauth request.',
            'type'          => 'string', 
            'required'      => true
        ],
        'network_token' =>  [
            'description'   => 'valid acess token for oauth.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],        
    'providers'     => ['context', 'orm', 'auth'] 
]);

// initalise local vars with inputs
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

list($result, $error_message_ids) = [true, []];

list($action_name, $network_name, $network_token) = [ 
    'resiway_user_auth',
    $params['network_name'],
    $params['network_token']
];

    
switch($network_name) {
case 'facebook':
    $oauthRequest = new HttpRequest('/v2.9/me', ['Host' => 'graph.facebook.com:443']);    
    $response = $oauthRequest
                ->setBody([
                    'fields'       => 'email,first_name,last_name',
                    'access_token' => $network_token
                ])->send();
    if(!is_null($response->get('error'))) {
        throw new Exception("user_invalid_auth", QN_ERROR_NOT_ALLOWED);
    }                
    $data = $response->getBody();
    $id = $data['id'];
    $account_type = 'facebook';        
    $avatar_url = "https://graph.facebook.com/{$id}/picture?height=@size&width=@size";
    list($login, $firstname, $lastname) = [$data['email'], $data['first_name'], $data['last_name']];       
    break;
case 'google':
    /* upcoming changes : @see https://developers.google.com/people/api/rest/v1/people/get
    $oauthRequest = new HttpRequest('/v1/people/me', ['Host' => 'people.googleapis.com:443']);
    [...]
    $response = $oauthRequest
                ->setBody([
                    'personFields' => 'names,emailAddresses',
                    'access_token' => $network_token
                ])->send();
    
    */
    $oauthRequest = new HttpRequest('/userinfo/v2/me', ['Host' => 'www.googleapis.com:443']);
    $response = $oauthRequest
                ->setBody([
                    'access_token' => $network_token
                ])->send();
    if(!is_null($response->get('error'))) {
        throw new Exception("user_invalid_auth", QN_ERROR_NOT_ALLOWED);
    }
    $data = $response->getBody();
    $account_type = 'google';        
    $avatar_url = $data['picture'];
    list($login, $firstname, $lastname) = [$data['email'], $data['given_name'], $data['family_name']];
    break;
default:
    throw new Exception("user_invalid_network", QN_ERROR_INVALID_PARAM);           
}


// check if an account has already been created for this email address
$ids = $om->search('core\User', ['login', '=',  $context->httpRequest()->get('login')]);

if($ids < 0) {
    throw new Exception("action_failed", QN_ERROR_UNKNOWN);
}

// an account with this email address already exists
if(count($ids) > 0) {
    $user_id = $ids[0];
}
// no account yet : register new user
else {
    // generate a random password (10 chars)
    $password = '';
    $dict = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $len = strlen($dict) - 1;
    for ($i = 0; $i < 10; $i++) {
        $password .= $dict[rand(0, $len)];
    }
    
    // try to create a new user account (disable email confirmation)
    $json = run('do', 'user_signup', [
        'login'         => $login, 
        'password'      => $password, 
        'firstname'     => $firstname,
        'lastname'      => $lastname,
        'language'      => $language,
        'send_confirm'  => false
    ]);


    // decode json into an array
    $data = json_decode($json, true);

    // relay error if any
    if(isset($data['errors'])) {
        foreach($data['errors'] as $name => $message) {
            throw new Exception($message, qn_error_code($name));
        }
    }

    $auth->authenticate($login, $password);
    $user_id = $auth->userId();
}

if($user_id <= 0) throw new Exception("action_failed", QN_ERROR_UNKNOWN); 

// update user data
$om->write('core\User', $user_id, [
    'validated'      => true
]);        

// generate access_token
$access_token = $auth->token($user_id);
                 
$context->httpResponse()
        // store token in cookie
        ->cookie('access_token', $access_token)
        ->body(['access_token' => $access_token])
        ->send();