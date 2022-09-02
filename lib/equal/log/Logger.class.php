<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\log;

use equal\organic\Service;
use equal\orm\ObjectManager;


class Logger extends Service {

    // Object Manager instance
    private $orm;

    /**
     * Contructor defines which methods have to be called when errors and uncaught exceptions occur
     *
     */
	public function __construct(ObjectManager $orm) {
        $this->orm = $orm;
	}

    /**
     * Static list of constants required by current provider
     *
     */
    public static function constants() {
        return ['LOGGING_ENABLED', 'ROOT_USER_ID'];
    }

	/**
	 * Adds a log to database.
	 * Everytime a change to an object occurs, a new record is created.
     * Filtering the logs is based on the action field, for which any value can be used: CUD ('create', 'update', 'delete' - which is always performed by the system) or action related to a specific controller.
	 *
	 * @param integer $user_id
	 * @param string  $action
	 * @param string  $object_class
	 * @param integer $object_id
	 */
	public function log($user_id, $action, $object_class, $object_id) {
		if(!LOGGING_ENABLED) return;

		// prevent infintite loops
		if($object_class == 'core\Log') return;

        // when using CLI actions are performed using ROOT_USER_ID, unless otherwise specified
        if($user_id == 0 && php_sapi_name() === 'cli') {
            $user_id = ROOT_USER_ID;
        }

		$values = [
            'action'        => $action,
            'object_class'  => $object_class,
            'object_id'     => $object_id,
            'user_id'       => $user_id
        ];

		// logs are system objects (no permissions must be applied)
		$this->orm->create('core\Log', $values, DEFAULT_LANG, false);
	}

}