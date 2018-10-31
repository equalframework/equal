<?php
namespace core;

use qinoa\orm\Model;

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