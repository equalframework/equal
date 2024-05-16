<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\pipeline;

use equal\orm\Model;

class Node extends Model
{

    public static function getColumns()
    {
        return [
            'name' => [
                'type'          => 'string',
                'required'      => true
            ],

            'description' => [
                'type'          => 'string'
            ],

            'pipeline_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\pipeline\Pipeline',
                'required'          => true
            ],

            'out_links_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\pipeline\NodeLink',
                'foreign_field'     => 'source_node_id'
            ],

            'in_links_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\pipeline\NodeLink',
                'foreign_field'     => 'target_node_id'
            ],

            'operation_controller' => [
                'type'             => 'string'
            ],

            'operation_type' => [
                'type'              => 'string'
            ],

            'params_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\pipeline\Parameter',
                'foreign_field'     => 'node_id'
            ]
        ];
    }

    public function getUnique()
    {
        return [
            ['name', 'pipeline_id']
        ];
    }
}
