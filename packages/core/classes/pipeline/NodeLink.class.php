<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\pipeline;

use equal\orm\Model;

class NodeLink extends Model
{

    public static function getColumns()
    {
        return [
            'reference_node_id' => [
                'type'              => 'integer',
                'required'          => true
            ],

            'source_node_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\pipeline\Node',
                'required'          => true
            ],

            'target_node_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\pipeline\Node',
                'required'          => true
            ],

            'target_param' => [
                'type'              => 'string'
            ]
        ];
    }
}
