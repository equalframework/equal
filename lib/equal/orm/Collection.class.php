<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

class Collection implements \Iterator, \Countable {

    /** @var \equal\services\Container */
    private $container;

    /** @var \equal\orm\ObjectManager */
    protected $orm;

    /** @var \equal\access\AccessController */
    protected $ac;

    /** @var \equal\auth\AuthenticationManager */
    protected $am;

    /** @var \equal\data\adapt\DataAdapterProvider */
    protected $dap;

    /** @var \equal\data\adapt\DataAdapter */
    protected $adapter;

    /** @var string */
    protected $lang;

    /** @var \equal\log\Logger */
    protected $logger;

    /**
     * Name of the class targeted by the Collection.
     * @var string
     */
    protected $class;


    /**
     * Instance of targeted class (used for retrieving fields and schema; inherits from Model class.
     * @var \equal\orm\Model
     */
    protected $model;

    /* map associating objects with their values with identifiers as keys */
    protected $objects;

    /* pagination limit: size of a page when searching or loading sub-objects (default is 0, i.e. no limit) */
    protected $limit;

    /**
     *
     * This method makes valid 'is_callable' tests on any static method name (i.e. entry calls, ::search(), ::ids(), ::id(), ::create() )
     */
    public static function __callStatic($name, $arguments) {}

    /**
     * Collection constructor.
     * This is called through the Collections service, which retrieves the dependencies through its container.
     * Collections service is a factory that creates Collection instances when requested.
     * (i.e. when a magic method is called on a class that derives from namespace `equal\orm\Model`)
     *
     * @var string                                  $class
     * @var \equal\service\Container                $container      Service container relayed from Collections service.
     */
    public function __construct($class, $container) {
        // init objects map
        $this->objects = [];

        // assign private members
        $this->class = $class;

        $this->container = $container;

        $this->orm = $this->container->get('orm');
        $this->ac = $this->container->get('access');
        $this->am = $this->container->get('auth');
        $this->dap = $this->container->get('adapt');
        $this->logger = $this->container->get('log');

        $this->adapter = null;

        // default according to config
        $this->lang = constant('DEFAULT_LANG');

        // check mandatory services
        if(!$this->orm || !$this->ac) {
            throw new \Exception(__CLASS__, EQ_ERROR_UNKNOWN);
        }
        // retrieve static instance for target class
        $this->model = $this->orm->getModel($class);
        if($this->model === false) {
            throw new \Exception($class, EQ_ERROR_UNKNOWN_OBJECT);
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

    public function current(): Model {
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
        return ($to_array) ? $this->_getRawObject($object, $to_array) : $object;
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
        return ($to_array)?$this->_getRawObject($object, $to_array):$object;
    }

    /**
     * Provide the whole collection as a map (by default) or as an array.
     *
     * @param   boolean $to_array       Flag to force conversion to an array (instead of a map). If set to true, the returned result is a list of objects with keys holding indexes and not ids. This is applied recursively.
     * @return  array                   Returns an associative array holding objects of the collection. If $to_array is set to true, all sub-collections are recursively converted to arrays and keys are no longer mapping objects identifiers.
     *                                  If the collection is empty, an empty array is returned.
     */
    public function get($to_array=false) {
        $result = [];
        foreach($this->objects as $id => $object) {
            $result[$id] = $this->_getRawObject($object, $to_array, true);
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
     * @param   boolean $to_array       Flag for requesting sub-collections as lists. Default is associative array (ids mapping related objects).
     * @param   boolean $adapt          Flag for requesting recursive adaptation for values (set to true only in call from `get()` method).
     */
    private function _getRawObject($object, $to_array=false, $adapt=false) {
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
                $result[$field] = $this->_getRawObject($value, $to_array);
            }
            else {
                if($this->adapter && ($to_array || $adapt)) {
                    $f = $object->getField($field);
                    if(!$f) {
                        // log an error and ignore adaptation
                        trigger_error("ORM::unexpected error when retrieving Field object for $field ({$object->getType()})", EQ_REPORT_WARNING);
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
     * Provide the whole Collection as an array.
     * Objects and sub-collections will be converted to arrays as well.
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
        if($id instanceof \equal\orm\Model) {
            $id = $id['id'];
        }
        elseif(is_array($id)) {
            if(isset($id['id']) && is_scalar($id['id'])) {
                $id = $id['id'];
            }
            else {
                throw new \Exception("non_scalar_or_missing_id", EQ_ERROR_INVALID_PARAM);
            }
        }

        if(!is_scalar($id)) {
            throw new \Exception("non_scalar_id", EQ_ERROR_INVALID_PARAM);
        }

        return $this->ids([(int) $id]);
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
            return array_filter(array_keys($this->objects), function($a) { return ($a > 0); });
        }
        else {
            $ids = $this->orm->filterExistingIdentifiers($this->class, (array) $args[0]);
            // reset list
            $this->objects = [];
            // init keys of `objects` member (resulting in a map with keys and Model instances holding default values)
            foreach($ids as $id) {
                $this->objects[$id] = clone $this->model;
                $this->objects[$id]['id'] = $id;
            }
        }
        return $this;
    }

    /**
     * Checks a given map of fields-values entries or an array of fields names, and filters out (discard):
     *   - special fields;
     *   - fields marked as readonly;
     *   - fields unknown to the given class.
     *
     * @param   array   $fields         Associative array mapping field names with their values.
     * @param   string  $operation      Targeted CRUD operation. If set to 'create', system fields are discarded.
     *
     * @return  array   Filtered array containing known fields names only.
     */
    private function sanitizeFields(array $fields, string $operation) {
        $result = [];
        if(count($fields)) {
            $schema = $this->model->getSchema();
            // retrieve valid fields, i.e. fields from schema
            $allowed_fields = array_keys($schema);
            if($operation == 'create') {
                // discard special fields (except `id` and `state`)
                $allowed_fields = array_diff($allowed_fields, ['creator','created','modifier','modified','deleted']);
                // #memo - all fields are allowed at creation (do not drop readonly)
            }
            elseif($operation == 'update') {
                // discard readonly fields
                $readonly_fields = array_filter($allowed_fields, function ($field) use($schema) {
                        return (isset($schema[$field]['readonly'])) ? ($schema[$field]['readonly'] && $schema[$field]['type'] != 'computed') : false;
                    });
                $allowed_fields = array_diff($allowed_fields, $readonly_fields);
                // discard special fields (except `state`)
                $allowed_fields = array_diff($allowed_fields, ['id','creator','created','modifier','modified','deleted']);
                // log a notice about discarded readonly fields
                trigger_error("ORM::discarding readonly fields ".implode(', ', $readonly_fields), EQ_REPORT_INFO);
            }
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
     * Set Collection to perform ongoing operations using the given language.
     * Language switching can be performed several times on a same collection and is sequential (the lang that will be used is the one set by the last `lang()` call).
     * #memo - the lang is set in constructor and defaults to 'en'.
     *
     * @param   string      $lang   ISO 639 code of the lang to use for further CRUD operations.
     * @return  Collection          Returns the instance of the current Collection.
     */
    public function lang($lang) {
        // #todo - discard if given lang is not an acceptable value
        $this->lang = $lang;
        return $this;
    }

    /**
     *
     * @param $fields   array   associative array holding values and their related fields as keys
     * @throws  Exception   if some value could not be validated against class constraints (see {class}::getConstraints method)
     */
    private function validate(array $fields, $ids=[], $check_unique=false, $check_required=false) {
        $errors = $this->orm->validate($this->class, $ids, $fields, $check_unique, $check_required);
        if(count($errors)) {
            foreach($errors as $error_id => $description) {
                if(is_array($description)) {
                    $description = serialize($description);
                }
                // send error using the same format as the announce method
                throw new \Exception($description, (int) $error_id);
            }
        }
    }

    /**
     *
     */
    public function grant($users_ids, $operation, $fields=[]) {
        // retrieve targeted identifiers
        $ids = $this->ids();

        // 2) check that current user has enough privilege to perform READ operation
        if(!$this->ac->isAllowed(EQ_R_MANAGE, $this->class, $fields, $ids)) {
            throw new \Exception('MANAGE,'.$this->class, EQ_ERROR_NOT_ALLOWED);
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

        // check `$params` consistency
        if(array_filter(array_keys($params), 'is_numeric')) {
            throw new \Exception('invalid_search_params', EQ_ERROR_INVALID_PARAM);
        }

        // retrieve current user id
        $user_id = $this->am->userId();

        if(isset($params['sort'])) {
            $params['sort'] = (array) $params['sort'];
        }
        $params = array_merge($defaults, $params);

        // 1) sanitize and validate given domain

        if(!empty($domain)) {
            $schema = $this->model->getSchema();
            if(!Domain::validate($domain, $schema)) {
                trigger_error("ORM::received invalid domain in Collection for entity {$this->class}:" . Domain::toString($domain), EQ_REPORT_ERROR);
                $domain = Domain::sanitize($domain, $schema);
            }
            else {
                $domain = Domain::normalize($domain, $schema);
            }
        }

        // 2) adapt domain according to access control type : either with ACL (permission) or Role (assignment)

        if($this->orm->hasObjectRoles($this->class)) {
            $roles = [];
            // find all roles for which a READ right is granted
            foreach($this->class::getRoles() as $role => $descriptor) {
                if(isset($descriptor['rights'])) {
                    if($descriptor['rights'] & EQ_R_READ) {
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
            if(!$this->ac->hasRight(EQ_R_READ, $this->class)) {
                // find the objects for which the user is explicitly granted for READ
                $permissions_ids = $this->orm->search('core\Permission', [ ['user_id', '=', $user_id], ['rights', '>=', EQ_R_READ], ['object_class', '=', $this->class] ]);
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
        $ids = $this->orm->search($this->class, $domain, $params['sort'], $params['start'], $params['limit'], $lang ?? $this->lang);

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

        // get full list of available fields
        $schema = $this->model->getSchema();
        $fields = array_keys($schema);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(EQ_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', EQ_ERROR_NOT_ALLOWED);
        }

        $canclone = $this->call('canclone');
        if(!empty($canclone)) {
            throw new \Exception(serialize($canclone), EQ_ERROR_NOT_ALLOWED);
        }

        // retrieve targeted identifiers
        $ids = $this->ids();

        $res = $this->orm->read($this->class, $ids, $fields, ($lang)?$lang:$this->lang);

        // #todo - this should be done in ObjectManager
        $uniques = $this->model->getUnique();

        // 3) duplicate each object of the collection
        foreach($res as $id => $obj) {
            $values = is_array($obj) ? $obj : $obj->toArray();

            // make unique fields complying with related constraints
            foreach($uniques as $unique) {
                $domain = [];
                foreach($unique as $field) {
                    if(!isset($values[$field])) {
                        continue 2;
                    }
                    $domain[] = [$field, '=',  $values[$field]];
                }
                $duplicate_ids = $this->orm->search($this->class, $domain);

                if(count($duplicate_ids)) {
                    foreach($unique as $field) {
                        // #todo - when renaming, we should favor name field if defined
                        if(in_array($schema[$field]['type'], ['string', 'text'])) {
                            $values[$field] = 'copy - '.$values[$field];
                        }
                        else {
                            // #todo - this does not guarantee that there will be no duplicate
                            // remove latest field to prevent duplicate issue
                            unset($values[$field]);
                        }
                        break;
                    }
                }
            }

            if(isset($schema['name']['type']) && $schema['name']['type'] == 'string') {
                $values['name'] = $values['name'].' - copy';
            }

            // set current user as creator and modifier
            $values['creator'] = $user_id;
            $values['modifier'] = $user_id;

            // validate : check unique keys
            $this->validate($values, [], true);

            $cancreate = $this->call('cancreate', $values);
            if(!empty($cancreate)) {
                throw new \Exception(serialize($cancreate), EQ_ERROR_NOT_ALLOWED);
            }

            // create the clone
            $new_id = $this->orm->clone($this->class, $id, $values, $lang);
            if($new_id <= 0) {
                // error at creation (possibly duplicate_index)
                throw new \Exception($this->class.'::'.implode(',', $fields), $new_id);
            }

            // log action (if enabled)
            $this->logger->log($user_id, 'create', $this->class, $new_id);
            // store new object in current collection
            // #todo - this should be an object
            $this->objects[$new_id] = ['id' => $new_id];
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

        $user_id = $this->am->userId();

        // 1) sanitize and retrieve necessary values
        if($values === null) {
            $values = [];
        }

        array_walk($values, function ($_, $key) {
            if(!is_string($key) || $key === '') {
                throw new \Exception('invalid_creation_map', EQ_ERROR_INVALID_PARAM);
            }
        });

        // drop invalid fields
        $values = $this->sanitizeFields($values, 'create');

        // retrieve targeted fields names
        $fields = array_keys($values);

        // 2) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(EQ_R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', EQ_ERROR_NOT_ALLOWED);
        }

        // if state is forced to draft, do not check required fields (to allow creation of empty/draft objects)
        $is_draft = (isset($values['state']) && $values['state'] == 'draft');

        // set current user as creator and modifier
        $values['creator'] = $user_id;
        $values['modifier'] = $user_id;

        // 3) validate : check required fields accordingly
        // #memo - we must check unique constraints at creation, but allow unique fields left to null (not set yet) in order to be able to create several draft objects
        $this->validate($values, [], true, !$is_draft);

        $cancreate = $this->call('cancreate', $values);
        if(!empty($cancreate)) {
            throw new \Exception(serialize($cancreate), EQ_ERROR_NOT_ALLOWED);
        }

        // 4) create the new object
        $id = $this->orm->create($this->class, $values, ($lang) ? $lang : $this->lang);
        if($id <= 0) {
            throw new \Exception('creation_failed', EQ_ERROR_UNKNOWN);
        }

        // log action (if enabled)
        $this->logger->log($user_id, 'create', $this->class, $id, $values);

        // put new object in current collection
        $this->id($id)->read(['id']);

        return $this;
    }

    public function call($method_name, $values=[]) {
        $result = [];

        if(!method_exists($this->class, $method_name)) {
            return $result;
        }

        $method = new \ReflectionMethod($this->class, $method_name);
        // make accessible, whatever the visibility
        $method->setAccessible(true);
        $methodParams = $method->getParameters();

        $args = [];
        foreach($methodParams as $methodParam) {
            $param = $methodParam->getName();
            if(in_array($param, ['om', 'orm'])) {
                $args[] = $this->orm;
            }
            elseif(in_array($param, ['ids', 'oids'])) {
                $args[] = $this->ids();
            }
            elseif($param == 'values') {
                $args[] = $values;
            }
            elseif($param == 'lang') {
                $args[] = $this->lang;
            }
            elseif($param == 'self') {
                $args[] = $this;
            }
            elseif($param == 'access') {
                $args[] = $this->ac;
            }
            elseif($param == 'auth') {
                $args[] = $this->am;
            }
            elseif($param == 'adapt') {
                $args[] = $this->dap;
            }
            elseif($param == 'log') {
                $args[] = $this->logger;
            }
            elseif(in_array($param, [
                    'report',
                    'context',
                    'validate',
                    'route',
                    'cron',
                    'dispatch',
                    'db'])) {
                $args[] = $this->container->get($param);
            }
        }

        try {
            if(!$method->isStatic()) {
                throw new \Exception('non_static_method', EQ_ERROR_INVALID_PARAM);
            }
            $res = $method->invokeArgs(null, $args);
            if($res !== null) {
                $result = $res;
            }
        }
        catch(\Exception $e) {
            trigger_error("ORM::unexpected error when calling {$method_name} on {$this->class}: ".$e->getMessage(), EQ_REPORT_ERROR);
            throw $e;
        }

        return $result;
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

            if(!is_array($fields)) {
                trigger_error("ORM::received non-array `\$fields` when reading `{$this->class}`: casting to array", EQ_REPORT_WARNING);
                // force argument into an array (single field name is accepted; if empty array is given: load all fields)
                $fields = (array) $fields;
            }

            $schema = $this->model->getSchema();

            // 1) drop invalid fields and build sub-request array
            $allowed_fields = array_keys($schema);

            // build a list of direct field to load (i.e. "object attributes")
            $requested_fields = [];

            // 'id'         : we might access an object directly by giving its `id`.
            // 'state'      : the state of the object is provided for concurrency control (check that a draft object is not instantiated twice).
            // 'deleted'    : since some objects might have been soft-deleted, we need to load the `deleted` state in order to know if object needs to be in the result set or not.
            // 'modified'   : the last update timestamp is always provided. At update, if `modified` is provided, it is compared to the current timestamp to detect concurrent changes.
            // #memo - we cannot add 'name' by default since it might be (an alias to) a computed field (that could lead to a circular dependency)
            $mandatory_fields = ['id', /*'name',*/ 'state', 'deleted', 'modified'];

            foreach($fields as $key => $val ) {
                // handle array notation
                $field = (!is_numeric($key)) ? $key : $val;
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
            $ids = $this->ids();

            // 2) check that current user has enough privilege to perform READ operation
            if(!$this->ac->isAllowed(EQ_R_READ, $this->class, $requested_fields, $ids)) {
                throw new \Exception($user_id.';READ;'.$this->class.';'.implode(',',$ids), EQ_ERROR_NOT_ALLOWED);
            }

            // #memo - this is necessary when non-ACL policies are set, otherwise it is redundant with access::isAllowed(R_READ)
            $canread = $this->call('canread', $requested_fields);
            if(!empty($canread)) {
                throw new \Exception(serialize($canread), EQ_ERROR_NOT_ALLOWED);
            }

            // 3) read values
            $res = $this->orm->read($this->class, $ids, $requested_fields, $lang ?? $this->lang);
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

            // withdraw invalid objects from collection (id not matching an actual object)
            foreach(array_diff(array_keys($this->objects), array_keys($res)) as $invalid_id) {
                unset($this->objects[$invalid_id]);
            }

            // merge retrieved values with current collection (overwrite if already present)
            foreach($res as $id => $object) {
                foreach($object as $field => $value) {
                    $this->objects[$id][$field] = $value;
                }
            }

            // 5) recursively load sub-fields, if any
            foreach($fields as $field => $subfields) {
                // discard direct fields
                if(is_numeric($field)) {
                    continue;
                }
                // accept both array or single value as subfields
                $subfields = (array) $subfields;
                // #memo - using Field object guarantees support for `alias` and `computed` fields
                $targetField = $this->model->getField($field);
                if(!$targetField) {
                    continue;
                }
                $target = $targetField->getDescriptor();

                if(!in_array($target['result_type'], ['one2many', 'many2one', 'many2many'])) {
                    continue;
                }

                $subdomain = [];
                $subparams = [];

                if(isset($target['order'])) {
                    $subparams['sort'] = [$target['order'] => (isset($target['sort'])) ? $target['sort'] : 'asc'];
                }

                foreach($subfields as $key => $val) {
                    if($key === '@domain') {
                        $subdomain = (array) $val;
                        unset($subfields[$key]);
                        continue;
                    }
                    if(in_array($key, ['@sort', '@limit', '@start'], true)) {
                        $subparams[substr($key, 1)] = $val;
                        unset($subfields[$key]);
                        continue;
                    }
                }

                if(!count($subfields)) {
                    if($target['result_type'] !== 'many2one') {
                        foreach($this->objects as $id => $object) {
                            $searchDomain = new Domain($subdomain);
                            $searchDomain->merge(new Domain([ 'id', 'in', $this->objects[$id][$field] ?? [] ]));
                            $this->objects[$id][$field] = $this->orm->search($target['foreign_object'], $searchDomain->toArray());
                        }
                    }
                    continue;
                }

                // recursively load and assign retrieved values to the objects they relate to
                foreach($this->objects as $id => $object) {
                    $searchDomain = new Domain($subdomain);
                    $searchDomain->merge(new Domain([ 'id', 'in', $this->objects[$id][$field] ?? [] ]));

                    /** @var Collection */
                    $children = $target['foreign_object']::search($searchDomain->toArray(), $subparams)->read($subfields, $lang ?? $this->lang);

                    if($target['result_type'] === 'many2one') {
                        // many2one might be either null or a Model object (which might contain sub-collections)
                        $this->objects[$id][$field] = $children->first();
                    }
                    else {
                        // one2many & many2many are Collection objects
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
        if(count($this->objects) <= 0)  {
            return $this;
        }

        $user_id = $this->am->userId();

        // 1) sanitize and retrieve necessary values
        if($values === null) {
            $values = [];
        }

        array_walk($values, function ($_, $key) {
            if(!is_string($key) || $key === '') {
                throw new \Exception('invalid_update_map', EQ_ERROR_INVALID_PARAM);
            }
        });

        $is_draft = (isset($values['state']) && $values['state'] == 'draft');

        // silently drop invalid fields
        $values = $this->sanitizeFields($values, $is_draft ? 'create' : 'update');

        // retrieve targeted fields names
        $fields = array_keys($values);

        // retrieve targeted identifiers
        $ids = $this->ids();

        // by convention, update operation sets modifier as current user
        $values['modifier'] = $user_id;

        // unless explicitly assigned to another value than 'draft', update operation sets state to 'instance'
        // #memo - moved to ObjectManager::update()
        /*
        if(!isset($values['state']) || $values['state'] == 'draft') {
            $values['state'] = 'instance';
        }
        */

        // 2) check that current user has enough privilege to perform the operation
        if(!$this->ac->isAllowed(EQ_R_UPDATE, $this->class, $fields, $ids)) {
            throw new \Exception($user_id.';UPDATE;'.$this->class.';['.implode(',', $fields).'];['.implode(',', $ids).']', EQ_ERROR_NOT_ALLOWED);
        }

        // 3) validate : check unique keys and required fields
        // if object is about to become an instance (still draft), check required fields (otherwise, partial update is allowed)
        $this->validate($values, $ids, true, $is_draft);

        // #memo - moved back from ObjectManager::update() (see above)
        // by convention, `update()` forces a 'draft' to an 'instance'
        if($is_draft) {
            $values['state'] = 'instance';
            // #todo - here we must check that all required fields (scalar types) have been provided, either at create() or during update()
            // this should probably rather be done in ORM + making a distinction between partial validation & full validation
        }

        // check if fields (other than special columns) can be updated
        $can_update = $this->call('canupdate', array_diff_key($values, Model::getSpecialColumns()));
        if(!empty($can_update)) {
            throw new \Exception(serialize($can_update), QN_ERROR_NOT_ALLOWED);
        }

        // 4) update objects
        $res = $this->orm->update($this->class, $ids, $values, ($lang)?$lang:$this->lang);
        if($res <= 0) {
            trigger_error("ORM::unexpected error when updating {$this->class} objects:".$this->orm->getLastError(), EQ_REPORT_INFO);
            throw new \Exception('update_failed', $res);
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
            $ids = $this->ids();

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(EQ_R_DELETE, $this->class, [], $ids)) {
                throw new \Exception($user_id.';DELETE,'.$this->class.'['.implode(',', $ids).']', EQ_ERROR_NOT_ALLOWED);
            }

            $candelete = $this->call('candelete');
            if(!empty($candelete)) {
                throw new \Exception(serialize($candelete), EQ_ERROR_NOT_ALLOWED);
            }

            // 3) delete objects
            $res = $this->orm->delete($this->class, $ids, $permanent);
            if($res <= 0) {
                trigger_error("ORM::unexpected error when updating {$this->class} objects:".$this->orm->getLastError(), EQ_REPORT_INFO);
                throw new \Exception('deletion_failed', $res);
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
        trigger_error("ORM::performing transition '{$transition}' on '{$this->class}'", EQ_REPORT_DEBUG);
        // retrieve targeted identifiers
        $ids = $this->ids();
        if(count($ids)) {
            // attempt to perform transition
            $can_transition = $this->orm->transition($this->class, $ids, $transition);
            if($can_transition < 0) {
                trigger_error("ORM::unexpected error for transition '{$transition}' on '{$this->class}' objects:".$this->orm->getLastError(), EQ_REPORT_WARNING);
                throw new \Exception('transition_failed', $can_transition);
            }
            elseif(count($can_transition)) {
                throw new \Exception(serialize($can_transition), EQ_ERROR_INVALID_PARAM);
            }
        }
        return $this;
    }


    /**
     * Attempts to perform a specific action to a series of objects.
     * If no workflow is defined, the call is ignored (no action is taken).
     * If there is no match or if there are some conditions on the transition that are not met, it returns an error code.
     *
     * @param   string      $action       Name of the requested action.
     * @param   array       $values       Associative array mapping arbitrary keys with their values, that can be used to relay arguments.
     * @return  Collection  returns the current instance (allowing calls chaining)
     */
    public function do($action, $values=[]) {
        trigger_error("ORM::performing action '{$action}' on '{$this->class}'", EQ_REPORT_DEBUG);
        // retrieve targeted identifiers
        $ids = $this->ids();
        if(count($ids)) {
            // check if action can be performed
            $res = $this->ac->canPerform($action, $this->class, $ids);

            if(count($res)) {
                throw new \Exception(serialize($res), EQ_ERROR_INVALID_PARAM);
            }

            // retrieve and perform action
            $actions = $this->class::getActions();
            if(isset($actions[$action]) && isset($actions[$action]['function'])) {
                if(count($ids)) {
                    $this->call($actions[$action]['function'], $values);
                }
            }
            else {
                trigger_error("ORM::unknown or missing function for action `$action` requested on `{$this->class}`", EQ_REPORT_WARNING);
            }
        }
        return $this;
    }


    /**
     * Asserts that the collection complies with one or several policies.
     * Throws an exception if any policy is violated.
     *
     * @param string|array $policies
     * @return Collection
     * @throws \Exception
     */
    public function assert($policies) {
        trigger_error("ORM::performing assert on '{$this->class}' for policies " . implode(',', (array) $policies), EQ_REPORT_DEBUG);

        $ids = $this->ids();

        // nothing to check
        if(!count($ids)) {
            return $this;
        }

        // normalize to array
        $policies = (array) $policies;

        // retrieve current user id
        $user_id = $this->am->userId();

        foreach($policies as $policy) {
            // call AccessController check
            $res = $this->ac->isCompliant($policy, $this->class, $ids, $user_id);

            // throw Exception upon violation
            if(count($res)) {
                throw new \Exception(serialize($res), EQ_ERROR_INVALID_PARAM);
            }
        }

        return $this;
    }


    /**
     * Apply a callback to each object of the collection.
     * The callback receives the object id and the Model instance.
     * This method does not alter the collection and allows chaining.
     *
     * @param callable $callback function(int $id, Model $object): void
     * @return Collection
     */
    public function each(callable $callback): Collection {
        foreach($this->objects as $id => $object) {
            $callback($id, $object);
        }
        return $this;
    }

    /*
      Following methods are defined here to allow equal\orm\Model children classes having arbitrary parameters.
      These methods are defined to prevent PHP strict errors & aot warnings, and are called through the
      Collection::call() method which, in turn, invokes the class the Collection relates to.
      If the method is not defined in the child class, a new Collection is instantiated and the call is applied to it (hence their definition here).
    */

    /**
     * Check wether an object can be read by current user.
     * This method can be overridden to define a custom set of tests (based on roles and/or policies).
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return array            Returns an associative array mapping ids and fields with their error messages. An empty array means that object has been successfully processed and can be read.
     */
    public static function canread(...$params) {
        return [];
    }

    /**
     * Check wether an object can be created.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be created.
     */
    public static function cancreate(...$params) {
        return [];
    }

    /**
     * Check wether an object can be updated.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be updated.
     */
    public static function canupdate(...$params) {
        return [];
    }

    /**
     * Check wether an object can be cloned.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return array            Returns an associative array mapping ids with their error messages. An empty array means that object has been successfully processed and can be updated.
     */
    public static function canclone(...$params) {
        return [];
    }

    /**
     * Check wether an object can be deleted.
     * This method can be overridden to define a custom set of tests.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return array            Returns an associative array mapping ids with their error messages. An empty array means that object has been successfully processed and can be deleted.
     */
    public static function candelete(...$params) {
        return [];
    }

    /**
     * Hook invoked after object creation for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function oncreate(...$params) {
    }

    /**
     * Hook invoked before object update for performing object-specific additional operations.
     * Current values of the object can still be read for comparing with new values.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onbeforeupdate(...$params) {
    }

    /**
     * Alias of `onbeforeupdate()`.
     */
    public static function onupdate(...$params) {
    }

    /**
     * Hook invoked after object update for performing object-specific additional operations.
     * Previous values of the object are no longer available.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onafterupdate(...$params) {
    }

    /**
     * Hook invoked after object cloning for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onclone(...$params) {
    }

    /**
     * Hook invoked before object deletion for performing object-specific additional operations.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onbeforedelete(...$params) {
    }

    /**
     * Alias of `onbeforedelete()`.
     */
    public static function ondelete(...$params) {
    }

    /**
     * Hook invoked after object deletion for performing object-specific additional operations.
     * The deleted objects are no longer available.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onafterdelete(...$params) {
    }

    /**
     * Hook invoked by UI for single object values change.
     * This method does not imply an actual update of the model, but a potential one (not made yet) and is intended for front-end only.
     *
     * Accepts variable list of arguments, based on their names (@see \equal\orm\Model class for list of available).
     * @return void
     */
    public static function onchange(...$params) {
        return [];
    }

}
