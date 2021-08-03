<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Group extends Model {

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string'
            ],
            'display_name' => [
                'type'              => 'string',
                'multilang'         => true
            ],
            'description' => [
                'type'              => 'string',
                'description'       => 'Short presentation of the role assigned to members of the group.',
                'multilang'         => true
            ],
            'users_ids' => [                
                'type'              => 'many2many',
                'foreign_object'    => 'core\User', 
                'foreign_field'     => 'groups_ids', 
                'rel_table'         => 'core_rel_group_user', 
                'rel_foreign_key'   => 'user_id', 
                'rel_local_key'     => 'group_id',
                'description'       => 'List of users that are members of the group.'
            ],
            'permissions_ids' => [
                'type'              => 'one2many', 
                'foreign_object'    => 'core\Permission', 
                'foreign_field'     => 'group_id'
            ]
        ];
    }

}