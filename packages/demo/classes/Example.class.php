<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace test;
use equal\orm\Model;

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
                'onupdate'      => 'test\Example::onupdateTotalFloat'
            ],

            'float_3'			=> ['type' => 'float', 'onupdate' => 'test\Example::onupdateTotalFloat'],

            'total_float' => [
                'type'          => 'computed',
                'result_type'   => 'float',
                'function'      => 'test\Example::calcTotalFloat',
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
                'function'      => 'test\Example::calcInteger3'
            ]

        ];
    }

    public static function calcTotalFloat($om, $oid, $lang) {
        $total = 0.0;
        $float_fields = array('float_1', 'float_2', 'float_3');
        $res = $om->read('test\Example', array($oid), $float_fields, $lang);
        foreach($float_fields as $float_field) $total += $res[$oid][$float_field];
        return $total;
    }

    public static function onupdateTotalFloat($om, $oid, $values, $lang) {
        $om->update('test\Example', array($oid), [ 'total_float' => Example::calcTotalFloat($om, $oid, $lang) ], $lang);
    }


    public static function calcInteger3($om, $oid, $lang) {
        return 12345;
    }

}