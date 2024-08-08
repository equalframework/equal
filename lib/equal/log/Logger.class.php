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
     * Constructor defines which methods have to be called when errors and uncaught exceptions occur
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
        return ['LOGGING_ENABLED'];
    }

    /**
     * Create a new log item describing a change, and store it.
     * Each time a change to an object occurs, a new record is created.
     * Filtering the logs is based on the action field, for which any value can be used: CUD ('create', 'update', 'delete' - which is always performed by the system);
     * an action defined at a Class level; or any custom action defined in a specific controller.
     *
     * @param integer $user_id
     * @param string  $action
     * @param string  $object_class
     * @param integer $object_id
     * @param array   $fields       Associative array mapping fields with values representing the partial state of the object being modified (fields impacted ny the action).
     */
    public function log($user_id, $action, $object_class, $object_id, $fields=null) {
        // ignore call if logging is disabled
        if(!constant('LOGGING_ENABLED')) {
            return;
        }

        // prevent infinite loops
        if($object_class == 'core\Log') {
            return;
        }

        // when using CLI, actions are performed using QN_ROOT_USER_ID, unless otherwise specified
        if($user_id == 0 && php_sapi_name() === 'cli') {
            $user_id = QN_ROOT_USER_ID;
        }

        /*
        // #todo - this feature is disabled and should be replaced with a link (m2o) to a Change object
        //     holding the optional payload of the event

        // #memo - with time, core_log table grows big and should only contain essential (meta) data

        $json = json_encode($fields);
        // discard faulty JSON
        if($json === false) {
            $json = '{"ignored": "JSON conversion failed"}';
        }
        // max size for log entry text is 32KiB
        elseif(strlen($json) > 32000) {
            // drop payload
            $json = '{"ignored": "resulting JSON too large"}';
        }
        */

        $values = [
            'action'        => $action,
            'object_class'  => $object_class,
            'object_id'     => $object_id,
            'user_id'       => $user_id,
            // 'value'         => $json
        ];

        // logs are system objects (no permissions must be applied)
        $this->orm->create('core\Log', $values, null, false);
    }

}