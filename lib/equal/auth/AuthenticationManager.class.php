<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\auth;

use core\security\AccessToken;
use core\security\factor\TotpKey;
use core\setting\Setting;
use core\User;
use equal\organic\Service;
use equal\orm\ObjectManager;
use equal\services\Container;

class AuthenticationManager extends Service {

    /**
     * Prefix used for Authentication Context Class Reference values.
     */
    private const ACR_PREFIX = 'urn:equal:auth:level:';

    /**
     * @var integer Final resolved user identifier, after applying impersonation if any.
     */
    private $user_id;

    /**
     * @var integer Authenticated user identifier, before applying impersonation.
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
     * Returns the highest non-expired authentication level granted by the current JWT token.
     *
     * Without a JWT, a previously resolved authenticated identity has level 1; otherwise the level is 0.
     */
    public function getAuthLevel($token = null) {
        $jwt = $this->retrieveAccessToken($token);

        if(!$jwt) {
            return is_null($token) && $this->authenticated_user_id > 0 ? 1 : 0;
        }

        return $this->resolveAuthLevel($this->extractAuthMethods($jwt));
    }

    /**
     * Provide a JWT token based on given user (or current user if known) and `AUTH_SECRET_KEY`.
     *
     * The JWT access token is built on a payload holding:
     *   - id  : the user identifier
     *   - sub : the user identifier
     *   - amr : standard Authentication Methods References as a list of strings
     *   - auth: detailed authentication methods (`method`, `exp`)
     *   - acr : assurance level resolved when this JWT representation is issued
     *   - iat : the datetime (timestamp) at which the token was issued
     *   - exp : (optional) the datetime (timestamp) at which the token expires
     *   - trk : is the token tracked or not
     *   - jti : (optional) id of the token to allow tracking
     *
     * Example:
     * {
     *     "id": 42,
     *     "sub": 42,
     *     "iat": 1760000000,
     *     "auth": [
     *         {
     *             "method": "pwd",
     *             "exp": 1760003600
     *         },
     *         {
     *             "method": "totp",
     *             "exp": 1760000900
     *         }
     *     ],
     *     "amr": [
     *         "pwd",
     *         "otp"
     *     ],
     *     "acr": "urn:equal:auth:level:2",
     *     "exp": 1760003600,
     *     "trk": true,
     *     "jti": 123
     * }
     *
     * The third argument accepts a list of authentication methods. A single method
     * remains accepted, and its historical singular parameter name is preserved,
     * so existing positional and named calls remain compatible.
     *
     * @param   int $user_id        identifier of the user for whom a token is requested
     * @param   int $validity       validity duration in seconds
     * @param   array $auth_method  authentication methods, or a single method for backward compatibility
     * @param   int $jti            id of the AccessToken to track it (non-tracked tokens are stateless)
     * @return  string              token using JWT format (https://tools.ietf.org/html/rfc7519)
     */
    public function token(int $user_id = 0, int $validity = 0, array $auth_method = [], int $jti = 0) {
        $issued_at = time();
        $token_expiry = $validity ? $issued_at + $validity : PHP_INT_MAX;

        // Keep accepting the historical single-method argument shape.
        $auth_methods = isset($auth_method['method']) ? [$auth_method] : $auth_method;
        $auth_methods = $this->extractAuthMethods(['auth' => $auth_methods], 'invalid_authentication');

        $payload = [
            // internal user identifier (non-standard claim)
            'id'    => $user_id ?: $this->user_id,

            // subject of the token (standard JWT claim) - represents the authenticated user
            'sub'   => $user_id ?: $this->user_id,

            // Issued At (standard JWT claim) - timestamp when the token was generated
            'iat'   => $issued_at,

            // Tracking flag (non-standard claim) - Indicates whether the token is tracked server-side (e.g. stored in DB)
            // If true, the token can be revoked (blacklist check required)
            // If false, the token is considered stateless (no server-side validation beyond signature/exp)
            'trk'   => $jti > 0
        ];

        $payload['auth'] = array_values($auth_methods);
        $payload['amr'] = $this->extractAmrReferences($auth_methods);
        $payload['acr'] = $this->extractAuthState($auth_methods);

        // handle expiry
        if($validity) {
            $payload['exp'] = $token_expiry;
        }
        // handle token id for tracking
        if($jti > 0) {
            $payload['jti'] = $jti;
        }

        return $this->encodeToken($payload);
    }

    /**
     * Issue a short-lived token carrying a password proof for a specific MFA flow.
     *
     * MFA challenge tokens deliberately omit the access-token `id` claim so they
     * cannot be accepted by `retrieveAccessToken()` as authenticated sessions.
     *
     * @param int    $user_id      identifier of the user who passed password authentication
     * @param string $mfa_method   MFA method that is allowed to consume the challenge (totp, email_otp)
     * @param int    $validity     challenge validity duration in seconds
     * @param array  $auth_methods authentication methods established before the challenge
     * @return string              signed MFA challenge using JWT format
     */
    public function issueMfaChallengeToken(
        int $user_id,
        string $mfa_method,
        int $validity,
        array $auth_methods
    ): string {
        $issued_at = time();

        if($user_id <= 0) {
            throw new \Exception('invalid_user', EQ_ERROR_INVALID_USER);
        }

        if($mfa_method === '' || $validity <= 0) {
            throw new \Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
        }

        $auth_methods = $this->extractAuthMethods(
            ['auth' => $auth_methods],
            'invalid_authentication'
        );

        $has_active_password_proof = false;
        foreach($auth_methods as $auth_method) {
            if($auth_method['method'] === 'pwd' && $auth_method['exp'] >= $issued_at) {
                $has_active_password_proof = true;
                break;
            }
        }

        if(!$has_active_password_proof) {
            throw new \Exception('invalid_authentication', EQ_ERROR_INVALID_PARAM);
        }

        return $this->encodeToken([
            'type'          => 'mfa_challenge',
            'mfa_method'    => $mfa_method,
            'auth'          => $auth_methods,
            'amr'           => $this->extractAmrReferences($auth_methods),
            'sub'           => $user_id,
            'iat'           => $issued_at,
            'exp'           => $issued_at + $validity
        ]);
    }

    /**
     * Verify and return a signed MFA challenge for the expected MFA flow.
     *
     * @param string $token               signed MFA challenge
     * @param string $expected_mfa_method MFA method attempting to consume the challenge
     * @return array                      validated challenge payload
     * @throws \Exception
     */
    public function verifyMfaChallengeToken(string $token, string $expected_mfa_method): array {
        try {
            $decoded = $this->decodeToken($token);

            if(
                !is_array($decoded)
                || ($decoded['header']['alg'] ?? null) !== 'HS256'
                || !$this->verifyToken($token, constant('AUTH_SECRET_KEY'))
            ) {
                throw new \Exception('invalid_token');
            }
        }
        catch(\Throwable $exception) {
            throw new \Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
        }

        $payload = $decoded['payload'] ?? null;
        if(
            !is_array($payload)
            || ($payload['type'] ?? null) !== 'mfa_challenge'
            || !is_string($payload['mfa_method'] ?? null)
            || $payload['mfa_method'] === ''
            || $payload['mfa_method'] !== $expected_mfa_method
            || !is_int($payload['sub'] ?? null)
            || $payload['sub'] <= 0
            || !is_int($payload['iat'] ?? null)
            || !is_int($payload['exp'] ?? null)
            || !is_array($payload['auth'] ?? null)
            || !is_array($payload['amr'] ?? null)
            || array_key_exists('id', $payload)
        ) {
            throw new \Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
        }

        $now = time();
        if($payload['iat'] > $now || $payload['exp'] < $now) {
            throw new \Exception('expired_token', EQ_ERROR_INVALID_PARAM);
        }

        $auth_methods = $this->extractAuthMethods($payload, 'invalid_token');
        if(
            $payload['auth'] !== $auth_methods
            || ($payload['amr'] ?? null) !== $this->extractAmrReferences($auth_methods)
        ) {
            throw new \Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
        }

        $has_active_password_proof = false;
        foreach($auth_methods as $auth_method) {
            if($auth_method['method'] === 'pwd' && $auth_method['exp'] >= $now) {
                $has_active_password_proof = true;
                break;
            }
        }

        if(!$has_active_password_proof) {
            throw new \Exception('expired_token', EQ_ERROR_INVALID_PARAM);
        }

        return $payload;
    }

    /**
     * Issue an access token after a controller has verified an authentication method.
     *
     * When the request already carries a valid access token, the method is added
     * without extending the token lifetime. Otherwise a new token is created and
     * any methods established by a preceding authentication challenge are restored.
     *
     * Expected `$auth_method` structure:
     * - `method` (string): non-empty authentication method identifier
     * - `exp` (int): Unix timestamp until which the method remains valid
     *
     * @param int   $user_id               identifier of the user authenticated by the method
     * @param array $auth_method           verified authentication method
     * @param array $previous_auth_methods methods established before the current method
     * @return string                      token using JWT format
     */
    public function issueAccessToken(int $user_id, array $auth_method, array $previous_auth_methods = []): string {
        if($user_id <= 0) {
            throw new \Exception('invalid_user', EQ_ERROR_INVALID_USER);
        }

        $jwt = $this->retrieveAccessToken();
        if($jwt) {
            if((int) $jwt['id'] !== $user_id) {
                throw new \Exception('authenticated_user_mismatch', EQ_ERROR_NOT_ALLOWED);
            }

            return $this->addAuthMethod($auth_method);
        }

        $auth_methods = $previous_auth_methods;
        $auth_methods[] = $auth_method;

        return $this->token(
            $user_id,
            constant('AUTH_ACCESS_TOKEN_VALIDITY'),
            $auth_methods
        );
    }

    /**
     * Add or refresh an authentication on the current JWT without extending its lifetime.
     *
     */
    public function addAuthMethod(array $auth_method, $jwt = null): string {
        $jwt = $this->retrieveAccessToken($jwt);
        if(is_null($jwt)) {
            throw new \Exception('unable_to_retrieve_access_token');
        }

        [$auth_method] = $this->extractAuthMethods(['auth' => [$auth_method]], 'invalid_auth_method');

        $auth_methods = $this->extractAuthMethods($jwt);

        foreach($auth_methods as $index => $existing_auth_method) {
            if($existing_auth_method['method'] === $auth_method['method']) {
                unset($auth_methods[$index]);
            }
        }
        $auth_methods[] = $auth_method;
        $auth_methods = array_values($auth_methods);

        $jwt['auth'] = $auth_methods;
        $jwt['amr'] = $this->extractAmrReferences($auth_methods);
        $jwt['acr'] = $this->extractAuthState($auth_methods);

        return $this->encodeToken($jwt);
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
            'iat'   => time(),
            'trk'   => $jwt['trk'] ?? false,
            'exp'   => time() + $validity
        ];

        $auth_methods = $this->extractAuthMethods($jwt);

        $payload['auth'] = $auth_methods;
        $payload['amr'] = $this->extractAmrReferences($auth_methods);
        $payload['acr'] = $this->extractAuthState($auth_methods);

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
     * @deprecated  use `::issueTrackedAccessToken()`, `::issueAccessToken()` or `::token()` instead
     */
    public function createAccessToken(int $user_id, int $validity = 0) {
        return $this->issueTrackedAccessToken($user_id, $validity);
    }

    public function issueTrackedAccessToken(int $user_id, int $validity = 0) {
        $accessToken = AccessToken::create([
                'user_id'   => $user_id,
                'expiry'    => ($validity) ? (time() + $validity) : null
            ])
            ->first();

        $auth_method = [
            'method'    => 'token',
            'exp'       => $validity ? time() + $validity : PHP_INT_MAX
        ];

        return $this->token($user_id, $validity, $auth_method, $accessToken['id']);
    }

    /**
     * Return authentication methods from the private auth claim.
     *
     * Legacy fields such as `level` are deliberately ignored because assurance is
     * resolved from the complete set of methods. When an error key is provided,
     * invalid methods are rejected; otherwise they are ignored so legacy tokens
     * with an unusable method remain readable.
     */
    private function extractAuthMethods(array $jwt, ?string $error_key = null): array {
        $auth_state = $jwt['auth'] ?? [];
        if(!is_array($auth_state)) {
            return [];
        }

        $result = [];
        foreach($auth_state as $auth_method) {
            if(
                !is_array($auth_method)
                || !isset($auth_method['method'], $auth_method['exp'])
                || !is_string($auth_method['method'])
                || $auth_method['method'] === ''
                || !is_int($auth_method['exp'])
            ) {
                if(!is_null($error_key)) {
                    throw new \Exception($error_key, EQ_ERROR_INVALID_PARAM);
                }
                continue;
            }

            $result[] = [
                'method'    => $auth_method['method'],
                'exp'       => $auth_method['exp']
            ];
        }

        return $result;
    }

    /**
     * Resolve assurance from non-expired authentication methods according to the eQual policy.
     *
     * The level numbering follows the Authentication Assurance Level (AAL) model:
     * level 1 represents a single active authentication method, while level 2
     * requires a user-verified passkey or password combined with an OTP method.
     * Level 0 is the eQual-specific unauthenticated state. This mapping is an
     * application policy inspired by AALs, not a claim of strict NIST conformance.
     *
     * @see https://pages.nist.gov/800-63-4/sp800-63b/aal/
     */
    private function resolveAuthLevel(array $auth_methods): int {
        $active_auth_methods = [];
        $now = time();

        foreach($auth_methods as $auth_method) {
            if($auth_method['exp'] >= $now) {
                $active_auth_methods[$auth_method['method']] = $auth_method;
            }
        }

        if(!$active_auth_methods) {
            return 0;
        }

        if(isset($active_auth_methods['passkey'])) {
            return 2;
        }

        if(
            isset($active_auth_methods['pwd'])
            && (isset($active_auth_methods['totp']) || isset($active_auth_methods['email_otp']))
        ) {
            return 2;
        }

        return 1;
    }

    /**
     * Return standard Authentication Method References for the given methods.
     */
    private function extractAmrReferences(array $auth_methods): array {
        $result = [];
        foreach($auth_methods as $auth_method) {
            $amr = $this->extractAmrReference($auth_method['method']);
            if(!in_array($amr, $result, true)) {
                $result[] = $amr;
            }
        }

        return $result;
    }

    /**
     * Return the Authentication Context Class Reference for the given methods.
     */
    private function extractAuthState(array $auth_methods): string {
        return self::ACR_PREFIX . $this->resolveAuthLevel($auth_methods);
    }

    /**
     * Map detailed eQual methods to standard Authentication Method References.
     */
    private function extractAmrReference(string $method): string {
        if(in_array($method, ['totp', 'email_otp'], true)) {
            return 'otp';
        }

        return $method;
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
     * @return array|null   Decoded, non-expired access token payload, including standard `amr` and private `auth` claims (@see `token()` method).
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
     * This is normally the user id provided by the access token.
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
        // unless already resolved, grant all rights when using CLI
        if($this->user_id <= 0 && php_sapi_name() === 'cli') {
            $this->user_id = EQ_ROOT_USER_ID;
        }

        // if already resolved, return user_id
        if($this->user_id > 0) {
            return $this->user_id;
        }

        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        try {
            $authenticated_user_id = 0;
            $tracked_token = null;

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
                    $tks = $orm->read(
                        'core\security\AccessToken',
                        $tk_ids,
                        ['id', 'is_revoked', 'use_count', 'use_limit']
                    );
                    $tk = current($tks);
                    if($tk && $tk['is_revoked']) {
                        // generate a 401 Unauthorized HTTP response
                        throw new \Exception('auth_revoked_token', EQ_ERROR_INVALID_USER);
                    }
                    if(
                        $tk
                        && isset($tk['use_limit'])
                        && (int) $tk['use_count'] >= (int) $tk['use_limit']
                    ) {
                        // generate a 401 Unauthorized HTTP response
                        throw new \Exception('auth_token_use_limit_reached', EQ_ERROR_INVALID_USER);
                    }
                    $tracked_token = $tk ?: null;
                }
                $authenticated_user_id = $jwt['id'];
            }
            if($authenticated_user_id > 0) {
                // validate the real authenticated user
                $this->assertActiveUser($authenticated_user_id);

                // remember authenticated user before applying impersonation
                $this->authenticated_user_id = $authenticated_user_id;

                // resolve the final user (target user may exist without being active/validated/confirmed)
                $this->user_id = $this->resolveUserId($authenticated_user_id);

                if($tracked_token && isset($tracked_token['use_limit'])) {
                    $orm->write(
                        'core\security\AccessToken',
                        [$tracked_token['id']],
                        [
                            'last_use'     => time(),
                            'use_count'    => (int) $tracked_token['use_count'] + 1
                        ]
                    );
                }
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
     * Resolve the second authentication factor required after password authentication.
     *
     * TOTP takes precedence when it is enabled and either required or already configured
     * for the user. Email OTP is used as a fallback when required by the user's settings.
     *
     * @return string|null Required MFA method, or null when no second factor is required.
     * @throws \Exception
     */
    public function getUserMfa(): ?string {
        $user_id = $this->userId();

        $user = User::id($user_id)
            ->read(['validated', 'allow_auth'])
            ->first();

        if(!$user) {
            throw new \Exception('user_not_found', EQ_ERROR_INVALID_USER);
        }

        if(!$user['validated']) {
            throw new \Exception('user_not_validated', EQ_ERROR_NOT_ALLOWED);
        }

        if(!$user['allow_auth']) {
            throw new \Exception('not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        $global_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
        $totp_enabled = Setting::get_value(
            'core',
            'security',
            'auth.totp.enabled',
            $global_totp_enabled,
            ['user_id' => $user['id']]
        );

        if($totp_enabled) {
            $totp_key = TotpKey::search([
                    ['user_id', '=', $user['id']],
                    ['type', '=', 'totp'],
                    ['status', '=', 'active']
                ])
                ->read(['failed_attempts'])
                ->first();

            $global_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
            $totp_required = Setting::get_value(
                'core',
                'security',
                'auth.password.totp_required',
                $global_totp_required,
                ['user_id' => $user['id']]
            );

            if($totp_required || $totp_key) {
                if($totp_key) {
                    $allowed_failed_attempts = Setting::get_value(
                        'core',
                        'security',
                        'auth.totp.allowed_failed_attempts',
                        5
                    );

                    if($totp_key['failed_attempts'] >= $allowed_failed_attempts) {
                        throw new \Exception('allowed_failed_attempts_reached', EQ_ERROR_NOT_ALLOWED);
                    }
                }

                return 'totp';
            }
        }

        $global_email_otp_required = Setting::get_value('core', 'security', 'auth.password.email_otp_required');
        $email_otp_required = Setting::get_value(
            'core',
            'security',
            'auth.password.email_otp_required',
            $global_email_otp_required,
            ['user_id' => $user['id']]
        );

        if($email_otp_required) {
            return 'email_otp';
        }

        return null;
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
