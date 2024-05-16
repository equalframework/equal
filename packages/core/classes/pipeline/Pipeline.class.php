<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
