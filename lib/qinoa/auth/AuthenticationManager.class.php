<?php
namespace qinoa\auth;

use qinoa\organic\Service;
use qinoa\services\Container;


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
        return ['AUTH_SECRET_KEY', 'ROOT_USER_ID'];
    }
    
    /**
     * Provide a JWT token based on `::user_id` and `AUTH_SECRET_KEY`
     *
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function token($user_id=null) {
        // generate access token (valid for 1 year)
        $token = JWT::encode([
                'id'    => ($user_id)?$user_id:$this->user_id,
                'exp'   => time()+60*60*24*365
            ], 
            AUTH_SECRET_KEY
        );
        return $token;
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
        list($headb64, $bodyb64, $sig64) = explode('.', $jwt);
        $token = $this->decodeToken($jwt);
        return JWT::verify("$headb64.$bodyb64", $token['signature'], $key, $token['header']['alg']);
    }

    public function getPemFromRsa($n, $e) {
        return JWT::rsaToPem($n, $e);
    }

    public function userId() {
        // return user_id member, if already resolved
        if($this->user_id > 0) return $this->user_id;

        // init JWT
        $jwt = null;

        // check the request headers for a JWT
        $request = $this->container->get('context')->httpRequest();     
        $auth_header = $request->header('Authorization');
        if(!is_null($auth_header)) {
            if(strpos($auth_header, 'Bearer ') !== false) {
                // retrieve JWT token    
                list($jwt) = sscanf($auth_header, 'Bearer %s');
            }
            else if(strpos($auth_header, 'Basic ') !== false) {
                list($token) = sscanf($auth_header, 'Basic %s');
                list($username, $password) = explode(':', base64_decode($token));
                $this->authenticate($username, $password);
                // $jwt = $this->token();
            }
            else if(strpos($auth_header, 'Digest ') !== false) {
                // todo
            }            
        }
        // no Authorization header : fallback to cookie, if any
        else {
            $jwt = $request->cookie('access_token');
        }

        // decode and verify token, if found
        if($jwt) {
            try {

                if( !$this->verifyToken($jwt, AUTH_SECRET_KEY) ){
                    throw new \Exception('JWT_invalid_signature');
                }
                
                $decoded = $this->decodeToken($jwt);

                if(isset($decoded['payload']['id']) && $decoded['payload']['id'] > 0) {
                    $this->user_id = $decoded['payload']['id'];
                }
            }
            catch(\Exception $e) {
                trigger_error("Unable to decode token: ".$e->getMessage(), QN_REPORT_ERROR);
            }
        }
        return $this->user_id;
    }
    
    public function authenticate($login, $password) {
        $orm = $this->container->get('orm');
        
        $errors = $orm->validate('core\User', ['login' => $login]);
        if(count($errors)) {
            throw new \Exception('login, password', QN_ERROR_INVALID_PARAM);
        }
        
        $ids = $orm->search('core\User', ['login', '=', $login]);
        if(!count($ids)) {
            throw new \Exception($login, QN_ERROR_INVALID_USER);
        }

        $list = $orm->read('core\User', $ids, ['id', 'login', 'password']);
        $user = array_shift($list);

        if(!password_verify($password, $user['password'])) {
            throw new \Exception("$login:$password", QN_ERROR_INVALID_USER);
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
        if($user_id > 0) {
            // update current user identifier
            $this->user_id = $user_id;
        }
        return $this;
    }
}