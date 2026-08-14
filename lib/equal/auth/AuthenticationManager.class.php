<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\auth;

use core\security\AccessToken;
use core\setting\Setting;
use equal\organic\Service;
use equal\orm\ObjectManager;
use equal\services\Container;

class AuthenticationManager extends Service {

    /**
     * @var integer Final resolved user identifier, after applying impersonation if any.
     */
    private $user_id;

    /**
     * @var integer Authenticated user identifier, as provided by the access token or Basic Auth, before applying impersonation.
     */
    private $authenticated_user_id;

    /**
     * @var array Map for caching decoded tokens.
     */
    private $tokens;

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        // initial configuration
        $this->user_id = 0;
        $this->authenticated_user_id = 0;
        $this->tokens = [];
    }

    public static function constants() {
        return ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS', 'EQ_ROOT_USER_ID'];
    }

    /**
     * Provides the resolved current user identifier.
     *
     * This method is an alias of userId().
     * It returns the final user id, after applying impersonation if any.
     *
     * @param string|null $token
     * @return int
     */
    public function getUserId($token = null): int {
        return $this->userId($token);
    }

    /**
     * Returns the authentication level of the current user based on the given JWT token.
     * This method checks the 'amr' (Authentication Methods Reference) in JWT to determine the authentication level (1: AAL1, 2: AAL2, 3: AAL3).
     * If the token is not provided, it attempts to retrieve the current user's token. Default authentication level is 1.
     */
    public function getAuthLevel($token = null) {
        $result = 1;
        $jwt = $this->retrieveAccessToken($token);
        if($jwt && isset($jwt['amr']) && is_array($jwt['amr'])) {
            foreach($jwt['amr'] as $auth_method) {
                if(isset($auth_method['auth_level']) && $auth_method['auth_level'] > $result) {
                    $result = $auth_method['auth_level'];
                }
            }
        }
        return $result;
    }

    /**
     * Provide a JWT token based on given user (or current user if known) and `AUTH_SECRET_KEY`.
     *
     * The JWT access token is built on a payload holding:
     *   - id  : the user identifier
     *   - sub : the user identifier
     *   - amr : Authentication Methods Reference
     *   - iat : the datetime (timestamp) at which the token was issued
     *   - trk : is the token tracked or not
     *   - exp : (optional) the datetime (timestamp) at which the token expires
     *   - jti : (optional) id of the token to allow tracking
     *
     * @param   int $user_id        identifier of the user for whom a token is requested
     * @param   int $validity       validity duration in seconds
     * @param   array $auth_method  authentication method to describe how the user was authenticated (e.g. password, MFA, etc.)
     * @param   int $jti            id of the AccessToken to track it (non-tracked tokens are stateless)
     * @return  string              token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function token(int $user_id = 0, int $validity = 0, array $auth_method = [], int $jti = 0) {
        if(isset($auth_method['auth_level'])) {
            // amr must be a list of all authentication methods
            $auth_method = [$auth_method];
        }

        $payload = [
            // internal user identifier (non-standard claim)
            'id'    => $user_id ?: $this->user_id,

            // subject of the token (standard JWT claim) - represents the authenticated user
            'sub'   => $user_id ?: $this->user_id,

            // Authentication Methods References (standard OpenID Connect claim) - describes how the user was authenticated (e.g. password, MFA, etc.)
            'amr'   => $auth_method,

            // Issued At (standard JWT claim) - timestamp when the token was generated
            'iat'   => time(),

            // Tracking flag (non-standard claim) - Indicates whether the token is tracked server-side (e.g. stored in DB)
            // If true, the token can be revoked (blacklist check required)
            // If false, the token is considered stateless (no server-side validation beyond signature/exp)
            'trk'   => $jti > 0
        ];
        // handle expiry
        if($validity) {
            $payload['exp'] = time() + $validity;
        }
        // handle token id for tracking
        if($jti > 0) {
            $payload['jti'] = $jti;
        }
        return $this->encodeToken($payload);
    }

    /**
     * Provide a renewed JWT token adding given validity time.
     *
     * @param int $validity duration in seconds
     * @return  string
     * @throws \Exception
     */
    public function renewedToken(int $validity = 0) {
        $jwt = $this->retrieveAccessToken();

        if(is_null($jwt)) {
            throw new \Exception('unable_to_retrieve_access_token');
        }

        $payload = [
            'id'    => $jwt['id'],
            'sub'   => $jwt['sub'] ?? $jwt['id'],
            'amr'   => $jwt['amr'] ?? [],
            'iat'   => time(),
            'trk'   => $jwt['trk'] ?? false,
            'exp'   => time() + $validity
        ];

        if(isset($jwt['jti'])) {
            $payload['jti'] = $jwt['jti'];
        }

        return $this->encodeToken($payload);
    }

    /**
     * Encode an array to a JWT token
     *
     * @param  $payload array representation of the object to be encoded
     *
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     * @deprecated use encodeToken instead
     */
    public function encode(array $payload) {
        return JWT::encode($payload, constant('AUTH_SECRET_KEY'));
    }

    /**
     * Encode an array to a JWT token
     *
     * @param  $payload array representation of the object to be encoded
     *
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function encodeToken(array $payload) {
        return JWT::encode($payload, constant('AUTH_SECRET_KEY'));
    }

    /**
     * Create a stored AccessToken and return it as a token
     *
     * @param   int $user_id        identifier of the user for whom a token is requested
     * @param   int $validity       validity duration in seconds
     *
     * @return string token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function createAccessToken(int $user_id, int $validity = 0) {
        $accessToken = AccessToken::create([
                'user_id'   => $user_id,
                'expiry'    => ($validity) ? (time() + $validity) : null
            ])
            ->first();
        return $this->token($user_id, $validity, [], $accessToken['id']);
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


    /**
     * This method is intended to check a token validity.
     * Given token can be used for any purpose (not only auth).
     */
    public function verifyToken($jwt, $key) {
        $parts = explode('.', $jwt, 3);
        if(count($parts) < 3) {
            return false;
        }

        list($headb64, $bodyb64, $sig64) = $parts;

        $token = $this->decodeToken($jwt);
        if(!is_array($token) || !isset($token['signature']) || !isset($token['signature']) || !isset($token['header']['alg'])) {
            return false;
        }

        return JWT::verify("$headb64.$bodyb64", $token['signature'], $key, $token['header']['alg']);
    }

    /**
     * Attempts to decode the JWT token from the received HTTP request, or uses the provided token if specified.
     *
     * @param string $jwt   The JSON Web Token (JWT) string to decode. If not provided, the function will attempt to extract the token from the HTTP request.
     *
     * @return array|null   Decoded, non-expired access token payload as an associative array mapping 'id', 'exp' & 'amr' (@see `token()` method).
     */
    public function retrieveAccessToken($jwt = null) {

        $result = null;

        if(!$jwt) {
            // check the request headers for a JWT
            $context = $this->container->get('context');

            /** @var \equal\http\HttpRequest  */
            $request = $context->httpRequest();

            $jwt = $request->cookie('access_token');

            // no token found : fallback to Authorization header
            if(!$jwt) {
                $auth_header = $request->header('Authorization');

                if($auth_header) {
                    if(strpos($auth_header, 'Bearer ') !== false) {
                        // retrieve JWT token
                        [$jwt] = sscanf($auth_header, 'Bearer %s');
                    }
                }
            }
        }

        if($jwt) {
            try {
                if(!$this->verifyToken($jwt, constant('AUTH_SECRET_KEY'))){
                    throw new \Exception('jwt_invalid_signature');
                }

                $decoded = $this->decodeToken($jwt);

                if(!isset($decoded['payload']['id']) || $decoded['payload']['id'] <= 0) {
                    throw new \Exception('jwt_invalid_payload');
                }

                if(isset($decoded['payload']['exp']) && $decoded['payload']['exp'] < time()) {
                    return null;
                }

                $result = $decoded['payload'];
            }
            catch(\Exception $e) {
                trigger_error("API::Unable to decode token: " . $e->getMessage(), EQ_REPORT_ERROR);
            }
        }

        return $result;
    }

    /**
     * Provides the authenticated user identifier, before applying impersonation.
     *
     * This is the user id provided by the access token, or by Basic Auth when used.
     * If no authenticated user has been resolved yet, this method resolves the
     * current user first.
     *
     * @param string|null $token
     * @return int
     */
    public function authenticatedUserId($token = null): int {
        if($this->authenticated_user_id > 0) {
            return $this->authenticated_user_id;
        }

        $this->userId($token);

        return $this->authenticated_user_id;
    }

    /**
     * Retrieves the identifier of the current user.
     * If not resolved yet, this method attempts to retrieve the user based on a token or HTTP header.
     * When called via CLI, it always returns the root ID .
     *
     * @return  integer     Upon success, the id of the current user is returned. Otherwise, this method returns 0.
     */
    public function userId($token=null) {
        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        // unless already resolved, grant all rights when using CLI
        if($this->user_id <= 0 && php_sapi_name() === 'cli') {
            $this->user_id = EQ_ROOT_USER_ID;
        }

        // if already resolved, return user_id
        if($this->user_id > 0) {
            return $this->user_id;
        }

        try {
            $authenticated_user_id = 0;

            // retrieve JWT payload
            $jwt = $this->retrieveAccessToken($token);

            // decode and verify token, if found
            if($jwt) {
                if(isset($jwt['exp']) && $jwt['exp'] < time()) {
                    // generate a 401 Unauthorized HTTP response
                    throw new \Exception('auth_expired_token', EQ_ERROR_INVALID_USER);
                }
                if(isset($jwt['trk']) && $jwt['trk'] && !empty($jwt['jti'])) {
                    $tk_ids = $orm->search('core\security\AccessToken', ['jti', '=', $jwt['jti']]);
                    $tks = $orm->read('core\security\AccessToken', $tk_ids, ['is_revoked']);
                    $tk = current($tks);
                    if($tk && $tk['is_revoked']) {
                        // generate a 401 Unauthorized HTTP response
                        throw new \Exception('auth_revoked_token', EQ_ERROR_INVALID_USER);
                    }
                }
                $authenticated_user_id = $jwt['id'];
            }
            // no jwt found: attempt using other Basic HTTP auth, if allowed
            else {
                // #todo - add a config setting to enable Basic http auth

                // check the request headers for a JWT
                $context = $this->container->get('context');

                /** @var \equal\http\HttpRequest  */
                $request = $context->httpRequest();

                $auth_header = $request->header('Authorization');

                if($auth_header) {
                    if(strpos($auth_header, 'Basic ') !== false) {
                        [$token] = sscanf($auth_header, 'Basic %s');
                        [$username, $password] = explode(':', base64_decode($token));
                        // leave $jwt unset and authenticate (sets $user_id)
                        $this->authenticate($username, $password);
                        $authenticated_user_id = $this->user_id;
                    }
                }
            }

            if($authenticated_user_id > 0) {
                // validate the real authenticated user
                $this->assertActiveUser($authenticated_user_id);

                // remember authenticated user before applying impersonation
                $this->authenticated_user_id = $authenticated_user_id;

                // resolve the final user (target user may exist without being active/validated/confirmed)
                $this->user_id = $this->resolveUserId($authenticated_user_id);
            }
        }
        catch(\Exception $e) {
            trigger_error("AAA::unable to retrieve user ID: " . $e->getMessage(), EQ_REPORT_WARNING);
            $this->user_id = 0;
        }

        return $this->user_id;
    }

    /**
     * Attempts to authenticate a user based on given login and password, and set internal `user_id` accordingly.
     *
     * @throws \Exception    Raises an exception in case the credentials are not related to a user.
     */
    public function authenticate($login, $password) {
        $orm = $this->container->get('orm');

        $errors = $orm->validate('core\User', [], ['login' => $login, 'password' => $password]);
        if(count($errors)) {
            // #memo - invalid provided password counts as an attempt and returns a HTTP 401
            throw new \Exception('invalid_credentials', EQ_ERROR_INVALID_USER);
        }

        $ids = $orm->search('core\User', ['login', '=', $login]);
        if(!is_array($ids) || !count($ids)) {
            throw new \Exception('invalid_credentials', EQ_ERROR_INVALID_USER);
        }

        $list = $orm->read('core\User', $ids, ['id', 'login', 'password', 'allow_auth']);
        $user = current($list);

        if(!$user['allow_auth']) {
            throw new \Exception('authentication_not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        if(!password_verify($password, $user['password'])) {
            throw new \Exception('invalid_credentials', EQ_ERROR_INVALID_USER);
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
    public function su(int $user_id = EQ_ROOT_USER_ID) {
        if($user_id >= 0) {
            // update current user identifier
            $this->authenticated_user_id = $user_id;
            $this->user_id = $user_id;
        }
        return $this;
    }

    /**
     * Checks whether the given user exists and is allowed to authenticate.
     *
     * @throws \Exception
     */
    private function assertActiveUser(int $user_id): void {
        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        $list = $orm->read('core\User', [$user_id], ['id', 'deleted', 'validated', 'status']);

        if(!is_array($list) || !count($list)) {
            throw new \Exception('non_existing_user', EQ_ERROR_INVALID_USER);
        }

        $user = current($list);

        if($user['deleted'] || !$user['validated'] || !in_array($user['status'], ['validated', 'confirmed'], true)) {
            throw new \Exception('invalid_user', EQ_ERROR_INVALID_USER);
        }
    }

    /**
     * Checks whether the given user exists.
     *
     * @throws \Exception
     */
    private function assertExistingUser(int $user_id): void {
        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        $list = $orm->read('core\User', [$user_id], ['id']);

        if(!is_array($list) || !count($list)) {
            throw new \Exception('non_existing_user', EQ_ERROR_INVALID_USER);
        }
    }

    /**
     * Resolves the final user id by applying impersonation settings, if any.
     *
     * The given user id is the authenticated user id. It has already been validated as an active user before this method is called.
     * The impersonation target only needs to exist. It does not need to be active, validated or confirmed.
     *
     * @param int $user_id Authenticated user id.
     * @return int Final resolved user id.
     */
    private function resolveUserId(int $user_id): int {
        if($user_id <= 0) {
            return 0;
        }

        $allowed = Setting::get_value(
            'core', 'security', 'impersonation.allowed',
            false,
            ['user_id' => $user_id]
        );

        if(!$allowed) {
            return $user_id;
        }

        $enabled = Setting::get_value(
            'core', 'security', 'impersonation.enabled',
            false,
            ['user_id' => $user_id]
        );

        if(!$enabled) {
            return $user_id;
        }

        $target_user_id = (int) Setting::get_value(
            'core', 'security', 'impersonation.user_id',
            0,
            ['user_id' => $user_id]
        );

        if(!$target_user_id || $target_user_id <= 0) {
            return $user_id;
        }

        if($target_user_id === $user_id) {
            return $user_id;
        }

        $expiry = (int) Setting::get_value(
            'core', 'security', 'impersonation.expiry',
            null,
            ['user_id' => $user_id]
        );

        if($expiry && $expiry < time()) {
            return $user_id;
        }

        try {
            $this->assertExistingUser($target_user_id);
        }
        catch(\Exception $e) {
            trigger_error("AAA::invalid impersonation target: " . $e->getMessage(), EQ_REPORT_WARNING);
            return $user_id;
        }

        return $target_user_id;
    }
}
