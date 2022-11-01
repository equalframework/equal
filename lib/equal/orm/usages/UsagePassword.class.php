    <?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsagePassword extends Usage {

    public function getType(): string {
        return 'password';
    }

    public function getSqlType(): string {
            return 'varchar(255)';
    }

    public function getConstraints(): array {
        switch($this->getSubtype()) {
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
                            $min_len = ($this->getLength() > 0)?$this->getLength():8;
                            return strlen($value) >= $min_len;
                        }
                    ]
                ];
        }
    }

    public function export($value, $lang='en'): string {
        return $value;
    }

}
