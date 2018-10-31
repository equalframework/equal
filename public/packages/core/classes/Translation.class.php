<?php
namespace core {

	class Translation extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'language'			=> array('type' => 'string'),
				'object_class'		=> array('type' => 'string'),
				'object_field'		=> array('type' => 'string'),
				'object_id'			=> array('type' => 'integer'),
				'value'				=> array('type' => 'binary'),
			);
		}
	}

}