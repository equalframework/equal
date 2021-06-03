<?php
namespace core;

use equal\orm\Model;

class Translation extends Model {

    public static function getColumns() {
        return array(
            'language'			=> array('type' => 'string'),
            'object_class'		=> array('type' => 'string'),
            'object_field'		=> array('type' => 'string'),
            'object_id'			=> array('type' => 'integer'),
            'value'				=> array('type' => 'binary'),
        );
    }
}