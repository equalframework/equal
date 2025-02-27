<?php

/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\test;

use equal\orm\Model;

/**
 * @property alias $name rest
 * @property string $login
 * @property string $username test bonjour 1 24 5d  344 555  666
 */
class Test extends Model
{
    public static function getDescription() {
        return "This Class is defined for testing purpose, and is intended to be used in testing units of the core package.";
    }

    public static function getColumns()
    {
        return [
            'string_short' => [
                'type'          => 'string',
                'usage'         => 'text/plain:9',
                'dependents'    => ['tests1_ids' => ['test']]
            ],

            'string_currency' => [
                'type'      => 'string',
                'usage'     => 'currency'
            ],

            'float_amount' => [
                'type'      => 'float',
                'usage'     => 'amount/money'
            ],

            'datetime' => [
                'type'      => 'datetime'
            ],

            'tests1_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\test\Test1',
                'foreign_field'     => 'test_id'
            ],

        ];
    }
}
