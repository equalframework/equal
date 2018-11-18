<?php
namespace qinoa\auth;

use qinoa\organic\Service;
use qinoa\services\Container;


class AuthenticationManager extends Service {

    
    private $user_id; 
    private $method;
    
    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        // initial configuration
        $this->user_id = 0;
    }
    
    public static function constants() {
        return ['AUTH_SECRET_KEY'];
    }
    
    /**
     * Provide a JWT token based on `::user_id` and `AUTH_SECRET_KEY`
     *
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function token() {
        // generate access token (valid for 1 year)
        $token = JWT::encode([
            'id'    => $this->user_id,
            'exp'   => time()+60*60*24*365
        ], 
        AUTH_SECRET_KEY);
        return $token;
    }
    
    public function userId() {
        // return user_id member, if already resolved
        if($this->user_id > 0) return $this->user_id;
        // init JWT
        $jwt = null;        
        // look the request headers for a JWT
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
                $jwt = $this->token();
            }
            else if(strpos($auth_header, 'Digest ') !== false) {
                // todo
            }            
        }
        // no Authorization header : fallback to cookie
        else {
            $jwt = $request->cookie('access_token');
        }    
        // decode token, if found
        if($jwt) {
            try {
                $data = (array) JWT::decode($jwt, AUTH_SECRET_KEY);
                if(isset($data['id']) && $data['id'] > 0) {
                    $this->user_id = $data['id'];
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
        
        $errors = $orm->validate('core\User', ['login' => $login, 'password' => $password]);
        if(count($errors)) throw new \Exception($login, QN_ERROR_INVALID_USER);
        
        $ids = $orm->search('core\User', [['login', '=', $login], ['password', '=', $password]]);        
        if(!count($ids)) throw new \Exception($login, QN_ERROR_INVALID_USER);

        // remember current user identifier
        $this->user_id = $ids[0];       

        return $this;
    }
}