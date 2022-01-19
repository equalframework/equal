<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\auth;

use equal\organic\Service;
use equal\services\Container;
use equal\auth\JWT;


class AuthenticationManager extends Service {

    
    private $user_id; 
    private $method;
    
    private $tokens;        // cache map of decoded tokens

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        // initial configuration
        $this->user_id = 0;
        $this->tokens = [];
    }
    
    public static function constants() {
        return ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS', 'ROOT_USER_ID'];
    }
    
    /**
     * Provide a JWT token based on current user id and `AUTH_SECRET_KEY`
     * 
     * @param   $user_id    identifier of the user for who a token is requested
     * @param   $validity   validity duration in seconds 
     * @return  string      token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function token($user_id=0, $validity=0) {
        $payload = [
            'id'    => ($user_id > 0)?$user_id:$this->user_id,
            'exp'   => time()+$validity
        ];
        return $this->encode($payload);
    }

    /**
     * Encode an array to a JWT token
     *
     * @param  $payload array representation of the object to be encoded
     * 
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    
    public function encode(array $payload) {
        return JWT::encode($payload, AUTH_SECRET_KEY);
    }

    public function decodeToken($jwt) {
        $decoded = '';
        if(isset($this->tokens[$jwt])) {
            $decoded = $this->tokens[$jwt];
        }
        else {
            $decoded = JWT::decode($jwt);
            $this->tokens[$jwt] = $decoded;
        }        
        return $decoded;
    }

    public function verifyToken($jwt, $key) {
        $parts = explode('.', $jwt, 3);
        if(count($parts) < 3) return false;

        list($headb64, $bodyb64, $sig64) = $parts;

        $token = $this->decodeToken($jwt);
        if(!is_array($token) || !isset($token['signature']) || !isset($token['signature']) || !isset($token['header']['alg'])) {
            return false;
        }

        return JWT::verify("$headb64.$bodyb64", $token['signature'], $key, $token['header']['alg']);
    }

    public function getPemFromRsa($n, $e) {
        return JWT::rsaToPem($n, $e);
    }

    /**
     * @throws Exception ['auth_expired_token', QN_ERROR_INVALID_USER]
     */
    public function userId($token=null) {
        // grant all rights when using CLI
        if(php_sapi_name() === 'cli') {
            $this->user_id = ROOT_USER_ID;
        }

        // return user_id member, if already resolved
        if($this->user_id > 0) return $this->user_id;

        // init JWT
        $jwt = $token;

        // check the request headers for a JWT
        $context = $this->container->get('context');
        $request = $context->httpRequest();

        // no token received as param : look in HTTP request headers
        if(!$jwt) {
            $auth_header = $request->header('Authorization');
            if(!is_null($auth_header)) {
                if(strpos($auth_header, 'Bearer ') !== false) {
                    // retrieve JWT token    
                    list($jwt) = sscanf($auth_header, 'Bearer %s');
                }
                else if(strpos($auth_header, 'Basic ') !== false) {
                    list($token) = sscanf($auth_header, 'Basic %s');
                    list($username, $password) = explode(':', base64_decode($token));
                    // leave $jwt unset and authenticate (sets $user_id)
                    $this->authenticate($username, $password);
                }
                else if(strpos($auth_header, 'Digest ') !== false) {
                    // #todo
                }            
            }    
        }

        // no token found : fallback to cookie
        if(!$jwt) {
            $jwt = $request->cookie('access_token');
            // no access token provided, but refresh token found (which means that the access token has expired)
            if(!$jwt && $request->cookie('refresh_token')) {
                $jwt = $request->cookie('refresh_token');
            }
        }

        // decode and verify token, if found
        if($jwt) {
            $payload = null;
            try {
                if( !$this->verifyToken($jwt, AUTH_SECRET_KEY) ){
                    throw new \Exception('jwt_invalid_signature');
                }
                
                $decoded = $this->decodeToken($jwt);

                if(!isset($decoded['payload']['exp']) || !isset($decoded['payload']['id']) || $decoded['payload']['id'] <= 0) {
                    throw new \Exception('jwt_invalid_payload');
                }
                $payload = $decoded['payload'];
            }
            catch(\Exception $e) {
                trigger_error("Unable to decode token: ".$e->getMessage(), QN_REPORT_ERROR);
            }
            if($payload) {
                if($payload['exp'] < time()) {
                    throw new \Exception('auth_expired_token', QN_ERROR_INVALID_USER);
                }
                $this->user_id = $payload['id'];
                // if access token was refreshed, send back a new access token
                if(!$request->cookie('access_token')) {
                    $token = $this->token($this->user_id, AUTH_ACCESS_TOKEN_VALIDITY);
                    $context->httpResponse()
                    ->cookie('access_token', $token, [
                        'expires'   => time() + AUTH_ACCESS_TOKEN_VALIDITY, 
                        'httponly'  => true, 
                        'secure'    => AUTH_TOKEN_HTTPS
                    ]);
                }
            }            
        }
        
        return $this->user_id;
    }
    
    /**
     * @throws Exception    Raises an excetpion in case the credentials are not related to a user.
     */
    public function authenticate($login, $password) {
        $orm = $this->container->get('orm');
        
        $errors = $orm->validate('core\User', [], ['login' => $login]);
        if(count($errors)) {
            throw new \Exception('invalid_username', QN_ERROR_INVALID_PARAM);
        }
        
        $ids = $orm->search('core\User', ['login', '=', $login]);
        if(!count($ids)) {
            throw new \Exception('invalid_credentials', QN_ERROR_INVALID_USER);
        }

        $list = $orm->read('core\User', $ids, ['id', 'login', 'password']);
        $user = array_shift($list);

        if(!password_verify($password, $user['password'])) {
            throw new \Exception('invalid_credentials', QN_ERROR_INVALID_USER);
        }
        
        // remember current user identifier
        $this->user_id = $user['id'];

        return $this;
    }

    /**
     * Switch to another user account.
     * This operation impacts all scripts within the current call stack (cascade), so it has to be used carefully.
     * In most situations, switching to ROOT has to be reverted as soon as possible by switching back to current user.
     *
     * @param   $user_id    integer Identifier of an existing user account.
     */
    public function su(int $user_id = ROOT_USER_ID) {
        if($user_id >= 0) {
            // update current user identifier
            $this->user_id = $user_id;
        }
        return $this;
    }
}