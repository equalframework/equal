    <?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageUri extends Usage {

    public function getConstraints(): array {
        switch($this->getSubtype()) {
            /*
                https://www.goo-gle.com:80/path/sub/?test&a=b#fragment
                //www.google.com.ua
                https://mail.google.com
                http://mail.google.com
                ldap://localhost:389/ou=people,o=myOrganization
                https://a.b.com:80/path/sub/?query=test&a=b#fragment
                ftp://ftp.mozilla.org/pub/mozilla.org/firefox/releases/
                dns://192.168.1.1/ftp.example.org?type=A
                file://localhost/path
                ircs://irc.example.com:6697/#channel1,#channel2
            */
            case 'url':
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid URL.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^([a-z]*:)?(\/\/)([a-zA-Z0-9-]+)*(\.[a-zA-Z0-9-]*)*(:[0-9]{1,5})?(\/.*)?$/', $value));
                        }
                    ]
                ];
            case 'uri/url.tel':
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid tel URL.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^tel:((\+[1-9]{2,3})|00)?[0-9]+$/', $value));
                        }
                    ]
                ];
            case 'uri/url.mailto':
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid mailto URL.',
                        'function'  =>  function($value) {
                            (bool) (preg_match('/^mailto:([_a-z0-9-]+)(\.[_a-z0-9+-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $value));
                        }
                    ]
                ];
            case 'urn.iban':
                return [
                    'invalid_iban' => [
                        'message'   => 'Bank account must be a valid IBAN number.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^[A-Z]{2}[0-9]{2}(?:[0-9]{4}){3,4}(?!(?:[0-9]){3})(?:[0-9]{1,2})?$/', $value));
                        }
                    ]
                ];
            case 'urn.ean':
                return [
                    'invalid_ean' => [
                        'message'   => 'String is not a valid EAN-13.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^[0-9]{13}$/', $value));
                        }
                    ]
                ];
        }
    }

    public function export($value, $lang='en'): string {
        return $value;
    }

}
