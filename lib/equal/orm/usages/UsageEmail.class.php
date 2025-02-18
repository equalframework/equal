<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


use equal\data\DataGenerator;

class UsageEmail extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            $this->length = 255;
        }
    }

    /**
     *
     *        supports:
     *            c@a.com
     *            c@a.b.com
     *            c@a.b.c.com
     *            a+c@a.b.c.com
     *            a.c+d@a.b.c.com
     *            ad@a.b.c.xn--vermgensberatung-pwb
     *        does not support:
     *            a+c.d@a.b.c.com
     */
    public function getConstraints(): array {
        return [
            'invalid_email' => [
                'message'   => 'Malformed email address.',
                'function'  =>  function($value) {
                    return (bool) (preg_match('/^([_a-z0-9-\.]+)(\+([_a-z0-9]+))?@(([a-z0-9-]+\.)*)([a-z0-9-]{1,63})(\.[a-z-]{2,24})$/i', $value));
                }
            ]
        ];
    }

    public function generateRandomValue(): string {
        return DataGenerator::email();
    }

}
