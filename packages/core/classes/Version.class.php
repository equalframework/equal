<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Version extends Model {

    public static function getColumns() {
        return array(
            'state'				=> array('type' => 'string', 'selection' => array('draft', 'version')),
            'object_class'		=> array('type' => 'string'),
            'object_id'			=> array('type' => 'integer'),
            'serialized_value'	=> array('type' => 'text'),
        );
    }
}