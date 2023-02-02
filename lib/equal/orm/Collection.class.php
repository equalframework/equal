<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\data\adapt\DataAdapterProvider;

class Collection implements \Iterator, \Countable {

    /** @var \equal\orm\ObjectManager */
    private $orm;

    /** @var \equal\access\AccessController */
    private $ac;

    /** @var \equal\auth\AuthenticationManager */
    private $am;

    /** @var \equal\data\adapt\DataAdapterProvider */
    private $dap;

    /** @var \equal\data\adapt\DataAdapter */
    private $adapter;

    /** @var string */
    private $lang;

    /** @var \equal\log\Logger */
    private $logger;

    /**
     * Name of the class targeted by the Collection.
     * @var string
     */
    private $class;


    /**
     * Instance of targeted class (used for retrieving fields and schema; inherits from Model class.
     * @var \equal\orm\Model
     */
    private $model;

    /* map associating objects with their values with identifiers as keys */
    private $objects;

    /* pagination limit: size of a page when searching or loading sub-objects (default is 0, i.e. no limit) */
    private $limit;

    /**
     *
     * This method makes valid 'is_callable' tests on any static method name (i.e. entry calls, ::search(), ::ids(), ::id(), ::create() )
     */
    public static function __callStatic($name, $arguments) {}

    /**
     * Collection constructor.
     * This is called through the Collections service, which retrieves the dependencies through its container.
     * Collections service is a factory that creates Collection instances when requested
     * (i.e. when a magic method is called on a class that derives from namespace `equal\orm\Model`).
     */
    public function __construct($class, $objectManager, $accessController, $authenticationManager, $dataAdapterProvider, $logger) {
        // init objects map
        $this->objects = [];

        // assign private members
        $this->class = $class;
        $this->orm = $objectManager;
        $this->ac = $accessController;
        $this->am = $authenticationManager;
        $this->dap = $dataAdapterProvider;
        $this->adapter = null;
        // $this->adapter = $dataAdapter;

        // default to 'en' version of the content
        $this->lang = 'en';

        $this->logger = $logger;
        // check mandatory services
        if(!$this->orm || !$this->ac) {
            throw new \Exception(__CLASS__, QN_ERROR_UNKNOWN);
        }
        // retrieve static instance for target class
        $this->model = $this->orm->getModel($class);
        if($this->model === false) {
            throw new \Exception($class, QN_ERROR_UNKNOWN_OBJECT);
        }
        // default to unlimited page size
        $this->limit(0);
    }

    // #todo - deprecate : this method doesn't seem to be used
    /**
     * @deprecated
     */
    public function extend($column, $description) {
        $this->model->setColumn($column, $description);
    }

    // #todo - deprecate : this method doesn't seem to be used
    /**
     * @deprecated
     */
    public function set($fields) {
        $fields = (array) $fields;
        foreach($this->objects as $id => $object) {
            foreach($fields as $field => $value) {
                $this->objects[$id][$field] = $value;
            }
        }
        return $this;
    }

    /* Countable  methods */

    public function count(): int {
        return count($this->objects);
    }

    /* Iterator  methods */

    public function current() {
        return current($this->objects);
    }

    public function key(): int {
        return key($this->objects);
    }

    public function next(): void {
        next($this->objects);
    }

    public function rewind(): void {
        reset($this->objects);
    }

    public function valid(): bool {
        $key = key($this->objects);
        $res = ($key !== null && $key !== false);
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
     *  Shift out the n first objects of the collection.
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
     * @param   $to_array   boolean    Flag to ask conversion to an array (instead of a map). If set to true, the object is also adapted using the current DataAdapter.
     * @return array|null  If current queue is not empty, returns the first object, otherwise returns null.
     */
    public function first($to_array=false) {
        if(!count($this->objects)) {
            return null;
        }
        $object = reset($this->objects);
        return ($to_array)?$this->get_raw_object($object, $to_array):$object;
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
        $object = end($this->objects);
        return ($to_array)?$this->get_raw_object($object, $to_array):$object;
    }

    /**
     * Provide the whole collection as a map (by default) or as an array.
     *
     * @param   boolean $to_array       Flag to force conversion to an array (instead of a map). If set to true, the returned result is an of objects with keys holding indexes (and no ids).
     * @return  array                   Returns an array holding objects of the collection. If $to_array is set to true, all sub-collections are recursively converted to arrays. If the collection is empty, an empty array is returned.
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
     * Recursively generate an array representation of an object from current collection,
     * handling sub-Collection instances either as maps or arrays.
     *
     * @param   Model   $object         Object to be converted to an array representation.
     * @param   boolean $to_array       Flag for requesting sub-collections as arrays (instead of a maps)
     */
    private function get_raw_object($object, $to_array=false) {
        $result = [];
        foreach($object as $field => $value) {
            if($value instanceof Collection) {
                if($this->adapter) {
                    $value->adapt($this->adapter->getType());
                }
                $result[$field] = $value->get($to_array);
            }
            elseif($value instanceof Model) {
                $result[$field] = $this->get_raw_object($value, $to_array);
            }
            else {
                if($to_array && $this->adapter) {
                    /** @var \equal\orm\Field */
                    $f = $this->model->getField($field);
                    if(!$f) {
                        // log an error and ignore adaptation
                        trigger_error("QN_DEBUG_ORM::unexpected error when retrieving Field object for $field ({$this->model->getType()})", QN_REPORT_INFO);
                        $result[$field] = $value;
                        continue;
                    }
                    // #todo - make a distinction between lang and locale (the latter should be used here)
                    $result[$field] = $this->adapter->adaptOut($value, $f->getUsage(), $this->lang);
                }
                else {
                    $result[$field] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Provide the while collection as an array.
    */
    public function toArray() {
        return $this->get(true);
    }

    /**
     * Initialize an empty Collection based on a given id.
     * This is an alias of the `ids()` method for convenience when a single id is passed.
     *
     * @return Collection Returns the Collection with a single empty object.
     */
    public function id($id) {
        return $this->ids((array) $id);
    }

    /**
     * Combined method depending on the arguments.
     * Getter - returns the list of objects identifiers in the Collection.
     * Setter - initialize the Collection with a list of identifiers (that are mapped with empty objects).
     * Note: neither ids validity not user rights are checked at this point (will be within subsequent CRUD calls).
     *
     * @param array optional if given, sets current objects array, if not returns current ids
     *
     * @return (Collection|array)  Setter version returns the Collection with a single empty object. Getter version returns an array with all objects identifiers.
     */
    public function ids() {
        $args = func_get_args();
        if(count($args) <= 0) {
            return array_keys($this->objects);
        }
        else {
            // #memo - filling the list with non-readable object(s) raises a NOT_ALLOWED exception at reading
            $ids = array_unique((array) $args[0]);
            // init keys of 'objects' member (resulting in a map with keys but no values)
            $this->objects = array_fill_keys($ids, []);
        }
        return $this;
    }

    /**
     * Filters a map of fields-values entries or an array of fields names by discarding special fields; fields marked as readonly; and fields unknown to the current class.
     *
     * @param   array   $fields             Associative array mapping field names with their values.
     * @param   bool    $check_readonly     If set to true, readonly fields are discarded.
     *
     * @return  array   Filtered array containing known fields names only.
     */
    private function filter(array $fields, bool $check_readonly=true) {
        $result = [];
        if(count($fields)) {
            $schema = $this->model->getSchema();
            // retrieve valid fields, i.e. fields from schema
            $allowed_fields = array_keys($schema);
            if($check_readonly) {
                // discard readonly fields
                $readonly_fields = array_filter($allowed_fields, function ($field) use($schema) {
                    return (isset($schema[$field]['readonly']))?($schema[$field]['readonly'] && $schema[$field]['type'] != 'computed'):false;
                });
                $allowed_fields = array_diff($allowed_fields, $readonly_fields);
                // log a notice about discarded readonly fields
                trigger_error("QN_DEBUG_ORM::discarding readonly fields ".implode(', ', $readonly_fields), QN_REPORT_INFO);
            }
            // discard special fields
            // #memo - `state` is left allowed for draft creation
            $allowed_fields = array_diff($allowed_fields, ['id','creator','created','modifier','modified','deleted']);
            // filter $fields argument
            $result = array_intersect_key($fields, array_flip($allowed_fields));
        }
        return $result;
    }

    /**
     * Mark Collection to be converted using the DataAdapter matching the target type (specific content-type or 'json', 'sql', 'txt').
     * Conversion is asynchronous. It is performed at Collection export (`get(true)`, `first(true)` or `last(true)`) and is based on field types.
     *
     * @param   string      $to     Target string  might be a map associating fields with their values, or a map association ids with objects
     * @param   string      $lang   Deprecated, use method `Collection->lang($lang)`.
     * @return  Collection          Returns the current Collection instance.
     */
    public function adapt($to='json', $lang=null) {
        // set the adapter to be used for adaptOut conversion
        $this->adapter = $this->dap->get($to);
        return $this;
    }

    /**
     *
     * @param $fields   array   associative array holding values and their related fields as keys
     * @throws  Exception   if some value could not be validated against class constraints (see {class}::getConstraints method)
     */
    private function validate(array $fields, $ids=[], $check_unique=false, $check_required=false) {
        $validation = $this->orm->validate($this->class, $ids, $fields, $check_unique, $check_required);
        if($validation < 0 || count($validation)) {
            foreach($validation as $error_code => $error_descr) {
                if(is_array($error_descr)) {
                    $error_descr = serialize($error_descr);
                }
                // send error using the same format as the announce method
                throw new \Exception($error_descr, (int) $error_code);
            }
        }
    }

    /**
     *
     */
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

    /**
     *
     */
    public function search(array $domain=[], array $params=[], $lang=null) {
        $defaults = [
            'sort'  => ['id' => 'asc']
        ];

        // retrieve current user id
        $user_id = $this->am->userId();

        if(isset($params['sort'])) {
            $params['sort'] = (array) $params['sort'];
        }
        $params = array_merge($defaults, $params);

        // 1) sanitize and validate given domain
        if(!empty($domain)) {
            $domain = Domain::normalize($domain);
            $schema = $this->model->getSchema();
            if(!Domain::validate($domain, $schema)) {
                throw new \Exception(Domain::toString($domain), QN_ERROR_INVALID_PARAM);
            }
        }

        // 2) check that current user has enough privilege to perform READ operation on given class
        // #todo - extract fields names from domain, and make sure user has R_READ access on those
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
            // filter results using access controller (reduce resulting ids based on access rights of current user)
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
     * @return  Collection  Returns the current Collection.
     * @example $newObject = MyClass::id(5)->clone()->first();
     */
    public function clone($lang=null) {

        // 1) sanitize and retrieve necessary values
        $user_id = $this->am->userId();

        $schema = $this->model->getSchema();
        // retrieve targeted identifiers
        $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
        // get full list of available fields
        $fields = array_keys($schema);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(QN_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }

        $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
        $uniques = $this->model->getUnique();

        $res = $this->orm->read($this->class, $ids, $fields, $lang);

        // #todo - this should be done in ObjectManager

        // 3) duplicate each object of the collection
        foreach($res as $id => $obj) {
            $original = is_array($obj) ? $obj : $obj->toArray();

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
                            // #todo - this does not guarantee that there will be no duplicate
                            // remove latest field to prevent duplicate issue
                            unset($original[$field]);
                        }
                        break;
                    }
                }
            }

            if(isset($schema['name']['type']) && $schema['name']['type'] == 'string') {
                $original['name'] = $original['name'].' - copy';
            }

            // set current user as creator and modifier
            $original['creator'] = $user_id;
            $original['modifier'] = $user_id;

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
     * Creates a new Object.
     *
     * @param   array   $values   Associative array mapping fields and values.
     * @param   string  $lang     Language for multilang fields.
     *
     * @return  Collection  Returns the current Collection.
     * @example $newObject = MyClass::create(['name'=>'test','code'=>3])->first();
     */
    public function create(array $values=null, $lang=null) {

        // 1) sanitize and retrieve necessary values
        $user_id = $this->am->userId();
        // silently drop invalid fields (do not check readonly: all fields are allowed at creation)
        $values = $this->filter($values, false);
        // retrieve targeted fields names
        $fields = array_keys($values);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(QN_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }

        // if state is forced to draft, do not check required fields (to allow creation of empty/draft objects)
        $check_required = (isset($values['state']) && $values['state'] == 'draft')?false:true;

        // 3) validate : check required fields accordingly
        // #memo - we cannot check unicity constraints at creation, since some fields might be null (not set yet) AND we must be able to create several draft objects
        $this->validate($values, [], false, $check_required);
        // set current user as creator and modifier
        $values['creator'] = $user_id;
        $values['modifier'] = $user_id;

        // 4) create the new object
        $res = $this->orm->create($this->class, $values, $lang);
        if($res <= 0) {
            throw new \Exception($this->orm->getLastError(), $res);
        }

        $oid = $res;

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
     * @return Collection   Returns the current collection.
     */
    public function read($fields, $lang=null) {

        if(count($this->objects)) {
            // retrieve current user id
            $user_id = $this->am->userId();

            // force argument into an array (single field name is accepted, empty array is accepted: load all fields)
            $fields = (array) $fields;

            $schema = $this->model->getSchema();

            // 1) drop invalid fields and build sub-request array
            $allowed_fields = array_keys($schema);

            // build a list of direct field to load (i.e. "object attributes")
            $requested_fields = [];

            // 'id': we might access an object directly by giving its `id`.
            // 'state': the state of the object is provided for concurrency control (check that a draft object is not validated twice).
            // 'deleted': since some objects might have been soft-deleted we need to load the `deleted` state in order to know if object needs to be in the result set or not.
            // 'modified': the last update timestamp is always provided. At update, if modified is provided, it is compared to the current timestamp to detect concurrent changes.
            // #memo - we cannot add 'name' by default since it might be a computed field (that could lead to a circular dependency)
            $mandatory_fields = ['id', /*'name',*/ 'state', 'deleted', 'modified'];

            foreach($fields as $key => $val ) {
                // handle array notation
                $field = (!is_numeric($key))?$key:$val;
                // check fields validity (and silently drop invalid fields)
                if(!in_array($field, $allowed_fields)) {
                    unset($fields[$key]);
                    continue;
                }
                else {
                    $requested_fields[] = $field;
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

            // 4) remove deleted items, if `deleted` field was not explicitly requested
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

            $this->objects = $res;

            // 5) recursively load sub-fields, if any (load a batches of sub-objects grouped by field)
            foreach($fields as $field => $subfields ) {
                // discard direct fields
                if(is_numeric($field)) {
                    continue;
                }
                // #memo - using Field object guarantees support for `alias` and `computed` fields
                $targetField = $this->model->getField($field);
                $target = $targetField->getDescriptor();

                $target_type = (isset($target['result_type']))?$target['result_type']:$target['type'];
                if(!in_array($target_type, ['one2many', 'many2one', 'many2many'])) {
                    continue;
                }
                $children_ids = [];
                foreach($this->objects as $object) {
                    foreach((array) $object[$field] as $oid) {
                        $children_ids[] = $oid;
                    }
                }
                $children_fields = [];
                foreach($subfields as $key => $val) {
                    $children_fields[] = (!is_numeric($key))?$key:$val;
                }
                // read all targeted children objects at once
                $this->orm->read($target['foreign_object'], $children_ids, $children_fields, $lang);
                // assign retrieved values to the objects they relate to
                foreach($this->objects as $id => $object) {
                    /** @var Collection */
                    $children = $target['foreign_object']::ids($this->objects[$id][$field])->read($subfields, $lang);
                    if($target_type == 'many2one') {
                        // #memo - result might be null or contain sub-collections
                        $this->objects[$id][$field] = $children->first();
                    }
                    else {
                        $this->objects[$id][$field] = $children;
                    }
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param   array       $values   associative array mapping fields and values
     * @param   string      $lang     Language for multilang fields.
     *
     * @return  Collection  returns the current instance (allowing calls chaining)
     * @throws  Exception   if some value could not be validated against class constraints (see {class}::getConstraints method)
     */
    public function update(array $values, $lang=null) {
        if(count($this->objects)) {

            // 1) sanitize and retrieve necessary values
            $user_id = $this->am->userId();
            // silently drop invalid fields
            $values = $this->filter($values);
            // retrieve targeted identifiers
            $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
            // retrieve targeted fields names
            $fields = array_keys($values);

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_WRITE, $this->class, $fields, $ids)) {
                throw new \Exception($user_id.';UPDATE;'.$this->class.';['.implode(',', $fields).'];['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }

            // if object is not yet an instance, check required fields (otherwise, we allow partial update)
            $check_required = (isset($values['state']) && $values['state'] == 'draft')?true:false;

            // 3) validate : check unique keys and required fields
            $this->validate($values, $ids, true, $check_required);

            // 4) update objects
            // by convention, update operation sets modifier as current user
            $values['modifier'] = $user_id;
            if(!isset($values['state']) || $values['state'] == 'draft') {
                // unless explicitly assigned (to another value than 'draft'), update operation always sets state to 'instance'
                $values['state'] = 'instance';
            }
            $res = $this->orm->update($this->class, $ids, $values, $lang);
            if($res <= 0) {
                throw new \Exception($this->orm->getLastError(), $res);
            }

            foreach($ids as $oid) {
                // log action (if enabled)
                $this->logger->log($user_id, 'update', $this->class, $oid);
                // store updated objects in current collection
                $this->objects[$oid]['id'] = $oid;
                foreach($values as $field => $value) {
                    $this->objects[$oid][$field] = $value;
                }
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

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_DELETE, $this->class, [], $ids)) {
                throw new \Exception($user_id.';DELETE,'.$this->class.'['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }

            // 3) delete objects
            $res = $this->orm->delete($this->class, $ids, $permanent);
            if($res <= 0) {
                throw new \Exception($this->orm->getLastError(), $res);
            }
            else {
                $ids = $res;
            }

            // log action (if enabled)
            foreach($ids as $oid) {
                $this->logger->log($user_id, 'delete', $this->class, $oid);
            }

            // 4) update current collection (remove all objects)
            $this->objects = [];
        }
        return $this;
    }
}