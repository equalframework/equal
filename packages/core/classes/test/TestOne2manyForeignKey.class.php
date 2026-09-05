<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\test;

class TestOne2manyForeignKey extends Test
{
    public static function getColumns() {
        return [
            'tests1_by_test_id_ids' => [
                'type'           => 'one2many',
                'foreign_object' => 'core\test\Test1',
                'foreign_field'  => 'test_id',
                'foreign_key'    => 'test_id'
            ]
        ];
    }
}
