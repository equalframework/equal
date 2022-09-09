<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsagePhone extends Usage {

    public function getType(): string {
        return 'phone';
    }

    public function getSqlType(): string {
        return 'varchar(40)';
    }

    public function getConstraints(): array {
        return [
            'invalid_phone' => [
                'message'   => 'String does not comply with usage constraint.',
                'function'  =>  function($value) {
                    return (bool) (preg_match('/^((\+[1-9]{2,3})|00)?[0-9]+$/', $value));
                }
            ]
        ];
    }

    public function export($value, $lang=DEFAULT_LANG): string {
        return $value;
    }

}
