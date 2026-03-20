<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\pipeline;

use equal\orm\Model;

class Pipeline extends Model
{

    public static function getColumns()
    {
        return [
            'nodes_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\pipeline\Node',
                'foreign_field'     => 'pipeline_id'
            ],

            'name' => [
                'type'              => 'string',
                'required'          => true,
                'unique'            => true
            ],
        ];
    }
}
