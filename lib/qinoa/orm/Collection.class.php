<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\orm;



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
    
    private $instance;
  
    /* map associating objects with their values with identifiers as keys */  
    private $objects;
    
    
    private $cursor;
    
    /* pagination limit: size of a page when searching or loading sub-objects (default is 0, i.e. no limit)*/
    private $limit;

    /**
     *
     * This method makes valid 'is_callable' tests on any static method name (i.e. 'entry' calls, e.g. ::search, ::ids, ::id, ...)
     */
    public static function __callStatic($name, $arguments) {}       
    
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
    
    public function rewind() {
        reset($this->objects);
    }
  
    public function current() {
        return current($this->objects);
    }
  
    public function key() {
        return key($this->objects);
    }
  
    public function next() {
        return next($this->objects);
    }
  
    public function valid() {
        $key = key($this->objects);
        $res = ($key !== NULL && $key !== FALSE);        
        return $res;
    }

    public function from($offset) {
        if($offset < count($this->objects)) {
            $this->objects = array_slice($this->objects, $offset, null, true);
        }
        else {
            $this->objects = [];
        }
        return $this;
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
        else {
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
    public function shift($offset) {
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
     *  Return the first object of the collection
     *
     */    
    public function first() {
        $this->objects = array_slice($this->objects, 0, 1, true);
        $res = $this->toArray();
        return count($res)?$res[0]:null;
        // return reset($this->objects);
    }
    
    /**
     *  Return the last object of the collection
     *
     */    
    public function last() {
        $this->objects = array_slice($this->objects, -1, 1, true);
        $res = $this->toArray();
        return $res[0];        
        //  return end($this->objects);
    }
    
    /**
     * Provide the whole collection as a map of [id => value, ...] (default) or as an array
     *
     * @param   $to_array   boolean    Flag to ask conversion to single array (instead of a map)
     * @param   $to         string     Expected format of the returned values (txt, php, sql, orm)
     *
     * @return  array       Associative array mapping objects identifiers with their related maps of fields/values
     *
     */    
    public function get($to_array=false) {
        // retrieve current objects map
        $result = [];
        $schema = $this->instance->getSchema();
        foreach($this->objects as $id => $object) {
            foreach($object as $field => $value) {
                if($value instanceof Collection) {
                    $list = $value->get($to_array);
                    if($schema[$field]['type'] == 'many2one' && count($list)) {
                        $result[$id][$field] = current($list);
                    }
                    else {
                        $result[$id][$field] = $list;
                    }
                }
                else {
                    $result[$id][$field] = $value;
                }
            }
        }        
        // if user requested an array of objects instead of a map
        if($to_array) {
            // create an array out of the values, ignoring keys
            $result = array_values($result);
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
        return $this->ids([$id]);
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
            // filter resulting ids based on current user permissions 
            // (filling the list with non-readable objects would raise a NOT_ALLOWED exception)            
/*            
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
     * Filters a map of fields-values entries or an array of fields names and disguard those unknonwn to the current class
     *
     * @param $fields   array   a set of fields or a map of fields-values
     * @return array    filtered array containing known fields names only
     */
    private function filter(array $fields) {
        $result = [];
        if(count($fields)) {
            // retreve valid fields
            $allowed_fields = $this->instance->getFields();
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
                    $this->objects[$id][$field] = $this->adapter->adapt($value, $schema[$field]['type'], $to, 'php');
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
    private function validate(array $fields, $check_unique=false) {
        $validation = $this->orm->validate($this->class, $fields, $check_unique);
        if($validation < 0 || count($validation)) {
            foreach($validation as $error_code => $error_descr) {
// todo : harmonize error codes                
                throw new \Exception(implode(',', array_keys($error_descr)), $error_code);
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

        if(isset($params['sort'])) $params['sort'] = (array) $params['sort'];
        $params = array_merge($defaults, $params);
        // 1) sanitize and validate given domain
        if(!empty($domain)) {
            $domain = Domain::normalize($domain);
            if(!Domain::validate($domain, $this->instance->getSchema())) {
                throw new \Exception(Domain::toString($domain), QN_ERROR_INVALID_PARAM);            
            }    
        }

        // 2) check that current user has enough privilege to perform READ operation on given class
// toto : extract fields names from domain, and make sure user has R_READ access on those        
        if(!$this->ac->isAllowed(QN_R_READ, $this->class)) {
            // user has always READ access to its own objects
			if(!$this->ac->isAllowed(QN_R_CREATE, $this->class)) {		
                // no READ nor CREATE permission: deny request
				throw new \Exception('READ,'.$this->class, QN_ERROR_NOT_ALLOWED);
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
            // init keys of 'objects' member (so far, it is an empty array)
            // filter results using access controller... (reduce resulting ids based on access rights)            
            $ids = $this->ac->filter(QN_R_READ, $this->class, [], $ids);
            $this->ids($ids);
        }
        else {
            // reset objects map (empty result)
            $this->objects = [];
        }
        return $this;
    }
    
    /**
     * Creates a new instance
     *
     * @return  object  current Collection
     * @example $newObject = MyClass::create();
     *
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

        // 3) validate and check unique keys
        $this->validate($values, true);
        // set current user as creator
        $values = array_merge($values, ['creator' => $user_id]);

        // 4) create the new object
        $oid = $this->orm->create($this->class, $values, $lang);        
        if($oid <= 0) {
            throw new \Exception($this->class.'::'.implode(',', $fields), $oid);
        }
        // log action (if enabled)
        $this->logger->log($user_id, 'create', $this->class, $oid);
        
        // store new object in current collection
        if(!in_array('id', $fields)) {
            $values['id'] = $oid;
        }
        $this->objects[$oid] = $values;

        return $this;
    }
    
    public function read($fields, $lang=DEFAULT_LANG) {

        if(count($this->objects)) {
            // force argument into an array (single field name is accepted, empty array is accepted: load all fields)
            $fields = (array) $fields;            

            $schema = $this->instance->getSchema();
            
            // 1) drop invalid fields and build sub-request array
            $allowed_fields = array_keys($schema);
            // build a list of direct field to load (i.e. "object attributes") 
            // we might access an object directly by giving its ID, as some objects might have been soft-deleted we need to load the `deleted` status in order to know if object needs to be in the result set
            $requested_fields = ['id', 'deleted'];
            
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
                    if(is_numeric($key) || !in_array($target['type'], ['one2many', 'many2one', 'many2many'])) {
                        unset($fields[$key]);
                    }
                }
            }            
            /*
                When $field argument is empty, nothing is loaded.
                Alternate strategies for empty $fields situation:
                
                * load all simple fields
                ```
                $requested_fields = array_keys(array_filter($schema, function($field) { return in_array($field['type'], ObjectManager::$simple_types);}));
                ```
                * load all fields
                ```
                $requested_fields = array_keys($schema);            
                ```
            */
            // retrieve targeted identifiers (remove null entries)
            $ids = array_filter(array_keys($this->objects), function($a) { return ($a > 0); });

			// 2) check that current user has enough privilege to perform READ operation
			if(!$this->ac->isAllowed(QN_R_READ, $this->class, $fields, $ids)) {
                throw new \Exception('READ,'.$this->class.';'.implode(',',$ids), QN_ERROR_NOT_ALLOWED);
                
                /*
				if(!$this->ac->isAllowed(QN_R_CREATE, $this->class)) {	
				    throw new \Exception('READ,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
				}
				// user might have created some objects: limit search to those, if any
				$domain = Domain::conditionAdd($domain, ['creator', '=', $this->am->userId()]);            
                */
            }
            
            // 3) read values
            $res = $this->orm->read($this->class, $ids, $requested_fields, $lang);
            // $res is an error code, something prevented to fetch requested fields
            if($res < 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }            

            // 4) remove any deleted items (to handle ::ids()->read()), if `deleted` field was not explicitely requested
            if(!in_array('deleted', $fields)) {            
                foreach($res as $oid => $odata) {
                    if($odata['deleted']) {
                        unset($res[$oid]);
                    }
                    else {
                        unset($res[$oid]['deleted']);
                    }
                }
            }

            // 5) load sub-fields (recusion), if any
            $this->objects = $res;            
            foreach($fields as $field => $subfields) {
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
            $ids = array_keys($this->objects);
            // retrieve targeted fields names
            $fields = array_keys($values);
            
            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_WRITE, $this->class, $fields, $ids)) {
                throw new \Exception($user_id.';UPDATE;'.$this->class.';['.implode(',', $fields).'];['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }
            
            // 3) validate
            $this->validate($values);            
                        
            // 4) write
            // set current user as modifier
            $values = array_merge($values, ['modifier' => $user_id]);            
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

            // 2) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(QN_R_DELETE, $this->class, [], $ids)) {
                throw new \Exception($user_id.';DELETE,'.$this->class.',['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }

            // 3) delete
            $res = $this->orm->remove($this->class, $ids, $permanent);
            if($res <= 0) {
                throw new \Exception($this->class.'::'.implode(',', $ids), $res);
            }
            else {
                $ids = $res;
            }
            
            foreach($ids as $oid) {            
                // log action (if enabled)
                $this->logger->log($user_id, 'delete', $this->class, $oid);
            }

            // 3) update current collection (remove all objects)
            $this->objects = [];
        }
        return $this;
    }
}