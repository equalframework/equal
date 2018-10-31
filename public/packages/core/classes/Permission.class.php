<?php
namespace core {

	class Permission extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'class_name'		=> array('type' => 'string'),
				'group_id'			=> array('type' => 'many2one', 'foreign_object' => 'core\Group'),
				'user_id'			=> array('type' => 'many2one', 'foreign_object' => 'core\User'),                
				'rights'			=> array('type' 	=> 'integer',
					                         'onchange' => 'core\Permission::onchange_rights'),
				// virtual fields, used in list views
				'rights_txt'		=> array('type' => 'function', 'store' => true, 'result_type' => 'string', 'function' => 'core\Permission::getRightsTxt'),
			);
		}

		public static function onchange_rights($om, $uid, $ids, $lang) {
			// note : we are in the core namespace, so we don't need to specify it when referring to this class
			$rights = Permission::getRightsTxt($om, $uid, $ids, $lang);
			foreach($ids as $oid) $om->write($uid, 'core\Permission', $oid, array('rights_txt' => $rights[$oid]), $lang);
		}

		public static function getRightsTxt($om, $uid, $ids, $lang) {
			$res = array();
			$values = $om->read($uid, 'core\Permission', $ids, array('rights'), $lang);
			foreach($ids as $oid) {
				$rights_txt = array();
				$rights = $$values[$oid]['rights'];
				if($rights & R_CREATE)	$rights_txt[] = 'create';
				if($rights & R_READ)	$rights_txt[] = 'read';
				if($rights & R_WRITE)	$rights_txt[] = 'write';
				if($rights & R_DELETE)	$rights_txt[] = 'delete';
				if($rights & R_MANAGE)	$rights_txt[] = 'manage';
				$res[$oid] = implode(', ', $rights_txt);
			}
			return $res;
		}

	}
}