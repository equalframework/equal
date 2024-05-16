<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\pipeline;

use equal\orm\Model;

class PipelineExecution extends Model
{

    public static function getColumns()
    {
        return [

            'pipeline_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\pipeline\Pipeline',
                'required'          => true
            ],

            'status' => [
                'type'             => 'string'
            ]
        ];
    }
}
