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
        return array(
            'name'				=> array('type' => 'string'),
            'users_ids'			=> array('type' => 'many2many', 'foreign_object' => 'core\User', 'foreign_field' => 'groups_ids', 'rel_table' => 'core_rel_group_user', 'rel_foreign_key' => 'user_id', 'rel_local_key' => 'group_id'),
            'permissions_ids'	=> array('type' => 'one2many', 'foreign_object' => 'core\Permission', 'foreign_field' => 'group_id')
        );
    }

}