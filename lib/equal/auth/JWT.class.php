<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\auth;

class JWT {
    /**
     * Decodes a JWT string into a PHP array mapping 'header', 'payload' and 'signature' with their values.
     *
     * @param 	string      $jwt    The JSON Web Token to decode.
     *
     * @return 	array      	A map holding JWT header, payload, and signature
     * @throws 	Exception
     *
     * @uses self::urlSafeBase64Decode
     */
    public static function decode($jwt) {
        $header  	= [];
        $payload 	= [];
        $signature 	= '';

        $parts = explode('.', $jwt);

        if (count($parts) != 3) {
            throw new \Exception('jwt_malformed_token');
        }

        [$head_b64, $body_b64, $sig_b64] = $parts;

        if ( !($header = json_decode(self::urlSafeBase64Decode($head_b64), true)) ) {
            throw new \Exception('jwt_header_unreadable');
        }
        if ( !isset($header['alg']) ) {
            throw new \Exception('jwt_header_missing_algorithm');
        }
        if ( !($payload = json_decode(self::urlSafeBase64Decode($body_b64), true)) ) {
            throw new \Exception('jwt_payload_unreadable');
        }
        if ( !($signature = self::urlSafeBase64Decode($sig_b64)) ) {
            throw new \Exception('jwt_signature_unreadable');
        }
        return [
                'header'    => $header,
                'payload'   => $payload,
                'signature' => $signature
            ];
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm.
     *                     Supported algorithms: 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'
     *
     * @return string      A signed JSON web token (JWT are URL-safe base64 encoded).
     * @uses urlSafeBase64Encode
     */
    public static function encode($payload, $key, $algo = 'HS256') {
        $header = [
                'alg' => $algo,
                'typ' => 'JWT'
            ];

        $parts = [];

        $parts[] = self::urlSafeBase64Encode(json_encode($header));
        $parts[] = self::urlSafeBase64Encode(json_encode($payload));

        $data = implode('.', $parts);

        $signature = self::sign($data, $key, $algo);

        $parts[] = self::urlSafeBase64Encode($signature);

        return implode('.', $parts);
    }


    public static function verify($msg, $sig, $key, $alg) {
        $res = 0;
        // for algo to support, @see https://tools.ietf.org/html/rfc7518#section-3
        if( in_array($alg, ['HS256', 'HS384', 'HS512']) ) {
            if ($sig == self::sign($msg, $key, $alg)) {
                $res = 1;
            }
        }
        elseif( in_array($alg, ['RS256', 'RS384', 'RS512']) ){
            $alg_map = [
                'RS256' => OPENSSL_ALGO_SHA256,
                'RS384' => OPENSSL_ALGO_SHA384,
                'RS512' => OPENSSL_ALGO_SHA512
            ];
            $res = openssl_verify(
                $msg,  	      	// base64 encoded header.payload
                $sig,           // binary value of token signature
                $key,           // PEM formatted public key
                $alg_map[$alg]  // OPENSSL algo to use
            );
        }
        else {
            throw new \Exception("algorithm_not_supported");
        }

        return $res;
    }

    /**
     * Sign a string using a given key and algorithm.
     *
     * @param string $msg    The message to sign.
     * @param string $key    The secret key. For RSA algorithms, this is the PEM formatted private key.
     * @param string $method The signing algorithm. Supported algorithms are 'HS256', 'HS384' and 'HS512'.
     *
     * @return string    An encrypted message
     * @throws Exception Unsupported algorithm was specified
     */
    private static function sign($msg, $key, $method = 'HS256') {
        $methods = [
                'HS256' => 'sha256',
                'HS384' => 'sha384',
                'HS512' => 'sha512',
                'RS256' => OPENSSL_ALGO_SHA256,
                'RS384' => OPENSSL_ALGO_SHA384,
                'RS512' => OPENSSL_ALGO_SHA512
            ];

        if( in_array($method, ['HS256', 'HS384', 'HS512']) ) {
            return hash_hmac($methods[$method], $msg, $key, true);
        }
        elseif( in_array($method, ['RS256', 'RS384', 'RS512']) ) {
            $key = openssl_pkey_get_private($key);
            if(!$key) {
                throw new \Exception('invalid_private_key');
            }
            if(!openssl_sign($msg, $signature, $key, $methods[$method])) {
                throw new \Exception('signature_failed');
            }
            return $signature;
        }
        else {
            throw new \Exception('algorithm_not_supported');
        }
    }

    private static function encodeLength($length) {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }

    /**
     * Generates a PEM formatted public key from a modulus and an exponent
     */
    public static function rsaToPem($n, $e) {
        $modulus = self::urlSafeBase64Decode($n);
        $publicExponent = self::urlSafeBase64Decode($e);

        $components = [
                'modulus'        => pack('Ca*a*', 2, self::encodeLength(strlen($modulus)), $modulus),
                'publicExponent' => pack('Ca*a*', 2, self::encodeLength(strlen($publicExponent)), $publicExponent)
            ];

        $RSAPublicKey = pack(
                'Ca*a*a*',
                48,
                self::encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
                $components['modulus'],
                $components['publicExponent']
            );

        // sequence for rsaEncryption: oid(1.2.840.113549.1.1.1), null
        // #memo - hex version of MA0GCSqGSIb3DQEBAQUA
        $rsaOID = pack('H*', '300d06092a864886f70d0101010500');
        $RSAPublicKey = chr(0) . $RSAPublicKey;
        $RSAPublicKey = chr(3) . self::encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;

        $RSAPublicKey = pack(
            'Ca*a*',
            48,
            self::encodeLength(strlen($rsaOID . $RSAPublicKey)),
            $rsaOID . $RSAPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\r\n" . chunk_split(base64_encode($RSAPublicKey), 64) . '-----END PUBLIC KEY-----';
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    private static function urlSafeBase64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $pad_len = 4 - $remainder;
            $input .= str_repeat('=', $pad_len);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    private static function urlSafeBase64Encode($input) {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

}