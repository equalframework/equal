<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
	 * @uses self::urlsafeB64Decode
	 */
	public static function decode($jwt) {
		$header  	= [];
        $payload 	= [];
		$signature 	= '';

		$parts = explode('.', $jwt);

		if (count($parts) != 3) {
			throw new \Exception('jwt_malformed_token');
		}

		list($headb64, $bodyb64, $sigb64) = $parts;

		if ( !($header = json_decode(self::urlsafeB64Decode($headb64), true)) ) {
			throw new \Exception('jwt_header_unreadable');
		}
		if ( !isset($header['alg']) ) {
			throw new \Exception('jwt_header_missing_algorithm');
		}
		if ( !($payload = json_decode(self::urlsafeB64Decode($bodyb64), true)) ) {
			throw new \Exception('jwt_payload_unreadable');
		}
		if ( !($signature = self::urlsafeB64Decode($sigb64)) ) {
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
	 * @param string       $algo    The signing algorithm. Supported
	 *                              algorithms are 'HS256', 'HS384' and 'HS512'
	 *
	 * @return string      A signed JSON web token (JWT are URL-safe base64 encoded).
	 * @uses urlsafeB64Encode
	 */
	public static function encode($payload, $key, $algo = 'HS256') {
		$header = [
                'typ' => 'JWT',
                'alg' => $algo
            ];
		$segments = [];
		$segments[] = self::urlsafeB64Encode(json_encode($header));
		$segments[] = self::urlsafeB64Encode(json_encode($payload));
		$signing_input = implode('.', $segments);
		$signature = self::sign($signing_input, $key, $algo);
		$segments[] = self::urlsafeB64Encode($signature);
		return implode('.', $segments);
	}


	public static function verify($msg, $sig, $key, $alg) {
		$res = 0;
		// for algo to support, @see https://tools.ietf.org/html/rfc7518#section-3
		if( in_array($alg, ['HS256', 'HS384', 'HS512']) ) {
			if ($sig == self::sign($msg, $key, $alg)) {
				$res = 1;
			}
		}
		else if( in_array($alg, ['RS256', 'RS384', 'RS512']) ){
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
			throw new \Exception("JWT_non_supported_alg");
		}

		return $res;
	}

	/**
	 * Sign a string with a given key and algorithm.
	 *
	 * @param string $msg    The message to sign.
	 * @param string $key    The secret key.
	 * @param string $method The signing algorithm. Supported algorithms are 'HS256', 'HS384' and 'HS512'.
	 *
	 * @return string    An encrypted message
	 * @throws Exception Unsupported algorithm was specified
	 */
	private static function sign($msg, $key, $method = 'HS256') {
		$methods = array(
			'HS256' => 'sha256',
			'HS384' => 'sha384',
			'HS512' => 'sha512'
		);
		if (!in_array($method, array_keys($methods))) {
			throw new \Exception('algorithm_not_supported');
		}
		return hash_hmac($methods[$method], $msg, $key, true);
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
        $modulus = self::urlsafeB64Decode($n);
        $publicExponent = self::urlsafeB64Decode($e);

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

		// hex version of MA0GCSqGSIb3DQEBAQUA
        $rsaOID = pack('H*', '300d06092a864886f70d0101010500');
        $RSAPublicKey = chr(0) . $RSAPublicKey;
        $RSAPublicKey = chr(3) . self::encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;

        $RSAPublicKey = pack(
            'Ca*a*',
            48,
            self::encodeLength(strlen($rsaOID . $RSAPublicKey)),
            $rsaOID . $RSAPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\r\n".chunk_split(base64_encode($RSAPublicKey), 64).'-----END PUBLIC KEY-----';
    }


	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function urlsafeB64Decode($input) {
		$remainder = strlen($input) % 4;
		if ($remainder) {
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
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
	public static function urlsafeB64Encode($input) {
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

}