<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageUri extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            $this->length = 1024;
        }
    }

    public function getConstraints(): array {
        switch($this->getSubtype()) {
            case 'url.relative':
                /*
                    /a
                    /a/b
                    /a/b/c
                */
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid relative URL.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^(\/([^\/])+)+$/', $value));
                        }
                    ]
                ];
            case 'url':
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
                    https://username:password@example.com:443
                */
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid URL.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^((([a-zA-Z][a-zA-Z0-9+.-]*):)?\/\/)?(([a-zA-Z0-9.-]+)(:[a-zA-Z0-9.-]+)?@)?([a-zA-Z0-9.-]+|\[[0-9:.]+\])(:[0-9]{1,5})?(\/[a-zA-Z0-9\/%._=,]*)?(\?[a-zA-Z0-9&=%._-]*)?(#[a-zA-Z0-9,%-]*)*$/', $value));
                        }
                    ]
                ];
            case 'url.tel':
                return [
                    'invalid_url' => [
                        'message'   => 'String is not a valid tel URL.',
                        'function'  =>  function($value) {
                            return (bool) (preg_match('/^tel:((\+[1-9]{2,3})|00)?[0-9]+$/', $value));
                        }
                    ]
                ];
            case 'url.mailto':
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
        return [];
    }

}
