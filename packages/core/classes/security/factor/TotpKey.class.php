<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security\factor;

use core\security\AuthenticationFactor;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;

class TotpKey extends AuthenticationFactor {

    public static function getDescription(): string {
        return 'A user Totp key that allows MFA authentication.';
    }

    public static function getColumns(): array {
        return [

            'type' => [
                'type'              => 'string',
                'description'       => 'Type of authentication factor.',
                'help'              => 'Technical mechanism used by the factor: blocked on `totp` according to current class.',
                'readonly'          => true,
                'default'           => 'totp'
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'pending',
                    'active',
                    'disabled',
                    'revoked'
                ],
                'description'       => 'Current status of the authentication factor.',
                'help'              => 'Controls whether the factor can currently be used for authentication.',
                'default'           => 'pending' // #memo - Default status is 'pending' because the user needs to use the generated QR code to activate it
            ],

            'secret' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => 'The totp key secret that will also be store on the user\'s authenticator application.',
                'readonly'          => true,
                'store'             => true,
                'function'          => 'calcSecret'
            ],

            'algorithm' => [
                'type'              => 'string',
                'description'       => 'The algorithm to use to create one time passwords.',
                'default'           => 'SHA1',
                'selection'         => [
                    'SHA1',
                    'SHA256',
                    'SHA512'
                ]
            ],

            'digits' => [
                'type'              => 'integer',
                'description'       => 'The number of digits that will compose generated one time passwords.',
                'help'              => 'Only six and eight are available.',
                'default'           => 6,
                'min'               => 6,
                'max'               => 8
            ],

            'period' => [
                'type'              => 'integer',
                'description'       => 'The period of time, in seconds, that will be used to generate one time passwords.',
                'default'           => 30
            ],

            'failed_attempts' => [
                'type'              => 'integer',
                'description'       => 'The quantity of failed authentication attempts since last time valid credentials were given by user',
                'default'           => 0
            ],

            'totp_uri' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'usage'             => 'url',
                'description'       => 'The uri that define the creation of the totp for the user\'s authenticator application.',
                'store'             => false,
                'function'          => 'calcTotpUri'
            ],

            'totp_qr_code_uri' => [
                'type'              => 'computed',
                'result_type'       => 'test',
                'description'       => 'The qr code image uri for the user to scan and add the totp to its authenticator application.',
                'store'             => false,
                'function'          => 'calcTotpQrCodeUri'
            ]

        ];
    }

    protected static function calcSecret($self): array {
        $result = [];

        $base32Encode = function (string $data): string {
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
            $bits = '';

            foreach (unpack('C*', $data) as $byte) {
                $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
            }

            $bits = str_pad($bits, (int)ceil(strlen($bits) / 5) * 5, '0');

            $encoded = '';
            foreach (str_split($bits, 5) as $chunk) {
                $encoded .= $alphabet[bindec($chunk)];
            }

            return $encoded;
        };

        foreach ($self as $id => $totp_key) {
            do {
                $secret = $base32Encode(random_bytes(20));

                $existingTotpKey = TotpKey::search([
                    ['type', '=', 'totp'],
                    ['secret', '=', $secret]
                ])
                    ->first();
            } while ($existingTotpKey);

            $result[$id] = $secret;
        }

        return $result;
    }

    protected static function calcTotpUri($self): array {
        $issuer = parse_url(constant('BACKEND_URL'), PHP_URL_HOST);

        $result = [];
        $self->read(['status', 'secret', 'user_id' => ['login']]);
        foreach($self as $id => $totp_key) {
            if($totp_key['status'] !== 'pending') {
                // #memo - the uri should only be available before activation
                continue;
            }

            $label = rawurlencode($issuer) . ':' . rawurlencode($totp_key['user_id']['login']);

            $http_params = [
                'secret'    => $totp_key['secret'],
                'issuer'    => $issuer,
                'algorithm' => 'SHA1',
                'digits'    => 6,
                'period'    => 30
            ];
            $query = http_build_query($http_params, '', '&', PHP_QUERY_RFC3986);

            $result[$id] = "otpauth://totp/{$label}?{$query}";
        }

        return $result;
    }

    protected static function calcTotpQrCodeUri($self): array {
        $result = [];
        $self->read(['status', 'totp_uri']);
        foreach($self as $id => $totp_key) {
            if($totp_key['status'] !== 'pending') {
                // #memo - the qr code should only be available before activation
                continue;
            }

            $builder = Builder::create()
                ->data($totp_key['totp_uri'])
                ->errorCorrectionLevel(new ErrorCorrectionLevelMedium())
                ->build();

            $result[$id] = $builder->getDataUri();
        }

        return $result;
    }

    protected static function policyActivatable($self): array {
        $result = [];
        $self->read(['user_id']);
        foreach($self as $id => $totpkey) {
            $user_has_active_totpkey = TotpKey::search([
                ['id', '<>', $id],
                ['type', '=', 'totp'],
                ['status', '=', 'active'],
                ['user_id', '=', $totpkey['user_id']]
            ])
                ->count() > 0;

            if($user_has_active_totpkey) {
                return ['user_id' => ['has_active' => "The object class must be configured to reverse the export."]];
            }
        }

        return $result;
    }
}
