<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql;

use equal\data\adapt\DataAdapter;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlText implements DataAdapter {

    private const SECRET_PREFIX = 'eqsecret:v1:';

    public function getType() {
        return 'sql/text';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        return 'TEXT';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (SQL -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            $result = (string) $value;
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create((string) $usage);
            }
            if($usage->getType() === 'secret') {
                $result = $this->decryptSecret($result);
            }
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> SQL).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create((string) $usage);
        }
        $result = (string) $value;
        switch($usage->getType()) {
            case 'password':
                if(substr($result, 0, 4) != '$2y$') {
                    $result = password_hash($result, PASSWORD_BCRYPT);
                }
                break;
            case 'secret':
                $result = $this->encryptSecret($result);
                break;
        }
        return $result;
    }

    private function encryptSecret(string $value): string {
        $key = $this->getSecretKey();
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $payload = $nonce . sodium_crypto_secretbox($value, $nonce, $key);
        return self::SECRET_PREFIX . base64_encode($payload);
    }

    private function decryptSecret(string $value): string {
        $key = $this->getSecretKey();

        if(strncmp($value, self::SECRET_PREFIX, strlen(self::SECRET_PREFIX)) !== 0) {
            trigger_error('ORM::secret - Invalid secret payload format.', EQ_REPORT_ERROR);
            throw new \Exception('invalid_secret_payload', EQ_ERROR_INVALID_PARAM);
        }

        $encoded_payload = substr($value, strlen(self::SECRET_PREFIX));
        $payload = base64_decode($encoded_payload, true);
        $minimum_length = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES;

        if(
            $payload === false
            || strlen($payload) < $minimum_length
            || base64_encode($payload) !== $encoded_payload
        ) {
            trigger_error('ORM::secret - Invalid encoded secret payload.', EQ_REPORT_ERROR);
            throw new \Exception('invalid_secret_payload', EQ_ERROR_INVALID_PARAM);
        }

        $nonce = substr($payload, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($payload, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $result = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if($result === false) {
            trigger_error('ORM::secret - Secret payload authentication failed.', EQ_REPORT_ERROR);
            throw new \Exception('secret_decryption_failed', EQ_ERROR_INVALID_PARAM);
        }

        return $result;
    }

    private function getSecretKey(): string {
        if(!defined('CIPHER_KEY')) {
            trigger_error('ORM::secret - CIPHER_KEY is not defined.', EQ_REPORT_ERROR);
            throw new \Exception('secret_key_unavailable', EQ_ERROR_INVALID_CONFIG);
        }

        $secret_key = \constant('CIPHER_KEY');
        if(!is_scalar($secret_key) || strlen((string) $secret_key) === 0) {
            trigger_error('ORM::secret - CIPHER_KEY is empty or invalid.', EQ_REPORT_ERROR);
            throw new \Exception('secret_key_unavailable', EQ_ERROR_INVALID_CONFIG);
        }

        return hash('sha256', (string) $secret_key, true);
    }

}
