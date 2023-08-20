<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

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
     *
     * @var string                                  $class
     * @var \equal\orm\ObjectManager                $objectManager
     * @var \equal\access\AccessController          $accessController
     * @var \equal\auth\AuthenticationManager       $authenticationManager
     * @var \equal\data\adapt\DataAdapterProvider   $dataAdapterProvider
     * @var \equal\log\Logger                       $logger
     */
    public function __construct($class, $objectManager, $accessController, $authenticationManager, $dataAdapterProvider, $logger) {
        // init objects map
        $this->objects = [];

        // assign private members
        $this->class = $class;
        /** @var \equal\orm\ObjectManager */
        $this->orm = $objectManager;
        /** @var \equal\access\AccessController */
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
        // discard all fields but `id`
        $schema = $this->model->getSchema();
        foreach(array_keys($schema) as $field) {
            if($field != 'id') {
                unset($this->model[$field]);
            }
        }
        // default to unlimited page size
        $this->limit(0);
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

    public function getClass() {
        return $this->class;
    }

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
     * @return  array                   Returns an associative array holding objects of the collection. If $to_array is set to true, all sub-collections are recursively converted to arrays and keys are no longer mapping objects identifiers.
     *                                  If the collection is empty, an empty array is returned.
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
                    // set adapter of child collection
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
                    $f = $object->getField($field);
                    if(!$f) {
                        // log an error and ignore adaptation
                        trigger_error("ORM::unexpected error when retrieving Field object for $field ({$object->getType()})", QN_REPORT_INFO);
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
     * Provide the whole collection as an array.
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
     * @return Collection|array  The setter version returns the Collection as a map of identifiers with empty object. The getter version returns an array with all objects identifiers in the Collection.
     */
    public function ids() {
        $args = func_get_args();
        if(count($args) <= 0) {
            return array_keys($this->objects);
        }
        else {
            // reset list
            $this->objects = [];
            // #memo - filling the list with non-readable object(s) raises a NOT_ALLOWED exception at reading
            $ids = array_unique((array) $args[0]);
            // init keys of `objects` member (resulting in a map with keys and Model instances holding default values)
            foreach($ids as $id) {
                $this->objects[$id] = clone $this->model;
            }
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
                trigger_error("ORM::discarding readonly fields ".implode(', ', $readonly_fields), QN_REPORT_INFO);
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
     * @return  Collection          Returns the instance of the current Collection.
     */
    public function adapt($to='json', $lang=null) {
        // set the adapter to be used for adaptOut conversion
        $this->adapter = $this->dap->get($to);
        return $this;
    }

    /**
     * Set Collection to perform ongoing operations using a given language.
     * Language switching can be performed several times on a same collection and is sequential (the lang that will be used is the one set by the last `lang()` call).
     * #memo - the lang is set in constructor and defaults to 'en'.
     *
     * @param   string      $lang   ISO 639 code of the lang to use for further CRUD operations.
     * @return  Collection          Returns the instance of the current Collection.
     */
    public function lang($lang) {
        $this->lang = $lang;
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
     * Feed the Collection with the IDs of the objects (of current class) that comply with the given domain.
     * If no domain is given and parameter limit is left to 0, all objects are taken in account (Warning: reading such Collection might consume a large amount of memory)
     *
     * The domain syntax is : array( array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]]) [, array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]])])
     * Array of several series of clauses joined by logical ANDs themselves joined by logical ORs : disjunctions of conjunctions
     * i.e.: (clause[, AND clause [, AND ...]]) [ OR (clause[, AND clause [, AND ...]]) [ OR ...]]
     */
    public function search(array $domain=[], array $params=[], $lang=null) {
        // #memo - by default, we set start and limit arguments to 0 (to be ignored) because the final result set depends on User's permissions
        // (and therefore, in  most situation, start() and limit() are chained to the Collection for that purpose)
        $defaults = [
            'sort'  => ['id' => 'asc'],
            'limit' => 0,
            'start' => 0
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
                throw new \Exception(serialize(['invalid_domain' => Domain::toString($domain)]), QN_ERROR_INVALID_PARAM);
            }
        }

        // 2) adapt domain according to access control type : either with ACL (permission) or Role (assignment)

        if($this->orm->hasObjectRoles($this->class)) {
            $roles = [];
            // find all roles for which a READ right is granted
            foreach($this->class::getRoles() as $role => $descriptor) {
                if(isset($descriptor['rights'])) {
                    if($descriptor['rights'] & QN_R_READ) {
                        $roles[] = $role;
                    }
                }
            }
            // find the objects for which the user is explicitly assigned to one of these roles
            $assignments_ids = $this->orm->search('core\Assignment', [ ['user_id', '=', $user_id], ['role', 'in', $roles], ['object_class', '=', $this->class] ]);
            if($assignments_ids > 0 && count($assignments_ids)) {
                $assignments = $this->orm->read('core\Assignment', $assignments_ids, ['object_id']);
                $objects_ids = array_map(function ($a) {return $a['object_id'];}, array_values($assignments));
                // limit search amongst those objects
                $domain = Domain::conditionAdd($domain, ['id', 'in', $objects_ids]);
            }
        }
        else {
            // if user is not granted for READ on full class (nor parent class), neither directly nor indirectly (groups)
            if(!$this->ac->hasRight($user_id, QN_R_READ, $this->class)) {
                // find the objects for which the user is explicitly granted for READ
                $permissions_ids = $this->orm->search('core\Permission', [ ['user_id', '=', $user_id], ['rights', '>=', QN_R_READ], ['object_class', '=', $this->class] ]);
                if($permissions_ids > 0 && count($permissions_ids)) {
                    $permissions = $this->orm->read('core\Permission', $permissions_ids, ['object_id']);
                    $objects_ids = array_map(function ($a) {return $a['object_id'];}, array_values($permissions));
                    // limit search amongst those objects
                    $domain = Domain::conditionAdd($domain, ['id', 'in', $objects_ids]);
                }
            }
        }

        // 3) perform search

        // search according to resulting domain and specific params, if given
        $ids = $this->orm->search($this->class, $domain, $params['sort'], $params['start'], $params['limit'], $lang);

        // $ids is an error code
        if($ids < 0) {
            throw new \Exception(Domain::toString($domain), $ids);
        }
        if(count($ids)) {
            // init keys of `objects` member (so far, it is an empty array)
            $this->ids($ids);
        }
        else {
            // reset objects map (empty result)
            $this->objects = [];
        }
        return $this;
    }

    /**
     * Create new instances by cloning the ones present in current Collection.
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

        $res = $this->orm->read($this->class, $ids, $fields, ($lang)?$lang:$this->lang);

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
        $values = $this->filter((array) $values, false);
        // retrieve targeted fields names
        $fields = array_map(function($value, $key) {
                return is_numeric($key)?$value:$key;
            }, $values, array_keys($values));

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(QN_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }

        // if state is forced to draft, do not check required fields (to allow creation of empty/draft objects)
        $check_required = (isset($values['state']) && $values['state'] == 'draft')?false:true;

        // 3) validate : check required fields accordingly
        // #memo - we must check unique constraints at creation, but allow unique fields left to null (not set yet) in order to be able to create several draft objects
        $this->validate($values, [], true, $check_required);
        // set current user as creator and modifier
        $values['creator'] = $user_id;
        $values['modifier'] = $user_id;

        // 4) create the new object
        $oid = $this->orm->create($this->class, $values, ($lang)?$lang:$this->lang);
        if($oid <= 0) {
            throw new \Exception($this->orm->getLastError(), $oid);
        }

        // log action (if enabled)
        $this->logger->log($user_id, 'create', $this->class, $oid, $values);

        // put new object in current collection
        $this->id($oid)->read(['id']);

        return $this;
    }

    /**
     * When $field argument is empty, only `id` and `name` fields are loaded.
     *
     * @param $fields
     * @param $lang     Specific language in which values have to be read. Defaults to language set on the Collection.
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
			if(!$this->ac->isAllowed(QN_R_READ, $this->class, $requested_fields, $ids)) {
                throw new \Exception($user_id.';READ;'.$this->class.';'.implode(',',$ids), QN_ERROR_NOT_ALLOWED);
            }

            // 3) read values
            $res = $this->orm->read($this->class, $ids, $requested_fields, ($lang)?$lang:$this->lang);
            // $res is an error code, something prevented to fetch requested fields
            if($res < 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }

            // 4) remove deleted items, if `deleted` field was not explicitly requested
            if(!in_array('deleted', $fields)) {
                foreach($res as $id => $object) {
                    if($object['deleted']) {
                        // remove the object from the result set
                        // when read operation is performed after a search, this situation should not occur
                        unset($res[$id]);
                    }
                    else {
                        unset($res[$id]['deleted']);
                    }
                }
            }

            // merge retrieved values with current collection (overwrite if already present)
            foreach($res as $id => $object) {
                foreach($object as $field => $value) {
                    $this->objects[$id][$field] = $value;
                }
            }

            // 5) recursively load sub-fields, if any (load a batches of sub-objects grouped by field)
            foreach($fields as $field => $subfields ) {
                // discard direct fields
                if(is_numeric($field)) {
                    continue;
                }
                // we accept single value as subfields
                $subfields = (array) $subfields;
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
                $this->orm->read($target['foreign_object'], $children_ids, $children_fields, ($lang)?$lang:$this->lang);
                // assign retrieved values to the objects they relate to
                foreach($this->objects as $id => $object) {
                    /** @var Collection */
                    $children = $target['foreign_object']::ids($this->objects[$id][$field])->read($subfields, ($lang)?$lang:$this->lang);
                    if($target_type == 'many2one') {
                        // #memo - result might be null or an Object (that might contain sub-collections)
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
            // unless explicitly assigned (to another value than 'draft'), update operation always sets state to 'instance'
            if(!isset($values['state']) || $values['state'] == 'draft') {
                $values['state'] = 'instance';
            }
            $res = $this->orm->update($this->class, $ids, $values, $lang);
            if($res <= 0) {
                throw new \Exception($this->orm->getLastError(), $res);
            }

            foreach($ids as $oid) {
                // log action (if enabled)
                $this->logger->log($user_id, 'update', $this->class, $oid, $values);
                // store updated objects in current collection
                $this->objects[$oid]['id'] = $oid;
                foreach($values as $field => $value) {
                    $this->objects[$oid][$field] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * @return  Collection  returns the current instance (allowing calls chaining)
     */
    public function delete($permanent=false) {
        if(count($this->objects)) {
            $user_id = $this->am->userId();

            // 1) sanitize and retrieve necessary values
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

    /**
     * Attempts to perform a specific action to a series of objects.
     * If no workflow is defined, the call is ignored (no action is taken).
     * If there is no match or if there are some conditions on the transition that are not met, it returns an error code.
     *
     * @return  Collection  returns the current instance (allowing calls chaining)
     */
    public function transition($transition) {
        // retrieve targeted identifiers
        $res = $this->orm->transition($this->class, $this->ids(), $transition);
        if(count($res)) {
            throw new \Exception(serialize($res), QN_ERROR_NOT_ALLOWED);
        }
        return $this;
    }


    /**
     * Attempts to perform a specific action to a series of objects.
     * If no workflow is defined, the call is ignored (no action is taken).
     * If there is no match or if there are some conditions on the transition that are not met, it returns an error code.
     *
     * @param   string      $action       Name of the requested action.
     * @return  Collection  returns the current instance (allowing calls chaining)
     */
    public function do($action) {
        // check if action can be performed
        $user_id = $this->am->userId();
        $res = $this->ac->canPerform($user_id, $action, $this->class, $this->ids());
        if(count($res)) {
            throw new \Exception(serialize($res), QN_ERROR_NOT_ALLOWED);
        }

        $actions = $this->class::getActions();

        if(isset($actions[$action]) && isset($actions[$action]['function'])) {
            // retrieve targeted identifiers
            $ids = array_keys($this->objects);
            if(count($ids)) {
                $this->orm->callonce($this->class, $actions[$action]['function'], $ids);
            }
        }

        return $this;
    }

    /**
     * Following methods are defined here because we want children classes to have arbitrary parameters (the only constraint is for multiple inheritance: children classes must implement a method the same way their parent does).
     * In case these methods are invoked on the Model class, the code ends up here.
     */

    /**
     * Check wether an object can be created.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $values     Associative array holding the values to be assigned to the new instance (not all fields might be set).
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be created.
     */
    public static function cancreate($om, $values=[], $lang='en') {
        return [];
    }


    /**
     * Check wether an object can be updated.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @param  array            $values     Associative array holding the new values to be assigned.
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be updated.
     */
    public static function canupdate($om, $ids=[], $values=[], $lang='en') {
        return [];
    }


    /**
     * Check wether an object can be cloned.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @return array            Returns an associative array mapping fields with their error messages. En empty array means that object has been successfully processed and can be updated.
     */
    public static function canclone($om, $ids=[]) {
        return [];
    }


    /**
     * Check wether an object can be deleted.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be deleted.
     */
    public static function candelete($om, $ids=[]) {
        return [];
    }


    /**
     * Hook invoked after object creation for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers. Should contain only the id of the object just created.
     * @param  array            $values     Associative array holding the newly assigned values.
     * @param  string           $lang       Language in which multilang fields are being created.
     * @return void
     */
    public static function oncreate($om, $ids=[], $values=[], $lang='en') {
    }


    /**
     * Hook invoked before object update for performing object-specific additional operations.
     * Current values of the object can still be read for comparing with new values.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @param  array            $values     Associative array holding the new values that have been assigned.
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return void
     */
    public static function onupdate($om, $ids=[], $values=[], $lang='en') {
    }

    /**
     * Hook invoked after object cloning for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @return void
     */
    public static function onclone($om, $ids=[]) {
    }


    /**
     * Hook invoked before object deletion for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @return void
     */
    public static function ondelete($om, $ids=[]) {
    }


    /**
     * Signature for single object values change in UI.
     * This method does not imply an actual update of the model, but a potential one (not made yet) and is intended for front-end only.
     *
     * Accepts variable list of arguments, based on their names :
     * @param  Collection       $self       Collection holding a series of objects of current class.
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $ids        List of objects identifiers.
     * @param  array            $event      Associative array holding changed fields as keys, and their related new values.
     * @param  array            $values     Copy of the current (partial) state of the object.
     * @return array            Returns an associative array mapping fields with their resulting values.
     */
    public static function onchange($om, $event=[], $values=[], $lang='en') {
        return [];
    }
}
