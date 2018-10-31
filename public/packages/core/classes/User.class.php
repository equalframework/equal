<?php
namespace core {

	class User extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'login'			=> array('type' => 'string'),
				'password'		=> array('type' => 'string'),
				'firstname'		=> array('type' => 'string'),
				'lastname'		=> array('type' => 'string'),
				'start'			=> array('type' => 'string'),				
				'birthdate'		=> array('type' => 'date'),
				'language'		=> array('type' => 'string'),
				'validated'		=> array('type' => 'boolean'),
				'groups_ids'	=> array('type' => 'many2many', 'foreign_object' => 'core\Group', 'foreign_field' => 'users_ids', 'rel_table' => 'core_rel_group_user', 'rel_foreign_key' => 'group_id', 'rel_local_key' => 'user_id'),
			);
		}

		public static function getConstraints() {
			return [
				'login'			=> array(
									'error_message_id' => 'invalid_login',
									'function' => function ($login) {
											return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/', $login, $matches));
										}
									),
				'password'		=> array(
									'error_message_id' => 'invalid_password',
									'function' => function ($password) {
											return (bool) (preg_match('/^[0-9|a-z]{32}$/', $password, $matches));
										}
									),
			];
		}
	}
}