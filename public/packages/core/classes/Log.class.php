<?php
namespace core {

	class Log extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'action'			=> array('type' => 'string', 'selection' => array('CREATE', 'READ', 'WRITE', 'DELETE')),
				'object_class'		=> array('type' => 'string'),
				'object_id'			=> array('type' => 'integer'),
				'object_fields'		=> array('type' => 'text'),
			);
		}
	}
}