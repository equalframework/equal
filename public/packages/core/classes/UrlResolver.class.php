<?php
namespace core {

	class UrlResolver extends \easyobject\orm\Object {

		public static function getColumns() {
			return array(
				'human_readable_url'	=> array('type' => 'string'),
				'complete_url'			=> array('type' => 'string'),
			);
		}
        
        public static function getUnique() {
            return array(
                ['human_readable_url']
            );
        }
        
	}
    
}