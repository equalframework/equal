<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsagePassword extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        // force allowing enough bytes for `password_hash()` result (depends on `PASSWORD_DEFAULT`)
        $this->length = 255;
        if($this->min == 0) {
            $this->min = 8;
        }
        if($this->max > 70) {
            $this->max = 70;
        }
    }

    public function getConstraints(): array {
        switch($this->getSubtype(0)) {
            case 'enisa':
            case 'nist':
                return [
                    'invalid_password' => [
                        'message'   => 'Password does not pass some NIST rules.',
                        'function'  =>  function($value) {
                            // NIST compliant password: min. 8 chars, 1 numeric digit, 1 uppercase, 1 lowercase, 1 special char (amongst #?!@$%^*-).'
                            // supported special chars are : #?!@$%^*- (compatible with Oracle Identity Manager and Microsoft Active Directory)
                            return (bool) (preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^*-]).{8,}$/', $value));
                        }
                    ]
                ];
            default:
                return [
                    'invalid_password' => [
                        'message'   => 'Password too short.',
                        'function'  =>  function($value) {
                            return (strlen($value) >= $this->getMin());
                        }
                    ]
                ];
        }
    }

    public function generateRandomValue(): string {
        $password = '';
        $dict = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $len = strlen($dict) - 1;
        for ($i = 0; $i < 10; $i++) {
            $password .= $dict[mt_rand(0, $len)];
        }

        return $password;
    }

}
