<?php
namespace core {

	class Group extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'name'				=> array('type' => 'string'),
				'users_ids'			=> array('type' => 'many2many', 'foreign_object' => 'core\User', 'foreign_field' => 'groups_ids', 'rel_table' => 'core_rel_group_user', 'rel_foreign_key' => 'user_id', 'rel_local_key' => 'group_id'),
				'permissions_ids'	=> array('type' => 'one2many', 'foreign_object' => 'core\Permission', 'foreign_field' => 'group_id')
			);
		}

	}
}