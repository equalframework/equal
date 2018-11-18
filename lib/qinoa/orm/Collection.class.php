<?php
namespace qinoa\orm;



class Collection implements \Iterator {

    /* ObjectManager */
    private $om;
    
    /* AccessController */
    private $ac;

    /* AuthenticationManager */
    private $am;

    /* DataAdapter */
    private $da;
    
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
    
    public function __construct($class, $objectManager, $accessController, $authenticationManager, $dataAdapter) {
        // init objects map
        $this->objects = [];
        $this->cursor = 0;
        // assign private members 
        $this->class = $class;        
        $this->om = $objectManager;
        $this->ac = $accessController;
        $this->am = $authenticationManager;
        $this->da = $dataAdapter;        
        // check mandatory services
        if(!$this->om || !$this->ac) {
            throw new \Exception(__CLASS__, QN_ERROR_UNKNOWN);
        }                
        // retrieve static instance for target class
        $this->instance = $this->om->getStatic($class);
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
        if($offset && $offset < count($this->objects)) {
            $this->objects = array_slice($this->objects, $offset, null, true);
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
        return $res[0];
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
            $ids = (array) $args[0];
            // init keys of 'objects' member (for now, contain only an empty array)
            $this->objects = array_fill_keys(array_unique($ids), []);            
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
                $result = array_intersect($field, $allowed_fields);
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
                    $this->objects[$id][$field] = $this->da->adapt($value, $schema[$field]['type'], $to, 'php');
                }
            }            
        }
        return $this;
    }
    
    /**
     *
     * @param $fields   array   associative array holding values and their related fields as keys 
     *
     */
    private function validate(array $fields) {
        $validation = $this->om->validate($this->class, $fields);
        if($validation < 0 || count($validation)) {
            throw new \Exception($this->class.'::'.implode(',', array_keys($fields)), QN_ERROR_INVALID_PARAM);
        }
    }
    
    public function search(array $domain=[], array $params=[], $lang=DEFAULT_LANG) {
        $defaults = [
            'sort'  => ['id' => 'asc'],
            'start' => 0,
            'limit' => $this->limit,
            'lang'  => $lang
        ];

        if(isset($params['sort'])) $params['sort'] = (array) $params['sort'];
        $params = array_merge($defaults, $params);
        // sanitize and validate given domain
        $domain = Domain::normalize($domain);
        if(!Domain::validate($domain, $this->instance->getSchema())) {
            throw new \Exception(Domain::toString($domain), QN_ERROR_INVALID_PARAM);            
        }
        // perform search
        $ids = $this->om->search($this->class, $domain, $params['sort'], $params['start'], $params['limit'], $params['lang']);
        // $ids is an error code
        if($ids < 0) {           
            throw new \Exception(Domain::toString($domain), $ids);
        }
        if(count($ids)) {
            // init keys of 'objects' member (for now, contain only an empty array)
            $this->objects = array_fill_keys($ids, []);
        }
        else {
            // reset objects map if not empty
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
        // 1) silently drop invalid fields
        $values = $this->filter($values);
        // 2) validate
        $this->validate($values);        
        // retrieve targeted fields names
        $fields = array_keys($values);        
        // 3) check that current user has enough privilege to perform CREATE operation
        if(!$this->ac->isAllowed(R_CREATE, $this->class, $fields)) {
            throw new \Exception('CREATE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
        }
        // set current user as creator
        $values = array_merge($values, ['creator' => $this->am->userId()]);
        // 4) create the new object
        $oid = $this->om->create($this->class, $values, $lang);        
        if($oid <= 0) {
            throw new \Exception($this->class.'::'.implode(',', $fields), $oid);
        }
        if(!in_array('id', $fields)) {
            $values['id'] = $oid;
        }
        // store new object in current collection
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
            $requested_fields = [];
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
                
                // load all simple fields
                $requested_fields = array_keys(array_filter($schema, function($field) { return in_array($field['type'], ObjectManager::$simple_types);}));
                // load all fields
                $requested_fields = array_keys($schema);            
            */

            // retrieve targeted identifiers
            $ids = array_keys($this->objects);
            
            // 2) check that current user has enough privilege to perform READ operation
            if(!$this->ac->isAllowed(R_READ, $this->class, $fields, $ids)) {
                throw new \Exception('READ,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
            }
            
            // 3) read values
            $res = $this->om->read($this->class, $ids, $requested_fields, $lang);
            // $res is an error code, something prevented to fetch requested fields
            if($res < 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }
            
            $this->objects = $res;
            
            // 4) load sub-fields (recusion), if any
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
            // 1) silently drop invalid fields
            $values = $this->filter($values);
            // 2) validate
            $this->validate($values);
            // retrieve targeted identifiers
            $ids = array_keys($this->objects);
            // retrieve targeted fields names
            $fields = array_keys($values);
            // 3) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(R_WRITE, $this->class, $ids, $fields, $ids)) {
                throw new \Exception('WRITE,'.$this->class.',['.implode(',', $fields).']', QN_ERROR_NOT_ALLOWED);
            }
            // set current user as modifier
            $values = array_merge($values, ['modifier' => $this->am->userId()]);
            // 4) write
            $res = $this->om->write($this->class, $ids, $values, $lang);
            if($res <= 0) {
                throw new \Exception($this->class.'::'.implode(',', $fields), $res);
            }            
            foreach($ids as $oid) {
                // store updated objects in current collection
                $this->objects[$oid] = array_merge(['id' => $oid], $values);
            }
        }
        return $this;
    }
    
    public function delete($permanent=false) {
        if(count($this->objects)) {                
            // retrieve targeted identifiers
            $ids = array_keys($this->objects);
            // 1) check that current user has enough privilege to perform WRITE operation
            if(!$this->ac->isAllowed(R_DELETE, $this->class, [], $ids)) {
                throw new \Exception('DELETE,'.$this->class.',['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
            }
            // 2) delete
            $res = $this->om->remove($this->class, $ids, $permanent);
            if($res <= 0) {
                throw new \Exception($this->class.'::'.implode(',', $ids), $res);
            }
            // update current collection (remove all objects)
            $this->objects = [];
        }
        return $this;
    }
}