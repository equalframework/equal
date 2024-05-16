<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\pipeline;

use equal\orm\Model;

class Parameter extends Model
{

    public static function getColumns()
    {
        return [
            'node_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\pipeline\Node'
            ],

            'value' => [
                'type'              => 'string',
                'required'          => true
            ],

            'param' => [
                'type'              => 'string',
                'required'          => true
            ]
        ];
    }
}
