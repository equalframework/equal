<?php
namespace core {

	class Version extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'state'				=> array('type' => 'string', 'selection' => array('draft', 'version')),
				'object_class'		=> array('type' => 'string'),
				'object_id'			=> array('type' => 'integer'),
				'serialized_value'	=> array('type' => 'text'),
			);
		}
	}

}