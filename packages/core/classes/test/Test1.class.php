<?php

/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\test;

use equal\orm\Model;

class Test1 extends Model
{
    public static function getColumns()
    {
        return [
            'test' => [
                'type'          => 'computed',
                'result_type'   => 'string',
                'function'      => 'calcTest',
                'store'         => true
            ],

            'test_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\test\Test'
            ],
        ];
    }

    public static function calcTest($self) {
        $result = [];
        $self->read(['test_id' => ['id', 'string_short']]);
        foreach($self as $id => $test1) {
            trigger_error("ORM::".'rel:'.$test1['test_id']['id'], QN_REPORT_WARNING);
            trigger_error("ORM::".'val:'.$test1['test_id']['string_short'], QN_REPORT_WARNING);
            $result[$id] = $test1['test_id']['string_short'];
        }
        return $result;
    }
}
