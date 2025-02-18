<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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
