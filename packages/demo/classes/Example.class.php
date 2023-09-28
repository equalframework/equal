<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace demo;
use equal\orm\Model;


/*
    CLI commands samples:

    ./equal.run --do=init_package --package=demo
    ./equal.run --do=model_create --entity=demo\\Example
    ./equal.run --do=model_create --entity=demo\\Example --fields[float_1]=11 --fields[date_2]="2022-08-09"
    ./equal.run --do=model_create --entity=demo\\Example --fields[float_1]=5.5 --fields[date_2]="2022-08-09"
*/


class Example extends Model {

    public static function getColumns() {
        return [
            'string' => [
                'type'          => 'string',
                'multilang'     => true
            ],

            'short_text' =>[
                'type'          => 'string',
                'usage'         => 'text/plain'
            ],

            'date_1' => [
                'type'          => 'date',
                'default'       => time()
            ],

            'date_2' => [
                'type'          => 'date',
                'required'      => true
            ],

            'date_3' => [
                'type'          => 'date'
            ],

            'float_1' => [
                'type'          => 'float',
                'onupdate'      => 'onupdateTotalFloat'
            ],

            'float_2' => [
                'type'          => 'float',
                'onupdate'      => 'demo\Example::onupdateTotalFloat'
            ],

            'float_3' => [
                'type'          => 'float',
                'onupdate'      => 'demo\Example::onupdateTotalFloat'
            ],

            'total_float' => [
                'type'          => 'computed',
                'result_type'   => 'float',
                'function'      => 'demo\Example::calcTotalFloat',
                'store'         => true
            ],

            'integer_1' => [
                'type'          => 'integer',
                'default'       => '54321'
            ],

            'integer_2'			=> ['type' => 'integer'],

            'integer_3' => [
                'type'          => 'function',
                'result_type'   => 'integer',
                'store'         => true,
                'function'      => 'demo\Example::calcInteger3'
            ]

        ];
    }

    public static function calcTotalFloat($om, $oid, $lang) {
        $total = 0.0;
        $float_fields = array('float_1', 'float_2', 'float_3');
        $res = $om->read('demo\Example', array($oid), $float_fields, $lang);
        foreach($float_fields as $float_field) $total += $res[$oid][$float_field];
        return $total;
    }

    public static function onupdateTotalFloat($om, $oid, $values, $lang) {
        $om->update('demo\Example', array($oid), [ 'total_float' => Example::calcTotalFloat($om, $oid, $lang) ], $lang);
    }

    public static function calcInteger3($om, $oid, $lang) {
        return 12345;
    }

    public static function getConstraints() {
        return [
            'float_1' =>  [
                'too_high' => [
                    'message'       => 'Value must be less than 10.',
                    'function'      => function ($float_1, $values) {
                        return ($float_1 < 10);
                    }
                ],
                'too_low' => [
                    'message'       => 'Value must be higher than 5.',
                    'function'      => function ($float_1, $values) {
                        return ($float_1 > 5);
                    }
                ]
            ]
        ];
    }

}