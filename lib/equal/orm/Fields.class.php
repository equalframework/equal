<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\orm\fields\FieldBoolean;
use equal\orm\fields\FieldInteger;
use equal\orm\fields\FieldFloat;
use equal\orm\fields\FieldString;

/**
 * Factory for providing Field instances.
 *
 */
class Fields {

    public function __construct() {
    }

    public static function create(array $descriptor) {
        $type = isset($descriptor['type'])?$descriptor['type']:'unknown';
        if($type == 'computed') {
            $type = isset($descriptor['result_type'])?$descriptor['result_type']:'unknown';
        }
        switch($type) {
            case 'boolean':
                return new FieldBoolean($descriptor);
            case 'integer':
                return new FieldInteger($descriptor);
            case 'float':
                return new FieldFloat($descriptor);
            case 'string':
                return new FieldString($descriptor);
            case 'date':
            case 'time':
            case 'binary':
            case 'many2one':
            case 'one2many':
            case 'many2many':
        }
        return null;
    }

}
