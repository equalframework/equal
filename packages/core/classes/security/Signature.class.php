<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class Signature extends Model {

    const OID_MAP = [
        // ==== RSA PKCS#1 v1.5 + SHA-2 ====
        // signatureAlgorithm OIDs: sha{224,256,384,512}WithRSAEncryption
        // DigestInfo = SEQUENCE { SEQUENCE { OID shaX, NULL }, OCTET STRING <hash> }
        '1.2.840.113549.1.1.14' => [                                            // sha224WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA-224',
            'digestOid'            => '2.16.840.1.101.3.4.2.4',
            'digestDerHex'         => '0609608648016503040204',                 // OID(sha224) DER
            'digestLengthBytes'    => 28,
            'digestInfoPrefixHex'  => '302D300D06096086480165030402040500041C', // +28B hash
            'opensslDigestName'    => 'sha224'
        ],
        '1.2.840.113549.1.1.11' => [                                            // sha256WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA-256',
            'digestOid'            => '2.16.840.1.101.3.4.2.1',
            'digestDerHex'         => '0609608648016503040201',                 // OID(sha256) DER
            'digestLengthBytes'    => 32,
            'digestInfoPrefixHex'  => '3031300D060960864801650304020105000420', // +32B hash
            'opensslDigestName'    => 'sha256'
        ],
        '1.2.840.113549.1.1.12' => [                                            // sha384WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA-384',
            'digestOid'            => '2.16.840.1.101.3.4.2.2',
            'digestDerHex'         => '0609608648016503040202',                 // OID(sha384) DER
            'digestLengthBytes'    => 48,
            'digestInfoPrefixHex'  => '3041300D060960864801650304020205000430', // +48B hash
            'opensslDigestName'    => 'sha384'
        ],
        '1.2.840.113549.1.1.13' => [                                            // sha512WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA-512',
            'digestOid'            => '2.16.840.1.101.3.4.2.3',
            'digestDerHex'         => '0609608648016503040203',                 // OID(sha512) DER
            'digestLengthBytes'    => 64,
            'digestInfoPrefixHex'  => '3051300D060960864801650304020305000440', // +64B hash
            'opensslDigestName'    => 'sha512'
        ],

        // ==== RSA PKCS#1 v1.5 + SHA-3 ====
        // signatureAlgorithm OIDs: sha3-{224,256,384,512}WithRSAEncryption
        // DigestInfo uses the corresponding SHA-3 OID (+ NULL)
        '2.16.840.1.101.3.4.3.13' => [ // sha3-224WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA3-224',
            'digestOid'            => '2.16.840.1.101.3.4.2.7',
            'digestDerHex'         => '0609608648016503040207',                 // OID(sha3-224) DER
            'digestLengthBytes'    => 28,
            'digestInfoPrefixHex'  => '302D300D06096086480165030402070500041C',
            'opensslDigestName'    => 'sha3-224'
        ],
        '2.16.840.1.101.3.4.3.14' => [                                          // sha3-256WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA3-256',
            'digestOid'            => '2.16.840.1.101.3.4.2.8',
            'digestDerHex'         => '0609608648016503040208',                 // OID(sha3-256) DER
            'digestLengthBytes'    => 32,
            'digestInfoPrefixHex'  => '3031300D060960864801650304020805000420',
            'opensslDigestName'    => 'sha3-256'
        ],
        '2.16.840.1.101.3.4.3.15' => [                                          // sha3-384WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA3-384',
            'digestOid'            => '2.16.840.1.101.3.4.2.9',
            'digestDerHex'         => '0609608648016503040209',                 // OID(sha3-384) DER
            'digestLengthBytes'    => 48,
            'digestInfoPrefixHex'  => '3041300D060960864801650304020905000430',
            'opensslDigestName'    => 'sha3-384'
        ],
        '2.16.840.1.101.3.4.3.16' => [                                          // sha3-512WithRSAEncryption
            'cryptoAlgorithm'      => 'RSA',
            'paddingScheme'        => 'PKCS1.5',
            'hashFunction'         => 'SHA3-512',
            'digestOid'            => '2.16.840.1.101.3.4.2.10',
            'digestDerHex'         => '060960864801650304020A',                 // OID(sha3-512) DER
            'digestLengthBytes'    => 64,
            'digestInfoPrefixHex'  => '3051300D060960864801650304020A05000440',
            'opensslDigestName'    => 'sha3-512'
        ],

        // ==== RSA-PSS (to be handled separately) ====
        // Unique OID (rsassaPss). The hash/mgf1/saltLen are in the DER parameters.
        '1.2.840.113549.1.1.10' => [ 'cryptoAlgorithm' => 'RSA', 'hashFunction' => null, 'paddingScheme' => 'PSS' ],

        // ==== ECC ECDSA ====
        '1.2.840.10045.4.3.1'  => ['cryptoAlgorithm' => 'ECC', 'hashFunction' => 'SHA-224', 'paddingScheme' => 'NONE'],
        '1.2.840.10045.4.3.2'  => ['cryptoAlgorithm' => 'ECC', 'hashFunction' => 'SHA-256', 'paddingScheme' => 'NONE'],
        '1.2.840.10045.4.3.3'  => ['cryptoAlgorithm' => 'ECC', 'hashFunction' => 'SHA-384', 'paddingScheme' => 'NONE'],
        '1.2.840.10045.4.3.4'  => ['cryptoAlgorithm' => 'ECC', 'hashFunction' => 'SHA-512', 'paddingScheme' => 'NONE']

    ];

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcName',
                'store'             => true
            ],

            'data_digest' => [
                'type'              => 'string',
                'usage'             => 'text/plain:64',
                'description'       => 'Digest of the original data signed by the signer.',
                'help'              => 'As a convention, it is not the  full data that is signed, but only its digest.
                    This field holds the hexadecimal representation of the value of the hash of the raw data, and might require a conversion to binary.
                    Only SHA-256 is supported for now.',
                'readonly'          => true,
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_method' => [
                'type'              => 'string',
                'selection'         => ['ses', 'aes', 'qes'],
                'required'          => true,
                'description'       => 'eIDAS signature level (ses = drawn).'
            ],

            'has_certificate' => [
                'type'              => 'computed',
                'result_type'       => 'boolean',
                'function'          => 'calcHasCertificate',
                'store'             => true,
                'description'       => 'True if a certificate is attached.',
                'help'              => 'This flag is set manually when a certificate is attached to the Signature.',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_drawn' => [
                'type'              => 'binary',
                'usage'             => 'image/png.signature',
                'description'       => 'Handwritten signature (PNG), if present.',
                'visible'           => ['sig_method', '=', 'ses'],
            ],

            'sig_json_cms' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'usage'             => 'text/json.small',
                'description'       => 'JSON CMS (Cryptographic Message Syntax) representation of the signature.',
                'function'          => 'calcJsonCms',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_cert' => [
                'type'              => 'binary',
                // #todo - not supported yes by UsageFactory
                /*'usage'             => 'application/pkix-cert',*/
                'description'       => 'X.509 certificate of the signer, as DER-encoded binary value.',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']],
                'dependents'        => ['has_certificate']
            ],

            'sig_hash' => [
                'type'              => 'string',
                'usage'             => 'text/plain:1000',
                'description'       => 'Cryptographic signature (signed hash).',
                'help'              => 'Base64 value of the cryptographic signature (RSA or ECDSA).',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']],
            ],

            'sig_algo_oid' => [
                'type'              => 'string',
                'description'       => 'Signature algorithm, e.g., RSA, ECC',
                'dependents'        => ['sig_algo', 'sig_padding', 'sig_hash_func'],
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_algo' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcSigAlgo',
                'store'             => true,
                'description'       => 'Signature algorithm, e.g., RSA, ECC',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_hash_func' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcSigHashFunc',
                'store'             => true,
                'description'       => 'Signature algorithm, e.g., RSA, ECC',
                'visible'           => ['sig_method', 'in', ['aes', 'qes']]
            ],

            'sig_padding' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcSigPadding',
                'store'             => true,
                'description'       => 'Public key padding scheme (RSA only). PKCS1.5, PSS, OAEP, NONE.',
                'help'              => 'Required for RSA signatures. NONE for algorithms without padding (e.g., ECDSA).',
                'visible'           => [['sig_method', 'in', ['aes', 'qes']], ['sig_algo', '=', 'RSA']]
            ],

            'sig_timestamp' => [
                'type'              => 'datetime',
                'description'       => 'Timestamp of the signature.',
                'default'           => function () { return time(); },
                'readonly'          => true
            ],

            'is_valid' => [
                'type'              => 'computed',
                'result_type'       => 'boolean',
                'description'       => 'Result of the check of signature digest using Certificate public key.',
                'function'          => 'calcIsValid',
                'store'             => false
            ],

        ];
    }

    protected static function calcName($self) {
        $result = [];
        $self->read(['sig_method', 'sig_algo_oid', 'sig_hash_func']);

        foreach($self as $id => $signature) {
            $algo = self::OID_MAP[$signature['sig_algo_oid']] ?? null;
            $result[$id] = $signature['sig_method'] . ' signature';
            if($algo) {
                $result[$id] .= ' (' . $algo['cryptoAlgorithm'] . ', ' . $algo['hashFunction'] . ')';
            }
        }
        return $result;
    }

    protected static function calcHasCertificate($self) {
        $result = [];
        $self->read(['sig_drawn', 'sig_cert']);
        foreach($self as $id => $signature) {
            if(strlen($signature['sig_cert']) > 0) {
                $result[$id] = true;
            }
            elseif(strlen($signature['sig_drawn']) > 0) {
                $result[$id] = false;
            }
        }
        return $result;
    }

    /**
     * Generate a JSON CMS (Cryptographic Message Syntax).
     * #todo - this should be done as a dedicated controller
     */
    protected static function calcJsonCms($self) {
        $result = [];

        $self->read(['data_digest', 'sig_algo_oid', 'sig_hash_func', 'sig_hash', 'sig_cert', 'sig_timestamp']);

        foreach($self as $id => $signature) {
            $result[$id] = json_encode([
                'hashFunction'              => $signature['sig_hash_func'],
                'hashValue'                 => base64_encode(hex2bin($signature['data_digest'])),
                'signatureAlgorithmOID'     => $signature['sig_algo_oid'],
                'signatureValue'            => $signature['sig_hash'],
                'certificate'               => self::computePemFromCert($signature['sig_cert']),
                'signatureFormat'           => 'raw-digest-signature'
            ], JSON_PRETTY_PRINT);
        }
        return $result;
    }

    protected static function calcIsValid($self) {
        $result = [];
        $self->read(['data_digest', 'sig_algo_oid', 'sig_hash', 'sig_cert']);
        foreach($self as $id => $signature) {
            $result[$id] = self::computeSignatureValidation(
                    $signature['data_digest'],
                    $signature['sig_algo_oid'],
                    $signature['sig_hash'],
                    $signature['sig_cert']
                );
        }
        return $result;
    }

    private static function computePemFromCert(string $cert) {
        $pem =  "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($cert), 64, "\n")
            . "-----END CERTIFICATE-----\n";

        return $pem;
    }

    private static function computePublicKeyFromPem(string $pem) {
        $x509 = openssl_x509_read($pem);
        if ($x509 === false) {
            throw new \Exception('invalid_X509_cert', EQ_ERROR_UNKNOWN);
        }
        $pub  = openssl_pkey_get_public($x509);
        if ($pub === false) {
            throw new \Exception('missing_public_key_cert', EQ_ERROR_UNKNOWN);
        }
        return $pub;
    }

    private static function computeSignerInfoFromPem(string $pem) {
        $parsed = openssl_x509_parse($pem, false);

        if(!$parsed || !isset($parsed['subject'])) {
            throw new \Exception('invalid_X509_cert', EQ_ERROR_UNKNOWN);
        }

        $subject = $parsed['subject'] ?? [];

        $firstname_words = preg_split('/\s+/', $subject['givenName']);
        $lastname_words = preg_split('/\s+/', $subject['surname']);

        $common_name_words = preg_split('/\s+/', preg_replace('/\s*\(.*\)$/u', '', $subject['commonName']));

        $intersectWords = function (array $a, array $b) {
            $aLower = array_map('mb_strtolower', $a);
            $bLower = array_map('mb_strtolower', $b);
            $inter = array_uintersect_assoc($a, $a, function ($w1, $w2) use ($aLower, $bLower) {
                return (in_array(mb_strtolower($w1), $bLower)) ? 0 : 1;
            });
            return $inter;
        };

        $result = [
            'firstname'                 => implode(' ', $intersectWords($common_name_words, $firstname_words)),
            'lastname'                  => implode(' ', $intersectWords($common_name_words, $lastname_words)),
            'citizen_identification'    => $subject['serialNumber'] ?? '',
        ];

        return $result;
    }

    private static function computePublicKeyFromCert(string $cert) {
        $pem = self::computePemFromCert($cert);
        return self::computePublicKeyFromPem($pem);
    }

    private static function computeSignatureValidation($data_digest, $sig_algo_oid, $sig_hash, $sig_cert) {
        $algo = self::OID_MAP[$sig_algo_oid] ?? null;
        if(!$algo) {
            // Signature algorithm not supported
            trigger_error('APP::unsupported signature algorithm: ' . $sig_algo_oid, EQ_REPORT_WARNING);
            return false;
        }

        try {
            $pubKey = self::computePublicKeyFromCert($sig_cert);
        }
        catch(\Throwable $e) {
            trigger_error('APP::unable to compute public key from certificate: ' . $e->getMessage(), EQ_REPORT_ERROR);
            return false;
        }

        $data = base64_decode((string) $sig_hash, true);
        if($data === false || $data === '') {
            trigger_error('APP::missing signed data', EQ_REPORT_WARNING);
            return false;
        }

        // Here, for verification, we use openssl_public_decrypt as if the hash of the original data (raw binary) had been generated by the signature algorithm.
        // This is only the case for RSA PKCS#1 v1.5 signatures with SHA-256, since we pre-compute the Digest of the binary as a SHA-256 hash (data_digest).
        if($algo['cryptoAlgorithm'] === 'RSA' && $algo['paddingScheme'] === 'PKCS1.5') {

            if($algo['hashFunction'] !== 'SHA-256') {
                trigger_error('APP::unsupported hash function for RSA PKCS#1 v1.5 (only SHA-256 is supported): ' . $algo['hashFunction'], EQ_REPORT_WARNING);
                return false;
            }
            // retrieve DigestInfo (DER)
            $decrypted_digest = '';
            if(!openssl_public_decrypt($data, $decrypted_digest, $pubKey, OPENSSL_PKCS1_PADDING)) {
                // corrupted signature or not PKCS#1 v1.5
                trigger_error('APP::corrupted signature', EQ_REPORT_WARNING);
                return false;
            }
            return hash_equals(
                $decrypted_digest,
                hex2bin($algo['digestInfoPrefixHex']) . hex2bin($data_digest)
            );
        }
        elseif($algo['cryptoAlgorithm'] === 'RSA' && $algo['paddingScheme'] === 'PSS') {
            // not supported yet
            trigger_error('APP::RSA PSS signatures not supported yet', EQ_REPORT_WARNING);
        }
        elseif ($algo['cryptoAlgorithm'] === 'ECC') {
            // not supported yet
            trigger_error('APP::ECC signatures not supported yet', EQ_REPORT_WARNING);
        }
        else {
            trigger_error('APP::unsupported signature algorithm: ' . $algo['cryptoAlgorithm'], EQ_REPORT_WARNING);
        }

        return false;
    }

    protected static function calcSigAlgo($self) {
        $result = [];
        $self->read(['sig_algo_oid']);

        foreach($self as $id => $signature) {
            $algorithm = self::OID_MAP[$signature['sig_algo_oid']] ?? null;
            $result[$id] = $algorithm ? $algorithm['cryptoAlgorithm'] : null;
        }

        return $result;
    }

    protected static function calcSigHashFunc($self) {
        $result = [];
        $self->read(['sig_algo_oid']);

        foreach($self as $id => $signature) {
            $algorithm = self::OID_MAP[$signature['sig_algo_oid']] ?? null;
            $result[$id] = $algorithm ? $algorithm['hashFunction'] : null;
        }

        return $result;
    }

    protected static function calcSigPadding($self) {
        $result = [];
        $self->read(['sig_algo_oid']);

        foreach($self as $id => $signature) {
            $algorithm = self::OID_MAP[$signature['sig_algo_oid']] ?? null;
            $result[$id] = $algorithm ? $algorithm['paddingScheme'] : null;
        }

        return $result;
    }
}
