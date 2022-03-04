<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;



class Collection implements \Iterator {

    /* ObjectManager */
    private $orm;

    /* AccessController */
    private $ac;

    /* AuthenticationManager */
    private $am;

    /* DataAdapter */
    private $adapter;

    /* Logger */
    private $logger;


    /* target class */
    private $class;

    /* static instance of target class (for retrieving fields and schema) */
    private $instance;

    /* map associating objects with their values with identifiers as keys */
    private $objects;

    /* pagination limit: size of a page when searching or loading sub-objects (default is 0, i.e. no limit) */
    private $limit;

    /**
     *
     * This method makes valid 'is_callable' tests on any static method name (i.e. 'entry' calls, e.g. ::search, ::ids, ::id, ...)
     */
    public static function __callStatic($name, $arguments) {}

    /**
     * Collection constructor.
     * This is called through the Collections service, which retrieves the dependencies through its container.
     * Collections service is a factory that creates Collection instances when requested
     * (i.e. when a magic method is called on a class that derives from namespace `equal\orm\Model`).
     */
    public function __construct($class, $objectManager, $accessController, $authenticationManager, $dataAdapter, $logger) {
        // init objects map
        $this->objects = [];
        $this->cursor = 0;
        // assign private members
        $this->class = $class;
        $this->orm = $objectManager;
        $this->ac = $accessController;
        $this->am = $authenticationManager;
        $this->adapter = $dataAdapter;
        $this->logger = $logger;
        // check mandatory services
        if(!$this->orm || !$this->ac) {
            throw new \Exception(__CLASS__, QN_ERROR_UNKNOWN);
        }
        // retrieve static instance for target class
        $this->instance = $this->orm->getStatic($class);
        if($this->instance === false) {
            throw new \Exception($class, QN_ERROR_UNKNOWN_OBJECT);
        }
        // default to unlimited page size
        $this->limit(0);
    }

    public function extend($column, $description) {
        $this->instance->setColumn($column, $description);
    }

    public function set($fields) {
        $fields = (array) $fields;
        foreach($this->objects as $id => $object) {
            foreach($fields as $field => $value) {
                $this->objects[$id][$field] = $value;
            }
        }
        return $this;
    }

    public function rewind() : void {
        reset($this->objects);
    }

    public function current() {
        return current($this->objects);
    }

    public function key() : mixed {
        return key($this->objects);
    }

    public function next() : void {
        next($this->objects);
    }

    public function valid() : bool {
        $key = key($this->objects);
        $res = ($key !== NULL && $key !== FALSE);
        return $res;
    }

    /*
    public function from($offset) {
        if($offset < count($this->objects)) {
            $this->objects = array_slice($this->objects, $offset, null, true);
        }
        else {
            $this->objects = [];
        }
        return $this;
    }
    */

    /**
     *  Pop out the n last objects of the collection (and set internal limit value)
     *
     */
    public function limit() {
        $args = func_get_args();
        if(count($args) < 1) {
            return $this->limit;
        }
        else if($args[0] > 0) {
            $this->limit = $args[0];
            if($this->limit && $this->limit < count($this->objects)) {
                $this->objects = array_slice($this->objects, 0, $this->limit, true);
            }
        }
        return $this;
    }

    /**
     *  Shift out the n first objects of the collection
     *
     */
    public function shift($offset=1) {
        if($offset) {
            if($offset < count($this->objects)) {
                $this->objects = array_slice($this->objects, $offset, null, true);
            }
            else {
                $this->objects = [];
            }
        }
        return $this;
    }

    /**
     * Return the first object present in the collection.
     * (This method does not alter the collection.)
     *
     * @param   $to_array   boolean    Flag to ask conversion to an array (instead of a map)
     * @return array|null  If current queue is not empty, returns the first object, otherwise returns null.
     */
    public function first($to_array=false) {
        if(!count($this->objects)) {
            return null;
        }
        $objects = array_slice($this->objects, 0, 1);
        $object = array_pop( $objects );
        return $this->get_raw_object($object, $to_array);
    }

    /**
     * Return the last object present in the collection.
     * (This method does not alter the collection.)
     *
     * @param   $to_array   boolean    Flag to ask conversion to an array (instead of a map)
     * @return array|null  If current queue is not empty, returns the last object, otherwise returns null.
     */
    public function last($to_array=false) {
        if(!count($this->objects)) {
            return null;
        }
        $objects = array_slice($this->objects, -1, 1);
        $object = array_pop( $objects );
        return $this->get_raw_object($object, $to_array);
    }

    /**
     * Provide the whole collection as a map (by default) or as an array
     *
     * @param   $to_array   boolean    Flag to ask conversion to an array (instead of a map)
     * @return  array       (associative) array holding objects converted to arrays (no Collection instances)
     */
    public function get($to_array=false) {
        $result = [];
        foreach($this->objects as $id => $object) {
            $result[$id] = $this->get_raw_object($object, $to_array);
        }
        if($to_array) {
            $result = array_values($result);
        }
        return $result;
    }

    /**
     * Recursively generate a copy of an object from current collection,
     * by replacing Collection instances with either a map or an array.
     *
     * @param   $to_array   boolean    Flag to ask conversion of sub-objects to arrays (instead of a maps)
     */
    private function get_raw_object(&$object, $to_array=false) {
        $result = [];
        $schema = $this->instance->getSchema();
        foreach($object as $field => $value) {
            if($value instanceof Collection) {
                $list = $value->get($to_array);
                if($schema[$field]['type'] == 'many2one' && count($list)) {
                    $result[$field] = current($list);
                }
                else {
                    $result[$field] = $list;
                }
            }
            else {
                $result[$field] = $value;
            }
        }
        return $result;
    }

    public function toArray() {
        return $this->get(true);
    }

    /**
     * create an empty object based on a given id
     */
    public function id($id) {
        return $this->ids((array) $id);
    }

    /**
     * @param array optional if given, sets current objects array, if not returns current ids
     *
     */
    public function ids() {
        $args = func_get_args();
        if(count($args) < 1) {
            return array_keys($this->objects);
        }
        else {
            $ids = array_unique((array) $args[0]);
            // #memo - filling the list with non-readable objects would raise a NOT_ALLOWED exception
            /*
            // #removed - filter resulting ids based on current user permissions
            foreach($ids as $i => $id) {
                if(!$this->ac->isAllowed(QN_R_READ, $this->class, [], $id)) {
                    unset($ids[$i]);
                }
            }
            */
            // init keys of 'objects' member (resulting in a map with keys but no values)
            $this->objects = array_fill_keys($ids, []);
        }
        return $this;
    }

    /**
     * Filters a map of fields-values entries or an array of fields names and disguard those unknonwn to the current class.
     *
     * @param $fields   array   a set of fields or a map of fields-values
     * @return array    filtered array containing known fields names only
     */
    private function filter(array $fields) {
        $result = [];
        if(count($fields)) {
            // retreve valid fields, i.e. fields from schema excepted special fields (#memo - `state` is allowed for draft creation)
            $allowed_fields = array_diff($this->instance->getFields(), ['id','creator','created','modifier','modified','deleted']);
            // filter $fields argument based on its structure
            // (either a list of fields to read, or a map of fields and their values for writing)
            if(!is_numeric(key($fields))) {
                $result = array_intersect_key($fields, array_flip($allowed_fields));
            }
            else {
                $result = array_intersect($fields, $allowed_fields);
            }
        }
        return $result;
    }

    /**
     * Convert values to specified context (txt, php, sql, orm) based on their type
     *
     * @param $to       string  might be a map associating fields with their values, or a map association ids with objects
     *
     * @return object   current instance
     */
    public function adapt($to='txt') {
        $schema = $this->instance->getSchema();
        foreach($this->objects as $id => $object) {
            foreach($object as $field => $value) {
                if($value instanceof Collection) {
                    $value->adapt($to);
                }
                else {
                    $type = $schema[$field]['type'];
                    if($type == 'computed' && isset($schema[$field]['result_type'])) {
                        $type = $schema[$field]['result_type'];
                    }
                    $this->objects[$id][$field] = $this->adapter->adapt($value, $type, $to, 'php');
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param $fields   array   associative array holding values and their related fields as keys
     * @throws  Exception   if some value could not be validated against class contraints (see {class}::getConstraints method)
     */
    private function validate(array $fields, $ids=[], $check_unique=false, $check_required=false) {
        $validation = $this->orm->validate($this->class, $ids, $fields, $check_unique, $check_required);
        if($validation < 0 || count($validation)) {
            foreach($validation as $error_code => $error_descr) {
                // this exception results in sending error in the same format as the announce method
                throw new \Exception(serialize($error_descr), $error_code);
            }
        }
    }

    public function grant($users_ids, $operation, $fields=[]) {
        // retrieve targeted identifiers
        $ids = array_keys($this->objects);

        // 2) check that current user has enough privilege to perform READ operation
        if(!$this->ac->isAllowed(QN_R_MANAGE, $this->class, $fields, $ids)) {
            throw new \Exception('MANAGE,'.$this->class, QN_ERROR_NOT_ALLOWED);
        }
        $this->ac->grantUsers($users_ids, $operation, $this->class, $fields, $ids);
        return $this;
    }

    public function search(array $domain=[], array $params=[], $lang=DEFAULT_LANG) {
        $defaults = [
            'sort'  => ['id' => 'asc']
        ];

        // retrieve current user id
        $user_id = $this->am->userId();

        if(isset($params['sort'])) $params['sort'] = (array) $params['sort'];
        $params = array_merge($defaults, $params);

        // 1) sanitize and validate given domain
        if(!empty($domain)) {
            $domain = Domain::normalize($domain);
            $schema = $this->instance->getSchema();
            if(!Domain::validate($domain, $schema)) {
                throw new \Exception(Domain::toString($domain), QN_ERROR_INVALID_PARAM);
            }
        }

        // 2) check that current user has enough privilege to perform READ operation on given class
        // #todo : extract fields names from domain, and make sure user has R_READ access on those
        if(!$this->ac->isAllowed(QN_R_READ, $this->class)) {
            // user has always READ access to its own objects
			if(!$this->ac->isAllowed(QN_R_CREATE, $this->class)) {
                // no READ nor CREATE permission: deny request
				throw new \Exception($user_id.';READ;'.$this->class, QN_ERROR_NOT_ALLOWED);
			}
            else {
                // user has CREATE access and might have created some objects: limit search to those, if any
                $domain = Domain::conditionAdd($domain, ['creator', '=', $this->am->userId()]);
            }
        }

        // 3) perform search
        // we don't use the start and limit arguments here because the final result set depends on permissions
        $ids = $this->orm->search($this->class, $domain, $params['sort'], 0, 0, $lang);
        // $ids is an error code
        if($ids < 0) {
            throw new \Exception(Domain::toString($domain), $ids);
        }
        if(count($ids)) {
            // filter results using access controller... (reduce resulting ids based on access rights)
            $ids = $this->ac->filter(QN_R_READ, $this->class, [], $ids);
            // init keys of 'objects' member (so far, it is an empty array)
            $this->ids($ids);
        }
        else {
            // reset objects map (empty result)
            $this->objects = [];
        }
        return $this;
    }

    /**
     * Creates new instances by copying exiting ones
     *
     * @return  object  current Collection
     * @example $newObject = MyClass::create();
     *
     */
    public function clone($lang=DEFAULT_LANG) {

        // 1) sanitize and retrieve necessary values
        $user_id = $this->am->userId();

        $schema = $this->instance->getSchema();

        // get full list of available fields
        $fields = array_keys($schema);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(QN_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }

        $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
        $uniques = $this->instance->getUnique();

        $res = $this->orm->read($this->class, $ids, $fields, $lang);

        // 3) duplicate each object of the collection
        foreach($res as $id => $original) {

            // make unique fields complying with related constraints
            foreach($uniques as $unique) {
                $domain = [];
                foreach($unique as $field) {
                    if(!isset($original[$field])) {
                        continue 2;
                    }
                    $domain[] = [$field, '=',  $original[$field]];
                }
                $duplicate_ids = $this->orm->search($this->class, $domain);

                if(count($duplicate_ids)) {
                    foreach($unique as $field) {
                        // #todo - when renaming, we should favor name field if defined
                        if(in_array($schema[$field]['type'], ['string', 'text'])) {
                            $original[$field] = 'copy - '.$original[$field];
                        }
                        else {
                            // remove latest field to prevent duplicate issue
                            unset($original[$field]);
                        }
                        break;
                    }
                }
            }

            if(!count($uniques)) {
                if($schema['name']['type'] == 'string') {
                    $original['name'] = $original['name'].' - copy';
                }
            }

            // set current user as creator
            $original = array_merge($original, ['creator' => $user_id]);

            // validate : check unique keys
            $this->validate($original, [], true);

            // create the clone
            $oid = $this->orm->clone($this->class, $id, $original, $lang);
            if($oid <= 0) {
                // error at creation (possibly duplicate_index)
                throw new \Exception($this->class.'::'.implode(',', $fields), $oid);
            }

            // log action (if enabled)
            $this->logger->log($user_id, 'create', $this->class, $oid);
            // store new object in current collection
            $this->objects[$oid] = ['id' => $oid];
        }

        return $this;
    }

    /**
     * Creates a new instance
     *
     * @param $values   array   list mapping fields and values
     * @param $lang     string  language for multilang fields
     *
     * @return  object  current Collection
     * @example $newObject = MyClass::create();
     */
    public function create(array $values=null, $lang=DEFAULT_LANG) {

        // 1) sanitize and retrieve necessary values
        $user_id = $this->am->userId();
        // silently drop invalid fields
        $values = $this->filter($values);
        // retrieve targeted fields names
        $fields = array_keys($values);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(QN_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }

        // if state is forced to draft, do not check required fields (to allow creation of emtpy objects)
        $check_required = (isset($values['state']) && $values['state'] == 'draft')?false:true;

        // 3) validate : check unique keys and required fields
        $this->validate($values, [], true, $check_required);
        // set current user as creator
        $values = array_merge($values, ['creator' => $user_id]);

        // 4) create the new object
        $oid = $this->orm->create($this->class, $values, $lang);
        if($oid <= 0) {
            throw new \Exception($this->class.'::'.implode(',', $fields), $oid);
        }

        // log action (if enabled)
        $this->logger->log($user_id, 'create', $this->class, $oid);

        // make sure the assigned id is present in the loaded object
        $values['id'] = $oid;

        // store new object in current collection
        $this->objects[$oid] = $values;

        return $this;
    }

    /**
     * When $field argument is empty, only `id` and `name` fields are loaded.
     *
     * @param $fields
     * @param $lang
     */
    public function read($fields, $lang=DEFAULT_LANG) {

        if(count($this->objects)) {
            // retrieve current user id
            $user_id = $this->am->userId();

            // force argument into an array (single field name is accepted, empty array is accepted: load all fields)
            $fields = (array) $fields;

            $schema = $this->instance->getSchema();

            // 1) drop invalid fields and build sub-request array
            $allowed_fields = array_keys($schema);

            // build a list of direct field to load (i.e. "object attributes")
            $requested_fields = [];

            // 'id': we might access an object directly by giving its `id`.
            // 'name': as a convention the name is always provided.
            // 'state': the state of the object is provided for concurrency control (check that a draft object is not validated twice).
            // 'deleted': since some objects might have been soft-deleted we need to load the `deleted` status in order to know if object needs to be in the result set or not.
            // 'modified': the last update timestamp is always provided. At update, if modified is provided, it is compared to the current timestamp to detect concurrent changes.
            $mandatory_fields = ['id', 'name', 'state', 'deleted', 'modified'];

            // remeber relational fields requiring additional loading
            $relational_fields = [];

            foreach($fields as $key => $val ) {
                // handle array notation
                $field = (!is_numeric($key))?$key:$val;
                // check fields validity (and silently drop invalid fields)
                if(!in_array($field, $allowed_fields)) {
                    unset($fields[$key]);
                }
                else {
                    $requested_fields[] = $field;
                    $target = $this->instance->field($field);
                    // remember only requested relational sub-fields
                    // if(is_numeric($key) || !in_array($schema[$field]['type'], ['one2many', 'many2one', 'many2many'])) {
                    if(!is_numeric($key) && in_array($target['type'], ['one2many', 'many2one', 'many2many'])) {
                        $relational_fields[$key] = $val;
                    }
                }
            }

            foreach($mandatory_fields as $field) {
                if(!in_array($field, $requested_fields)) {
                    $requested_fields[] = $field;
                }
            }

            // retrieve targeted identifiers (remove null entries)
            $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });

			// 2) check that current user has enough privilege to perform READ operation
			if(!$this->ac->isAllowed(QN_R_READ, $this->class, $fields, $ids)) {
                throw new \Exception($user_id.';READ;'.$this->class.';'.implode(',',$ids), QN_ERROR_NOT_ALLOWED);
            }

            // 3) read values
            $res = $this->orm->read($this->class, $ids, $requested_fields, $lang);
            // $res is an error code, something prevented to fetch requested fields
            if($res < 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }

            // 4) remove deleted items, if `deleted` field was not explicitely requested
            if(!in_array('deleted', $fields)) {
                foreach($res as $oid => $odata) {
                    if($odata['deleted']) {
                        // remove the object from the result set
                        // when read operation is performed after a search, this situation should not occur
                        unset($res[$oid]);
                    }
                    else {
                        unset($res[$oid]['deleted']);
                    }
                }
            }

            // 5) recursively load sub-fields, if any
            $this->objects = $res;
            foreach($relational_fields as $field => $subfields) {
                // load batches of sub-objects grouped by field
                $ids = [];
                foreach($this->objects as $object) {
                    $ids = array_merge($ids, (array) $object[$field]);
                }
                $target = $this->instance->field($field);
                $objects = $target['foreign_object']::ids($ids)->read($subfields, $lang)->get();
                // assign retrieved values to the objects they're related to
                foreach($this->objects as $id => $object) {
                    $this->objects[$id][$field] = $target['foreign_object']::ids($this->objects[$id][$field])->read($subfields, $lang);
                }
            }

        }
        return $this;
    }

    /**
     *
     * @return  Collection  returns the current instance (allowing calls chaining)
     * @throws  Exception   if some value could not be validated against class contraints (see {class}::getConstraints method)
     *
     */
    public function update(array $values, $lang=DEFAULT_LANG) {
        if(count($this->objects)) {

            // 1) sanitize and retrieve necessary values
            $user_id = $this->am->userId();
            // silently drop invalid fields
            $values = $this->filter($values);
            // retrieve targeted identifiers
            $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
            // retrieve targeted fields names
            $fields = array_keys($values);

            if( !$this->class::onupdate($this->orm, $ids, $values) ) {
                throw new \Exception('onupdate_denied', QN_ERROR_NOT_ALLOWED);
            }

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_WRITE, $this->class, $fields, $ids)) {
                throw new \Exception($user_id.';UPDATE;'.$this->class.';['.implode(',', $fields).'];['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }

            // if object is not yet an instance, check required fields (otherwise, we allow partial update)
            $check_required = (isset($values['state']) && $values['state'] == 'draft')?true:false;

            // 3) validate : check unique keys and required fields
            $this->validate($values, $ids, true, $check_required);

            // 4) write
            // by convention, update operation always sets object state as 'instance' and modifier as current user
            $values = array_merge($values, ['modifier' => $user_id, 'state' => 'instance']);
            $res = $this->orm->write($this->class, $ids, $values, $lang);
            if($res <= 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }
            else {
                $ids = $res;
            }

            foreach($ids as $oid) {
                // log action (if enabled)
                $this->logger->log($user_id, 'update', $this->class, $oid);
                // store updated objects in current collection
                $this->objects[$oid] = array_merge(['id' => $oid], $values);
            }
        }
        return $this;
    }

    public function delete($permanent=false) {
        if(count($this->objects)) {

            // 1) sanitize and retrieve necessary values
            $user_id = $this->am->userId();
            // retrieve targeted identifiers
            $ids = array_keys($this->objects);

            if( !$this->class::ondelete($this->orm, $ids) ) {
                throw new \Exception('ondelete_denied', QN_ERROR_NOT_ALLOWED);
            }

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_DELETE, $this->class, [], $ids)) {
                throw new \Exception($user_id.';DELETE,'.$this->class.'['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }

            // 3) delete
            $res = $this->orm->remove($this->class, $ids, $permanent);
            if($res <= 0) {
                throw new \Exception($this->class.'['.implode(',', $ids).']', $res);
            }
            else {
                $ids = $res;
            }

            // log action (if enabled)
            foreach($ids as $oid) {
                $this->logger->log($user_id, 'delete', $this->class, $oid);
            }

            // 3) update current collection (remove all objects)
            $this->objects = [];
        }
        return $this;
    }
}