<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\organic\Service;
use equal\db\DBConnector;
use equal\data\DataValidator;

use \Exception as Exception;



class ObjectManager extends Service {

    /**
     * Buffer for storing objects values as they are loaded.
     * Structure is defined this way: `$cache[$class][$oid][$lang][$field]`
     * Only methods load() and update() update its values.
     * @var array
     */
    private $cache;

    /**
     * Array for remembering which methods have been invoked during an 'update' cycle
     * @var array
     */
    private $object_methods;

    /**
     * Array for keeping track of identifiers matching actual objects
     * Structure is defined this way: `$identifiers[$object_class][$object_id] = true;`
     * @var array
     */
    private $identifiers;

    /**
     * Array holding static instances of models (i.e. one object of each class having fields set to default values)
     * @var array
     */
    private $models;

    /**
     * Array holding static fields instances
     * @var array
     */
    private $fields;

    /**
     * Array holding names of defined packages (folders under /package directory)
     * @var array
     */
    private $packages;

    /**
     * String holding the serialized value of the latest error that occurred (to be used in addition with error codes)
     * @var string
     */
    private $last_error;

    /**
     * DB connector instance
     * @var DBConnector
     */
    private $db;

    public static $virtual_types = array('alias');
    public static $simple_types  = array('boolean', 'integer', 'float', 'string', 'text', 'date', 'time', 'datetime', 'file', 'binary', 'many2one');
    public static $complex_types = array('one2many', 'many2many', 'computed');

    /**
     * - dev-2.0 - 'dependencies' has been deprecated in favor to 'dependents'
     */
    public static $valid_attributes = [
        'alias'         => array('description', 'help', 'type', 'visible', 'default', 'usage', 'alias', 'required', 'deprecated'),
        'boolean'       => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate'),
        'integer'       => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'selection', 'unique'),
        'float'         => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'selection', 'precision'),
        'string'        => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'multilang', 'selection', 'unique'),
        'text'          => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'multilang'),
        'date'          => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate'),
        'time'          => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate'),
        'datetime'      => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate'),
        'file'          => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'multilang'),
        'binary'        => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'usage', 'required', 'deprecated', 'onupdate', 'multilang'),
        'many2one'      => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'required', 'deprecated', 'foreign_object', 'domain', 'onupdate', 'ondelete', 'multilang'),
        'one2many'      => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'deprecated', 'foreign_object', 'foreign_field', 'domain', 'onupdate', 'ondetach', 'order', 'sort'),
        'many2many'     => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'deprecated', 'foreign_object', 'foreign_field', 'rel_table', 'rel_local_key', 'rel_foreign_key', 'domain', 'onupdate'),
        'computed'      => array('description', 'help', 'type', 'visible', 'default', 'dependencies', 'dependents', 'readonly', 'deprecated', 'result_type', 'usage', 'function', 'relation', 'onupdate', 'onrevert', 'store', 'instant', 'multilang', 'selection', 'foreign_object')
    ];

    public static $mandatory_attributes = [
        'alias'         => array('type', 'alias'),
        'boolean'       => array('type'),
        'integer'       => array('type'),
        'float'         => array('type'),
        'string'        => array('type'),
        'text'          => array('type'),
        'date'          => array('type'),
        'time'          => array('type'),
        'datetime'      => array('type'),
        'binary'        => array('type'),
        'file'          => array('type'),
        'many2one'      => array('type', 'foreign_object'),
        'one2many'      => array('type', 'foreign_object', 'foreign_field'),
        'many2many'     => array('type', 'foreign_object', 'foreign_field', 'rel_table', 'rel_local_key', 'rel_foreign_key'),
        'computed'      => array('type', 'result_type', ['function', 'relation'])
    ];

    public static $valid_operators = [
        'boolean'       => array('=', '<>', '<', '>', 'in', 'is', 'is not'),
        'integer'       => array('in', 'not in', '=', '<>', '<', '>', '<=', '>=', 'is', 'is not'),
        'float'         => array('in', 'not in', '=', '<>', '<', '>', '<=', '>=', 'is', 'is not'),
        'string'        => array('like', 'ilike', 'in', 'not in', '=', '<>', 'is', 'is not'),
        'text'          => array('is', 'is not'),
        'date'          => array('=', '<>', '<', '>', '<=', '>=', 'like', 'is', 'is not'),
        'time'          => array('=', '<>', '<', '>', '<=', '>=', 'is', 'is not'),
        'datetime'      => array('=', '<>', '<', '>', '<=', '>=', 'is', 'is not'),
        /* #deprecated - use binary */
        'file'          => array('like', 'ilike', '=', 'is', 'is not'),
        'binary'        => array('like', 'ilike', '=', 'is', 'is not'),
        // #memo - for convenience, 'contains' is allowed for many2one field (in such case 'contains' operator means 'list contains *at least one* of the following ids')
        'many2one'      => array('is', 'is not', 'in', 'not in', '=', '<>', '<', '>', '<=', '>=', 'contains'),
        'one2many'      => array('contains'),
        'many2many'     => array('contains'),
    ];

    /**
     * Mapping of ORM types with their sized notations.
     *
     * ORM types: boolean, integer, float, string
     *
     * Size is expressed this way: type[(length|precision[,scale])]
     * and focus on the storage, in bytes, to allocate (rather than what value range can actually be stored).
     *
     * Each DBManipulator Service is in charge of converting ORM types matching its own native type.
     * Types that are ambiguous regarding their size (string, text, binary) or require additional info (decimal place for real numbers representations).
     * All ORM types have an equivalent in SQL.
     *
     * #todo - deprecate
     */
    public static $types_associations = [
        'integer'       => 'integer(4)',        // 4 bytes integer
        'float'         => 'float(10,4)',       // 10 digits float holding 4 decimal digits
        'string'        => 'string(255)',       // 255 bytes string
        'text'          => 'string(32000)',     // 32 kilobytes string
        'binary'        => 'binary(64000000)'   // 64MB binary value
    ];

    /**
     * @param DBConnector $db  Instance of the Service allowing connection to DBMS (connection might not be established yet).
     */
    protected function __construct(DBConnector $db) {
        $this->db = &$db;
        $this->packages = null;
        $this->cache = [];
        $this->models = [];
        $this->object_methods = [];
        $this->last_error = '';
    }

    public function __clone() {
        trigger_error("ORM::__clone: instance is being copied.", QN_REPORT_ERROR);
    }

    public function __destruct() {
        // close DB connection, if open
        if($this->db && $this->db->connected()) {
            $this->db->disconnect();
        }
    }

    public static function constants() {
        return ['DEFAULT_LANG', 'UPLOAD_MAX_FILE_SIZE', 'FILE_STORAGE_MODE', 'DRAFT_VALIDITY', 'ORM_EVENTS_FORCE_ONUPDATE_AT_CREATION'];
    }

    /**
     * #todo - deprecate : use the DBConnector instead
     * @deprecated
     */
    public function getDB() {
        return $this->db;
    }

    /**
     * Provide the db handler (DBConnector instance).
     * If the connection hasn't been established yet, tries to connect to DBMS.
     *
     * @return DBConnector
     */
    private function getDbHandler() {
        // open DB connection, if not connected yet
        if(!$this->db->connected()) {
            if($this->db->connect() === false) {
                // fatal error
                trigger_error("ORM::".'Unable to establish connection to database: check connection parameters '.
                        '(possibles reasons: non-supported DBMS, unknown database name, incorrect username or password, DB offline, ...)',
                        QN_REPORT_ERROR
                    );
                throw new Exception("connection_failed", QN_ERROR_INVALID_CONFIG);
            }
        }
        return $this->db;
    }

    public function __toString() {
        return "ObjectManager instance";
    }

    /**
     * Returns the list of installed packages, based on directories present in the /packages directory.
     *
     * @return string[]     Array of packages names (string).
     */
    public function getPackages() {
        if(!$this->packages) {
            $this->packages = [];
            $package_directory = QN_BASEDIR.'/packages';
            if(is_dir($package_directory) && ($list = scandir($package_directory))) {
                foreach($list as $node) {
                    if(is_dir($package_directory.'/'.$node)
                    && !in_array($node, array('.', '..'))
                    && !in_array($node, $this->packages)) {
                        $this->packages[] = $node;
                    }
                }
            }
        }
        return $this->packages;
    }

    /**
     * Returns a static instance for the specified object class (does not create a new object)
     *
     * @param   string       $class      The full name of the class with its namespace.
     * @param   array        $fields     Associative array mapping fields with default values (provided by create() method).
     * @throws  Exception
     * @return  Model        Returns a partial instance of the targeted class.
     */
    private function getStaticInstance($class, $fields=[]) {
        if(count($fields) || !isset($this->models[$class])) {
            // if class is unknown, load the file containing the class declaration of the requested object
            if(!class_exists($class)) {
                // first, read the file to see if the class extends from another (which could not be loaded yet)
                $filename = QN_BASEDIR.'/packages/'.self::getObjectPackage($class).'/classes/'.self::getObjectClassFile($class);
                if(!is_file($filename)) {
                    throw new Exception("unknown model: '$class'", QN_ERROR_UNKNOWN_OBJECT);
                }
                $parts = explode('\\', $class);
                $class_name = array_pop($parts);
                $file_content = file_get_contents($filename);
                preg_match('/class(\s*)'.$class_name.'(.*)(\s*)\{/iU', $file_content, $matches);
                if(!isset($matches[1])) {
                    throw new Exception("malformed class file for model '$class': class name do not match file name", QN_ERROR_INVALID_PARAM);
                }
                // #todo - this should be tested in package-consistency controller
                preg_match('/\bextends\b(.*)(\s*)\{/iU', $file_content, $matches);
                if(!isset($matches[1])) {
                    throw new Exception("malformed class file for model '$class': parent class name not found in file", QN_ERROR_INVALID_PARAM);
                }
                if(!(include_once $filename)) {
                    throw new Exception("unknown model: '$class'", QN_ERROR_UNKNOWN_OBJECT);
                }
                if(!class_exists($class)) {
                    throw new Exception("unknown model (check file syntax): '$class'", QN_ERROR_UNKNOWN_OBJECT);
                }
            }
            if(!isset($this->models[$class])) {
                $this->models[$class] = new $class();
            }
            if(count($fields)) {
                return new $class($fields);
            }
        }
        return $this->models[$class];
    }

    /**
     * Gets the name of the table associated to the specified class (required to convert namespace notation).
     *
     * @param   string      $class          The full name of the class, with its namespace.
     * @return  mixed(string|integer)       Returns the name of the table related to the Class, or an error code (integer) if class cannot be resolved.
     */
    public function getObjectTableName($class) {
        $result = '';
        try {
            $object = $this->getStaticInstance($class);
            $result = strtolower($object->getTable());
        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            return $e->getCode();
        }
        return $result;
    }

    /**
     * Gets the filename containing the class definition of a class,
     * without package name, but including namespace path (required to convert namespace notation).
     *
     * @param   string  $class  The full name of the entity with its namespace.
     * @return  string  The path of the file holding the class definition, relative to the `<package>/class/` folder
     */
    public static function getObjectClassFile($class) {
        $parts = explode('\\', $class);
        array_shift($parts);
        return implode('/', $parts).'.class.php';
    }

    /**
     * Retrieve the root parent class of a class.
     * If there are several level of inheritance, the method loops up until the first class that inherits from the Model interface (`equal\orm\Model`).
     *
     * @param   string  $class   The full name of the entity with its namespace.
     */
    public static function getObjectRootClass($class) {
        $entity = $class;
        while(true) {
            if(method_exists($class, 'getTable')) {
                $reflectionClass = new \ReflectionClass($class);
                if($reflectionClass->getMethod('getTable')->class == $class) {
                    break;
                }
            }
            $parent = (class_exists($entity))?get_parent_class($entity):false;
            if(!$parent || $parent == 'equal\orm\Model') {
                break;
            }
            $entity = $parent;
        }
        return $entity;
    }

    /**
     * Retrieve the root parent class of a class.
     * If there are several level of inheritance, the method loops up until the first class that inherits from the Model interface (`equal\orm\Model`).
     *
     * @param   string  $class   The full name of the entity with its namespace.
     * @return  array   Returns an array containing the list of all the classes the given class inherits from (order is not guaranteed).
     */
    public static function getObjectParentsClasses($class) {
        $classes = [];
        $entity = $class;
        while(true) {
            $parent = get_parent_class($entity);
            if(!$parent || $parent == 'equal\orm\Model') {
                break;
            }
            $classes[] = $parent;
            $entity = $parent;
        }
        return $classes;
    }

    /**
     * Retrieve the package in which is defined the class of an object (required to convert namespace notation).
     *
     * @param   string  $class   The full name of the entity with its namespace.
     * @return  string
     */
    public static function getObjectPackage($class) {
        $parts = explode('\\', $class);
        return array_shift($parts);
    }

    /**
     * Retrieve the complete schema of an object (including special fields).
     *  note: this method is not set as static since we need to load class file in order to retrieve the schema
     * (and this is only done in the getStaticInstance method)
     *
     * @param   string  $class   The full name of the entity with its namespace.
     * @return  array
     */
    public function getObjectSchema($class) {
        $object = $this->getStaticInstance($class);
        return $object->getSchema();
    }


    /**
     * Check if a getRoles() method is defined on the targeted class.
     * Methods from ancestors classes are not taken in account.
     *
     * @param   string  $class   The full name of the entity with its namespace.
     * @return array|boolean Returns either and associative array mapping roles with their descriptors. In case no getRoles() method is defined, returns false.
     */
    public function hasObjectRoles($class) {
        if(method_exists($class, 'getRoles')) {
            /** @var \ReflectionClass */
            $reflectionClass = new \ReflectionClass($class);
            return ($reflectionClass->getMethod('getRoles')->class == $class);
        }
        return false;
    }

    /**
     * #todo - deprecate : use Field class instead.
     * @deprecated
     */
    public function getFinalType($object_class, $field) {
        $schema = $this->getObjectSchema($object_class);

        while($field && isset($schema[$field]) && isset($schema[$field]['type']) && $schema[$field]['type'] == 'alias') {
            $field = $schema[$field]['alias'];
        }

        if(!$field || !isset($schema[$field]) || !isset($schema[$field]['type'])) {
            return null;
        }

        $type = $schema[$field]['type'];

        if(isset($schema[$field]['result_type'])) {
            $type = $schema[$field]['result_type'];
        }

        return $type;
    }

    /**
    * Checks if all the given attributes are defined in the specified schema for the given field.
    *
    * @param  array     $check_array
    * @param  array     $schema
    * @param  string    $field
    * @return bool      Returns true if all attributes in $check_array actually exist in the given schema.
    */
    public static function checkFieldAttributes($check_array, $schema, $field) {
        if (!isset($schema) || !isset($schema[$field]) || !isset($schema[$field]['type'])) {
            throw new Exception("empty schema or unknown field name '$field'", QN_ERROR_UNKNOWN_OBJECT);
        }
        $required_attributes = $check_array[$schema[$field]['type']];
        $map_attributes = $schema[$field];
        $attributes = array_keys($map_attributes);
        foreach($required_attributes as $rule) {
            if(is_array($rule)) {
                if(count(array_intersect($attributes, $rule)) <= 0) {
                    return false;
                }
            }
            else {
                if(!isset($map_attributes[$rule])) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Sanitize a value provided as `$ids`: a list of object identifiers.
     * Return an array of valid identifiers (positive integers).
     *
     * @param   $ids
     */
    private function sanitizeIdentifiers($ids) {
        // sanitize $ids
        if(!is_array($ids)) {
            $ids = (array) $ids;
        }
        // ensure ids are positive integer values
        foreach($ids as $i => $oid) {
            $id = intval($oid);
            if(!is_numeric($oid) || $id <= 0) {
                unset($ids[$i]);
                continue;
            }
            $ids[$i] = $id;
        }
        // remove duplicate ids, if any
        $ids = array_unique($ids);
        return $ids;
    }

    /**
     * Filter given identifiers and return only valid ones (whatever the status of the objects).
     * Ids that do not match an object in the database are removed from the list (Ids of soft-deleted object are considered valid).
     *
     * @param   $class
     * @param   $ids
     */
    public function filterExistingIdentifiers($class, $ids) {
        $ids = $this->sanitizeIdentifiers($ids);

        if(empty($ids)) {
            return [];
        }
        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();
            $map_valid_ids = [];
            $table_name = $this->getObjectTableName($class);
            // get all records at once
            $result = $db->getRecords($table_name, 'id', $ids);
            // store all found ids in an array
            while($row = $db->fetchArray($result)) {
                $id = (int) $row['id'];
                $map_valid_ids[$id] = true;
            }
            foreach($ids as $i => $id) {
                if(!isset($map_valid_ids[$id])) {
                    unset($ids[$i]);
                }
            }
        }
        catch(\Exception $e) {
            // unexpected error (DB connection)
        }

        return $ids;
    }
    /**
     * Load given fields from specified class into the cache
     *
     * @return void
     * @throws Exception
     */
    private function load($class, $ids, $fields, $lang) {
        // get the object instance
        $object = $this->getStaticInstance($class);
        // get the complete schema of the object (including special fields)
        $schema = $object->getSchema();
        // retrieve the name of the DB table associated to the class
        $table_name = $this->getObjectTableName($class);

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();

            // array holding functions to load each type of fields
            $load_fields = [
                // 'alias'
                'alias'        =>    function($om, $ids, $fields) {
                    // nothing to do : this type is handled in read methods
                },
                // 'multilang' is a particular case of simple field
                'multilang'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                    $result = $om->db->getRecords(
                        array('core_translation'),
                        array('object_id', 'object_field', 'value'),
                        $ids,
                        array(array(
                                array('language','=',$lang),
                                array('object_class','=',$class),
                                array('object_field','in',$fields)
                                )
                        ),
                        'object_id');
                    // fill in the internal buffer with returned rows (translation is stored in the 'value' column)
                    while($row = $om->db->fetchArray($result)) {
                        $oid = $row['object_id'];
                        $field = $row['object_field'];
                        // do some pre-treatment if necessary (this step is symmetrical to the one in store method)
                        /** @var \equal\data\adapt\DataAdapterProvider */
                        $dap = $this->container->get('adapt');
                        /** @var \equal\data\adapt\DataAdapter */
                        $adapter = $dap->get('sql');
                        $value = $adapter->adaptIn($row['value'], 'binary');
                        // update the internal buffer with fetched value
                        $om->cache[$table_name][$oid][$lang][$field] = $value;
                    }
                    // force assignment to null if no result was returned by the SQL query
                    foreach($ids as $oid) {
                        foreach($fields as $field) {
                            if(!isset($om->cache[$table_name][$oid][$lang][$field])) {
                                $om->cache[$table_name][$oid][$lang][$field] = null;
                            }
                        }
                    }
                },
                'simple'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                    // make sure to load the 'id' field (we need it to map fetched values to their object)
                    $fields[] = 'id';
                    // get all records at once
                    $result = $om->db->getRecords([$table_name], $fields, $ids);

                    // treat sql result in the same order than the ids list
                    while($row = $om->db->fetchArray($result)) {
                        // retrieve the id of the object associated with current record
                        $oid = $row['id'];
                        foreach($row as $field => $value) {
                            // do some pre-treatment if necessary (this step is symmetrical to the one in store method)
                            if(!is_null($value)) {
                                /** @var \equal\data\adapt\DataAdapterProvider */
                                $dap = $this->container->get('adapt');
                                /** @var \equal\data\adapt\DataAdapter */
                                $adapter = $dap->get('sql');
                                $f = new Field($schema[$field], $field);
                                $value = $adapter->adaptIn($value, $f->getUsage());
                            }
                            // update the internal buffer with fetched value
                            $om->cache[$table_name][$oid][$lang][$field] = $value;
                        }
                    }
                },
                'one2many'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                    foreach($fields as $field) {
                        if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) {
                            throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        $order = (isset($schema[$field]['order']) && isset($schema[$schema[$field]['order']]))?$schema[$field]['order']:'id';
                        $sort = (isset($schema[$field]['sort']))?$schema[$field]['sort']:'asc';
                        $domain = [
                            [
                                [$schema[$field]['foreign_field'], 'in', $ids],
                                ['state', '=', 'instance'],
                                ['deleted', '=', '0'],
                            ]
                        ];
                        // #todo : handle alias fields (require subsequent schema)
                        // merge domain with additional domain clauses, if any
                        if(isset($schema[$field]['domain'])) {
                            $domain_tmp = new Domain($domain);
                            $domain_tmp->merge(new Domain($schema[$field]['domain']));
                            $domain = $domain_tmp->toArray();
                        }
                        // #todo - add support for sorting on m2o fields (for now user needs to use usort)
                        if($schema[$order]['type'] == 'many2one' || (isset($schema[$order]['result_type']) && $schema[$order]['result_type'] == 'many2one') ) {
                            $order = 'id';
                        }
                        // obtain the ids by searching inside the foreign object's table
                        $result = $om->db->getRecords(
                                $om->getObjectTableName($schema[$field]['foreign_object']),
                                ['id', $schema[$field]['foreign_field'], $order],
                                null,
                                $domain,
                                'id',
                                [$order => $sort]
                            );
                        $items = [];
                        while ($row = $om->db->fetchArray($result)) {
                            $id = $row[$schema[$field]['foreign_field']];
                            $items[$id][] = $row['id'];
                        }
                        foreach($ids as $id) {
                            $om->cache[$table_name][$id][$lang][$field] = (isset($items[$id]))?$items[$id]:[];
                        }
                    }
                },
                'many2many'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                    foreach($fields as $field) {
                        if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) {
                            throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        // obtain the ids by searching inside relation table
                        $result = $om->db->getRecords(
                            array('t0' => $om->getObjectTableName($schema[$field]['foreign_object']), 't1' => $schema[$field]['rel_table']),
                            array('t1.'.$schema[$field]['rel_foreign_key'], 't1.'.$schema[$field]['rel_local_key']),
                            null,
                            [
                                [
                                    // #memo - right field needs to be escaped because there is no way for dbManipulator to guess it is not a value
                                    array('t0.id', '=', "`t1`.`{$schema[$field]['rel_foreign_key']}`"),
                                    array('t1.'.$schema[$field]['rel_local_key'], 'in', $ids),
                                    array('t0.state', '=', 'instance'),
                                    array('t0.deleted', '=', '0'),
                                ]
                            ],
                            't0.id'
                        );
                        $lists = array();
                        while ($row = $om->db->fetchArray($result)) {
                            $oid = $row[$schema[$field]['rel_local_key']];
                            $lists[$oid][] = $row[$schema[$field]['rel_foreign_key']];
                        }
                        foreach($ids as $oid) {
                            $om->cache[$table_name][$oid][$lang][$field] = (isset($lists[$oid]))?$lists[$oid]:[];
                        }
                    }
                },
                'computed'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                    foreach($fields as $field) {
                        if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) {
                            throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        // spot the fields that have not been computed yet (or unset) during this cycle
                        $missing_ids = [];
                        foreach($ids as $oid) {
                            if(!isset($om->cache[$table_name][$oid][$lang][$field])) {
                                $missing_ids[] = $oid;
                            }
                        }
                        if(count($missing_ids)) {
                            if(isset($schema[$field]['function'])) {
                                trigger_error("ORM::computing 'function' for '$field' of class '$class'", QN_REPORT_INFO);
                                $res = $this->callonce($class, $schema[$field]['function'], $missing_ids, [], $lang);
                                if($res > 0) {
                                    foreach($missing_ids as $oid) {
                                        if(isset($res[$oid])) {
                                            $value = $res[$oid];
                                        }
                                        else {
                                            $value = null;
                                        }
                                        $om->cache[$table_name][$oid][$lang][$field] = $value;
                                    }
                                }
                            }
                            elseif(isset($schema[$field]['relation']) && is_array($schema[$field]['relation'])) {
                                try {
                                    trigger_error("ORM::computing 'relation' for '$field' of class '$class'", QN_REPORT_INFO);
                                    $res = $class::ids($ids)->read($schema[$field]['relation'])->get(true);
                                    foreach($res as $elem) {
                                        $id = $elem['id'];
                                        $relation = $schema[$field]['relation'];
                                        while(is_array($relation)) {
                                            $target = array_key_first($relation);
                                            if(is_numeric($target)) {
                                                $om->cache[$table_name][$id][$lang][$field] = $elem[$relation[$target]] ?? null;
                                                break;
                                            }
                                            else {
                                                $elem = $elem[$target] ?? null;
                                                $relation = isset($relation[$target]) ? (array) $relation[$target] : null;
                                            }
                                        }
                                    }
                                }
                                catch(Exception $e) {
                                    trigger_error('ORM::unable to retrieve targeted relational field: '.$e->getMessage(), QN_REPORT_ERROR);
                                }

                            }
                        }
                    }
                }
            ];

            if(!isset($this->cache[$table_name])) {
                $this->cache[$table_name] = [];
            }

            // build an associative array ordering given fields by their type
            $fields_lists = array();
            // remember computed fields having store attribute set (we need this as $type will hold the $schema['result_type'] value)
            $stored_fields = array();

            // 1) retrieve fields types
            foreach($fields as $key => $field) {
                // retrieve field type
                $type = $schema[$field]['type'];
                // computed fields are handled following their resulting type
                if( ($type == 'computed')
                    && isset($schema[$field]['store'])
                    && $schema[$field]['store']
                ) {
                    $type = $schema[$field]['result_type'];
                    $stored_fields[] = $field;
                }
                // make a distinction between simple and complex fields (all simple fields are treated the same way)
                if(in_array($type, self::$simple_types)) {
                    // note: only simple fields can have the multilang attribute set (this includes computed fields having a simple type as result)
                    if($lang != constant('DEFAULT_LANG') && isset($schema[$field]['multilang']) && $schema[$field]['multilang']) {
                        $fields_lists['multilang'][] = $field;
                    }
                    // note: if $lang differs from DEFAULT_LANG and field is not set as multilang, no change will be stored for that field
                    else {
                        $fields_lists['simple'][] = $field;
                    }
                }
                else {
                    $fields_lists[$type][] = $field;
                }
            }

            // 2) load fields values, grouping fields by type
            foreach($fields_lists as $type => $list) {
                if(isset($load_fields[$type])) {
                    $load_fields[$type]($this, $ids, $list);
                }
            }

            // 3) check if some computed fields were not set in database
            foreach($stored_fields as $field) {
                // for each computed field, build an array holding ids of incomplete objects
                $missing_ids = [];
                // if store attribute is set and no result was found, we need to compute the value
                foreach($ids as $id) {
                    // #memo - is_null() is favoured over empty() since an empty value could be the result of a calculation
                    if(!isset($this->cache[$table_name][$id][$lang][$field])
                        || is_null($this->cache[$table_name][$id][$lang][$field])) {
                        $missing_ids[] = $id;
                    }
                }
                if(count($missing_ids)) {
                    // compute field for incomplete objects
                    $load_fields['computed']($this, $missing_ids, array($field));
                    try {
                        // store newly computed fields to database ('store' attribute set to true)
                        $this->store($class, $missing_ids, array($field), $lang);
                    }
                    catch(Exception $e) {
                        trigger_error('ORM::unable to store computed field: '.$e->getMessage(), QN_REPORT_ERROR);
                    }
                }
            }

        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            throw new Exception('unable to load object fields', $e->getCode());
        }
    }

    /**
     * Stores specified fields of selected objects into database.
     *
     * @param string $class     Class name of the objects to be stored.
     * @param array  $ids       List of identifiers of the objects to store.
     * @param array  $fields    List of fields names to store (values are read from `$cache`).
     * @param string $lang      Lang id (2 chars) in which to store multilang fields.
     */
    private function store($class, $ids, $fields, $lang) {
        // get the object instance
        $object = $this->getStaticInstance($class);
        // get the complete schema of the object (including special fields)
        $schema = $object->getSchema();
        // retrieve the name of the DB table associated to the class
        $table_name = $this->getObjectTableName($class);

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();
            // array holding functions to store each type of fields
            $store_fields = array(
            // 'multilang' is a particular case of simple field)
            'multilang'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                $om->db->deleteRecords(
                    'core_translation',
                    $ids,
                    array(
                        array(
                            array('language', '=', $lang),
                            array('object_class', '=', $class),
                            array('object_field', 'in', $fields)
                        )
                    ),
                    'object_id'
                );
                /** @var \equal\data\adapt\DataAdapterProvider */
                $dap = $this->container->get('adapt');
                /** @var \equal\data\adapt\DataAdapter */
                $adapter = $dap->get('sql');

                $values_array = [];
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $values_array[] = [
                            $lang,
                            $class,
                            $field,
                            $oid,
                            $adapter->adaptOut($om->cache[$table_name][$oid][$lang][$field], 'binary'),
                            // creator
                            QN_ROOT_USER_ID,
                            // created
                            $adapter->adaptOut(time(), 'datetime'),
                            // modifier
                            QN_ROOT_USER_ID,
                            // modified
                            $adapter->adaptOut(time(), 'datetime'),
                            // state
                            'instance',
                            // deleted
                            0
                        ];
                    }
                }
                $om->db->addRecords('core_translation', [
                            'language',
                            'object_class',
                            'object_field',
                            'object_id',
                            'value',
                            'creator',
                            'created',
                            'modifier',
                            'modified',
                            'state',
                            'deleted'
                        ],
                        $values_array
                    );
            },
            'simple'    =>    function($om, $ids, $fields) use ($schema, $class, $table_name) {
                /** @var \equal\data\adapt\DataAdapterProvider */
                $dap = $this->container->get('adapt');
                // #memo - this handler is for non-multilang fields
                $lang = constant('DEFAULT_LANG');
                foreach($ids as $oid) {
                    $fields_values = array();
                    foreach($fields as $field) {
                        $value = (isset($om->cache[$table_name][$oid][$lang][$field]))?$om->cache[$table_name][$oid][$lang][$field]:null;
                        // adapt values except for null of computed fields (marked as to be re-computed)
                        if(!is_null($value) || $schema[$field]['type'] != 'computed') {
                            /** @var \equal\data\adapt\DataAdapter */
                            $adapter = $dap->get('sql');
                            $f = new Field($schema[$field], $field);
                            // adapt value to SQL
                            $value = $adapter->adaptOut($value, $f->getUsage());
                        }
                        $fields_values[$field] = $value;
                    }
                    $om->db->setRecords($om->getObjectTableName($class), array($oid), $fields_values);
                }
            },
            'one2many'     =>    function($om, $ids, $fields) use ($schema, $class, $table_name) {
                // #memo - this handler is for non-multilang fields
                $lang = constant('DEFAULT_LANG');
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $value = $om->cache[$table_name][$oid][$lang][$field];
                        if(!is_array($value)) {
                            if(is_numeric($value)) {
                                $value = (array) intval($value);
                            }
                            else {
                                trigger_error("ORM::wrong value for field '$field' of class '$class', should be an array", QN_REPORT_ERROR);
                                continue;
                            }
                        }
                        $ids_to_remove = array();
                        $ids_to_add = array();
                        foreach($value as $id) {
                            if(!is_numeric($id)) {
                                continue;
                            }
                            $id = intval($id);
                            if($id < 0) {
                                $ids_to_remove[] = abs($id);
                            }
                            elseif($id > 0) {
                                $ids_to_add[] = $id;
                            }
                        }
                        $foreign_table = $om->getObjectTableName($schema[$field]['foreign_object']);
                        // remove relation
                        if(count($ids_to_remove)) {
                            if(isset($schema[$field]['ondetach'])) {
                                $call_res = $this->callonce($class, $schema[$field]['ondetach'], $ids, $ids_to_remove, $lang);
                                // for unknown method, check special keywords 'delete' and 'null'
                                if($call_res < 0) {
                                    switch($schema[$field]['ondetach']) {
                                        case 'delete':
                                            $this->delete($schema[$field]['foreign_object'], $ids_to_remove, true);
                                            break;
                                        case 'null':
                                        default:
                                            $om->db->setRecords($foreign_table, $ids_to_remove, [ $schema[$field]['foreign_field'] => null ] );
                                            break;
                                    }
                                }
                            }
                            else {
                                // remove relation by setting pointing id to 0
                                $om->db->setRecords($foreign_table, $ids_to_remove, [$schema[$field]['foreign_field'] => null]);
                            }
                        }
                        // add relation by setting the pointing id (overwrite previous value if any)
                        if(count($ids_to_add)) {
                            $om->db->setRecords($foreign_table, $ids_to_add, [$schema[$field]['foreign_field'] => $oid]);
                        }
                        // invalidate cache (field partially loaded)
                        unset($om->cache[$table_name][$oid][$lang][$field]);
                    }
                }
            },
            'many2many' =>    function($om, $ids, $fields) use ($schema, $class, $table_name) {
                // #memo - this handler is for non-multilang fields
                $lang = constant('DEFAULT_LANG');
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $rel_ids = $om->cache[$table_name][$oid][$lang][$field];
                        if(!is_array($rel_ids)) {
                            if(is_numeric($rel_ids)) {
                                $rel_ids = [intval($rel_ids)];
                            }
                            else {
                                trigger_error("ORM::wrong value for field '$field' of class '$class', should be an array", QN_REPORT_ERROR);
                                continue;
                            }
                        }
                        $values = [];
                        foreach($rel_ids as $index => $id) {
                            $id = intval($id);
                            // ignore ids to remove
                            if($id > 0) {
                                $values[] = array($oid, $id);
                            }
                            $rel_ids[$index] = abs($id);
                        }
                        // delete all targeted relations
                        $om->db->deleteRecords(
                            $schema[$field]['rel_table'],
                            array($oid),
                            array(
                                array(
                                    array($schema[$field]['rel_foreign_key'], 'in', $rel_ids)
                                )
                            ),
                            $schema[$field]['rel_local_key']
                        );
                        // re-create only relations with positive id as value
                        $om->db->addRecords(
                            $schema[$field]['rel_table'],
                            array(
                                $schema[$field]['rel_local_key'],
                                $schema[$field]['rel_foreign_key']
                            ),
                            $values
                        );
                        // invalidate cache (field partially loaded)
                        unset($om->cache[$table_name][$oid][$lang][$field]);
                    }
                }
            },
            'computed' =>    function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                // nothing to store for computed fields (ending up here means that the 'store' attribute is not set)
            },
            'alias' =>       function($om, $ids, $fields) use ($schema, $class, $table_name, $lang) {
                // nothing to store for virtual fields
            });

            // build an associative array ordering given fields by their type
            $fields_lists = array();

            // 1) retrieve fields types
            foreach($fields as $field) {
                // retrieve field type
                $type = $schema[$field]['type'];
                // stored computed fields are handled following their resulting type
                if( ($type == 'computed')
                    && isset($schema[$field]['store'])
                    && $schema[$field]['store']
                ) {
                    $type = $schema[$field]['result_type'];
                }
                // make a distinction between simple and complex fields (all simple fields are treated the same way)
                if(in_array($type, self::$simple_types)) {
                    // note: only simple fields can have the multilang attribute set (this includes computed fields having a simple type as result)
                    if($lang != constant('DEFAULT_LANG') && isset($schema[$field]['multilang']) && $schema[$field]['multilang']) {
                        $fields_lists['multilang'][] = $field;
                    }
                    // note: if $lang differs from DEFAULT_LANG and field is not set as multilang, no change will be stored for that field
                    else {
                        $fields_lists['simple'][] = $field;
                    }
                }
                else {
                    $fields_lists[$type][] = $field;
                }
            }
            // 2) store fields according to their types
            foreach($fields_lists as $type => $list) {
                $store_fields[$type]($this, $ids, $list);
            }

        }
        catch (Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            throw new Exception('unable to store object fields', $e->getCode());
        }
    }


    /**
     * Invoke a callback from an object Class.
     * By default, objects callback signature is `methodName($orm: object, $ids: array, $lang: string)`. Arguments can be adapted in the $signature parameter.
     * This method can lead to recursion: class member `object_methods` is used to prevent inner loop within a same cycle (sub-calls from callbacks invoked in a create, read, write, delete).
     *
     * @param   string    $class
     * @param   string    $method
     * @param   int[]     $ids
     * @param   array     $values
     * @param   array     $signature        (deprecated) List of parameters to relay to target method (required if differing from default).
     * @return  mixed                       Returns the result of the called method (defaults to empty array), or error code (negative int) if something went wrong.
     */
    public function callonce($class, $method, $ids=[], $values=[], $lang=null, $signature=['ids', 'values', 'lang']) {
        trigger_error("ORM::calling orm\ObjectManager::callonce {$class}::{$method}", QN_REPORT_DEBUG);

        // stack current state of object_methods map (current state is restored at the end of the method)
        $object_methods_state = $this->object_methods;

        $result = [];

        $lang = ($lang)?$lang:constant('DEFAULT_LANG');
        $called_class = $class;
        $called_method = $method;

        $parts = explode('::', $method);
        $count = count($parts);

        if( $count < 1 || $count > 2 ) {
            trigger_error("ORM::invalid args ($method, $class)", QN_REPORT_WARNING);
            return QN_ERROR_INVALID_PARAM;
        }

        if( $count == 2 ) {
            $called_class = $parts[0];
            $called_method = $parts[1];
        }

        if(!method_exists($called_class, $called_method)) {
            trigger_error("ORM::ignoring non-existing method '$method' for class '$class'", QN_REPORT_INFO);
            return QN_ERROR_UNKNOWN;
        }

        // init call stack
        if(!isset($this->object_methods[$called_class][$called_method])) {
            $this->object_methods[$called_class][$called_method] = [];
        }

        // prevent inner loops (several calls to same handler with identical ids) during the cycle (subsequent `callonce()` calls)
        $processed_ids = $this->object_methods[$called_class][$called_method];
        $unprocessed_ids = array_diff((array) $ids, $processed_ids);
        $this->object_methods[$called_class][$called_method] = array_merge($processed_ids, $unprocessed_ids);

        /** @var \ReflectionMethod */
        $method = new \ReflectionMethod($called_class, $called_method);
        /** @var \ReflectionParameter */
        $params = $method->getParameters();

        $args = [];
        foreach($params as $param) {
            $param_name = $param->getName();
            if(in_array($param_name, ['om', 'orm'])) {
                $args[] = $this;
            }
            elseif(in_array($param_name, [
                    'report',
                    'auth',
                    'access',
                    'context',
                    'validate',
                    'adapt',
                    'route',
                    'log',
                    'cron',
                    'dispatch',
                    'db'])) {
                $args[] = $this->container->get($param_name);
            }

            elseif(in_array($param_name, ['ids', 'oids'])) {
                $args[] = $unprocessed_ids;
            }
            elseif($param_name == 'values') {
                $args[] = $values;
            }
            // #todo - deprecate : use $auth instead
            elseif($param_name == 'user_id') {
                $auth = $this->container->get('auth');
                $user_id = $auth->userId();
                $args[] = $user_id;
            }
            elseif($param_name == 'lang') {
                $args[] = $lang;
            }
            elseif($param_name == 'self') {
                $args[] = $called_class::ids($unprocessed_ids)->lang($lang);
            }
        }

        try {
            $res = $called_class::$called_method(...$args);
            if($res !== null) {
                $result = $res;
            }
        }
        catch(\Exception $e) {
            $result = $e->getCode();
        }

        // unstack global object_methods state
        $this->object_methods = $object_methods_state;

        return $result;
    }

    /**
     * Invoke a callback from an object Class.
     * Default objects callback signature is `methodName($orm: object, $ids: array, $lang: string)`
     * There is no recursion protection and a same callback can be invoked several times without any restrictions.
     *
     * @param string    $class
     * @param string    $method
     * @param int[]     $ids
     * @param array     $values
     * @param array     $signature  (deprecated) List of parameters to relay to target method (required if differing from default).
     */
    public function call($class, $method, $ids, $values=[], $lang=null, $signature=['ids', 'values', 'lang']) {
        trigger_error("ORM::calling orm\ObjectManager::call {$class}::{$method}", QN_REPORT_DEBUG);
        $result = [];

        $lang = ($lang)?$lang:constant('DEFAULT_LANG');
        $called_class = $class;
        $called_method = $method;

        $parts = explode('::', $method);
        $count = count($parts);

        if( $count < 1 || $count > 2 ) {
            throw new Exception("ObjectManager::call: invalid args ($method, $class)");
        }

        if( $count == 2 ) {
            $called_class = $parts[0];
            $called_method = $parts[1];
        }

        if(!method_exists($called_class, $called_method)) {
            trigger_error("ORM::ignoring non-existing method '$method' for class '$class'", QN_REPORT_INFO);
            return $result;
        }
        /** @var \ReflectionMethod */
        $method = new \ReflectionMethod($called_class, $called_method);
        /** @var \ReflectionParameter */
        $params = $method->getParameters();

        $args = [];
        foreach($params as $param) {
            $param_name = $param->getName();
            if(in_array($param_name, ['om', 'orm'])) {
                $args[] = $this;
            }
            elseif(in_array($param_name, ['ids', 'oids'])) {
                $args[] = $ids;
            }
            elseif($param_name == 'values') {
                $args[] = $values;
            }
            elseif($param_name == 'lang') {
                $args[] = $lang;
            }
            elseif($param_name == 'self') {
                $args[] = $called_class::ids($ids)->lang($lang);
            }
        }

        try {
            $res = $called_class::$called_method(...$args);
            if($res !== null) {
                $result = $res;
            }
        }
        catch(\Exception $e) {
            $result = $e->getCode();
        }

        return $result;
    }

    /**
     * Retrieve the static instance of a given class (Model with default values).
     * This method is registered as autoload handler in `eq.lib.php`.
     *
     * @return boolean|Model   Returns the static instance of the model with default values. If no Model matches the class name returns false.
     */
    public function getModel($class) {
        $model = false;
        try {
            $model = $this->getStaticInstance($class);
        }
        catch(Exception $e) {
            // #memo - another autoload handler might be registered, so no exception must be raised here
            trigger_error('ORM::'.$e->getMessage(), QN_REPORT_ERROR);
        }
        return $model;
    }

    public function getLastError() {
        return $this->last_error;
    }

    /**
     * Checks whether a set of values is valid according to given class definition.
     * This is done using the class validation method.
     * Relations consistency check (verifying that targeted object(s) actually exist) is not performed here.
     *
     * Result example:
     *           "INVALID_PARAM": {
     *             "login": {
     *                 "invalid_email": "Login must be a valid email address."
     *               }
     *            }
     *
     * @param   string          $class          Entity name.
     * @param   array           $ids            Array of objects identifiers.
     * @param   array           $values         Associative array mapping fields names with values to be assigned to the object(s).
     * @param   boolean         $check_unique   Request check for unique constraints (related to getUnique method).
     * @param   boolean         $check_required Request check for required fields (and _self constraints).
     *
     * @return  array           Returns an associative array containing invalid fields with their associated error_message_id.
     *                          An empty array means all fields are valid. In case of error, the method returns a negative integer.
     */
    public function validate($class, $ids, $values, $check_unique=false, $check_required=false) {
        $res = [];

        /** @var \equal\orm\Model */
        $model = $this->getStaticInstance($class);
        $schema = $model->getSchema();


        /**
         * 1) REQUIRED constraint check
         */

        if($check_required) {
            foreach($schema as $field => $def) {
                // required fields must be provided and cannot be left/set to null
                if( isset($def['required']) && $def['required'] && (!isset($values[$field]) || is_null($values[$field])) ) {
                    $error_code = QN_ERROR_INVALID_PARAM;
                    $res[$field]['missing_mandatory'] = 'Missing mandatory value.';
                    // issue a warning about missing mandatory field
                    trigger_error("ORM::mandatory field {$field} is missing for instance of {$class}", QN_REPORT_WARNING);
                }
            }
        }


        /**
         * 2) MODEL constraint check
         */

        // get constraints defined in the model (schema)
        $constraints = $model->getConstraints();
        // append constraints implied by type and usage
        foreach($values as $field => $value) {
            /** @var Field */
            $f = $model->getField($field);
            foreach($f->getConstraints() as $error_id => $constraint) {
                if(!isset($constraint['function'])) {
                    continue;
                }
                if(is_callable($constraint['function'])) {
                    $closure = \Closure::fromCallable($constraint['function']);
                    $closure->bindTo($f->getUsage());
                    if(!isset($constraints[$field])) {
                        $constraints[$field] = [];
                    }
                    $constraints[$field][$error_id] = [
                            'message'   => $constraint['message'],
                            'function'  => $closure
                        ];
                }
            }
        }
        // check constraints
        foreach($values as $field => $value) {
            if(!isset($constraints[$field]) || empty($constraints[$field])) {
                // ignore fields with no constraints
                continue;
            }
            if($value === null) {
                // all fields can be reset to null (unless marked as `required`)
                continue;
            }
            foreach($constraints[$field] as $error_id => $constraint) {
                if(!isset($constraint['function']) ) {
                    continue;
                }
                $validation_func = $constraint['function'];
                // #todo - use a single arg (validation should be independent from context, otherwise use cancreate/canupdate)
                if(is_callable($validation_func) && !call_user_func($validation_func, $value, $values)) {
                    if(!isset($constraint['message'])) {
                        $constraint['message'] = 'Invalid field.';
                    }
                    trigger_error("ORM::given value for field `{$field}` violates constraint : {$constraint['message']}", QN_REPORT_INFO);
                    $error_code = QN_ERROR_INVALID_PARAM;
                    if(!isset($res[$field])) {
                        $res[$field] = [];
                    }
                    $res[$field][$error_id] = $constraint['message'];
                }
            }
        }


        /**
         * 3) UNIQUE constraint check
         */

        if($check_unique && !count($res) && method_exists($model, 'getUnique')) {
            $uniques = $model->getUnique();

            // load all fields involved in 'unique' constraints
            $unique_fields = [];
            foreach($uniques as $unique) {
                foreach($unique as $field) {
                    $unique_fields[] = $field;
                }
            }
            $extra_values = [];
            $extra_fields = array_diff(array_unique($unique_fields), array_keys($values));
            if(count($extra_fields)) {
                $extra_values = $this->read($class, $ids, $extra_fields);
            }

            $ids = (array) $ids;

            // $ids can be empty for validation at creation of new object
            if(empty($ids)) {
                // make sure we always have at least one id
                $ids[] = 0;
            }
            foreach($ids as $id) {
                foreach($uniques as $unique) {
                    $conflict_ids = [];
                    $domain = [];
                    foreach($unique as $field) {
                        // map unique fields with the given values
                        $value = null;
                        if(isset($values[$field])) {
                            $value = $values[$field];
                        }
                        elseif(isset($extra_values[$id][$field])) {
                            $value = $extra_values[$id][$field];
                        }
                        elseif(!isset($schema[$field])) {
                            // ignore non-exiting fields
                            continue;
                        }
                        // #memo - field involved in unique constraint can be left to null (unless marked as required)
                        if(is_null($value)) {
                            // if one of the value composing the key is null, we consider the object as valid (discard constraint)
                            continue 2;
                        }
                        $domain[] = [$field, '=', $value];
                    }
                    // search for objects already set with unique values
                    if(count($domain)) {
                        // overload default 'state' condition (set in search method): no restriction on state
                        $domain[] = ['state', 'like', '%%'];
                        // overload default 'deleted' condition (set in search method): we must be able to restore deleted objects
                        $domain[] = ['deleted', 'in', ['0', '1']];
                        // object does not conflict with itself
                        $domain[] = ['id', '<>', $id];
                        $conflict_ids = $this->search($class, $domain);
                        if($conflict_ids < 0) {
                            // interrupt the process in case of system error (SQL)
                            return $conflict_ids;
                        }
                    }
                    // there is a violation : stop and fetch info about it
                    if(count($conflict_ids)) {
                        trigger_error("ORM::field {$field} violates unique constraint with objects (".implode(',', $conflict_ids).")", QN_REPORT_WARNING);
                        $error_code = QN_ERROR_CONFLICT_OBJECT;
                        $res[$field] = ['duplicate_index' => 'unique constraint violation'];
                        break 2;
                    }
                }

                // loop only if some field impacted by the 'unique' constraint are not present in the received $values
                if(count($extra_fields) <= 0) {
                    break;
                }
            }

        }

        return (count($res))?[$error_code => $res]:[];
    }


    /**
     * Creates a new instance of given class and, if given, assigns values to targeted fields.
     *
     * Upon creation, the object remains in 'draft' state until it has been written:
     * - if $fields is empty, a draft object is created with fields set to default values defined by the class Model;
     * - if $field contains some values, object is created and its state is set to 'instance', unless `state` is explicitly set (@see write method).
     *
     *
     * @param  string       $class        Class of the object to create.
     * @param  array        $fields       Associative array mapping each field to its assigned value.
     * @param  string       $lang         Language in which to store multilang fields.
     * @param  boolean      $use_draft    If set to false, disables the re-use of outdated drafts (objects created but not saved afterward).
     *
     * @return integer      The result is an identifier of the newly created object or, in case of error, the code of the error that was raised (by convention, error codes are negative integers).
     */
    public function create($class, $fields=null, $lang=null, $use_draft=true) {
        $res = 0;
        $lang = ($lang)?$lang:constant('DEFAULT_LANG');

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();
            // get static instance (checks that given class exists)
            $object = $this->getStaticInstance($class, $fields);
            // retrieve schema
            $schema = $object->getSchema();
            $table_name = $this->getObjectTableName($class);
            $special_fields = Model::getSpecialColumns();

            // 1) define default values
            $creation_array = [];
            foreach($special_fields as $field => $descr) {
                if(isset($object[$field])) {
                    $creation_array[$field] = $object[$field];
                }
            }

            // set creation array according to received fields: `id` and `creator` can be set to force resulting object
            if(!empty($fields)) {
                // use given id field as identifier, if any and valid
                if(isset($fields['id']) && is_numeric($fields['id'])) {
                    $creation_array['id'] = (int) $fields['id'];
                }
                // use given creator id, if any and valid
                if(isset($fields['creator']) && is_numeric($fields['creator'])) {
                    $creation_array['creator'] = (int) $fields['creator'];
                }
            }

            // 2) garbage collect: check for expired draft object

            // by default, request a new object ID
            $oid = 0;

            if(!isset($creation_array['id'])) {
                if($use_draft) {
                    // list ids of records having creation date older than DRAFT_VALIDITY
                    $ids = $this->search($class, [
                                ['state', '=', 'draft'],
                                // #todo - update search() so that we still use PHP timestamps at this stage
                                ['created', '<', date("Y-m-d H:i:s", time()-(3600*24*constant('DRAFT_VALIDITY')))]
                            ],
                            ['id' => 'asc']
                        );
                    if($ids > 0 && count($ids) && $ids[0] > 0) {
                        // use the oldest expired draft
                        $oid = $ids[0];
                        // store the id to reuse
                        $creation_array['id'] = $oid;
                        // and delete the associated record (might contain obsolete data)
                        $db->deleteRecords($table_name, array($oid));
                        trigger_error("ORM::found draft object in table $table_name with id $oid.", QN_REPORT_DEBUG);
                    }
                    else {
                        trigger_error("ORM::no reusable draft object found.", QN_REPORT_DEBUG);
                    }
                }
            }
            else {
                // check if there is an object with same id
                $records = $db->getRecords($table_name, 'id', (array) $creation_array['id']);
                if($db->fetchArray($records)) {
                    throw new Exception('duplicate_object_id', QN_ERROR_CONFLICT_OBJECT);
                }
                $oid = (int) $creation_array['id'];
            }

            // 3) create a new record with the found value, (if no id is given, the autoincrement will assign a value)
            $sql_values = [];
            /** @var \equal\data\adapt\DataAdapterProvider */
            $dap = $this->container->get('adapt');
            foreach($creation_array as $field => $value) {
                $adapter = $dap->get('sql');
                $f = new Field($schema[$field], $field);
                // adapt value to SQL
                $sql_values[$field] = $adapter->adaptOut($value, $f->getUsage());
            }
            $db->addRecords($table_name, array_keys($sql_values), [ array_values($sql_values) ]);

            if($oid <= 0) {
                // id field is auto-increment: retrieve last value
                $oid = $db->getLastId();
                if($oid <= 0) {
                    throw new Exception('invalid_object_id', QN_ERROR_UNKNOWN);
                }
                $this->cache[$table_name][$oid][$lang] = $creation_array;
            }
            // in any case, we return the object id
            $res = $oid;

            // 4) update new object with given fields values, if any

            // build creation array with actual object values (#memo - fields are mapped with PHP values, not SQL)
            $creation_array = array_merge( $creation_array, $object->getValues(), $fields );
            // request an object update (mark call as 'from_create')
            $res_w = $this->update($class, $oid, $creation_array, $lang, true);
            // if write method generated an error, return error code instead of object id
            if($res_w < 0) {
                $res = $res_w;
            }

            // 5) force computing 'instant' computed fields
            $map_instant_fields = [];

            foreach($schema as $field => $descriptor) {
                if($descriptor['type'] == 'computed' && ($descriptor['instant'] ?? false) == true) {
                    $map_instant_fields[$field] = true;
                }
            }
            // re-compute local 'instant' computed field, if any
            if(count($map_instant_fields)) {
                $this->load($class, (array) $oid, array_keys($map_instant_fields), $lang);
            }

            // 6) call 'oncreate' hook
            $this->callonce($class, 'oncreate', (array) $oid, $creation_array, $lang);

        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_WARNING);
            $this->last_error = $e->getMessage();
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * Alias for update()
     * @deprecated
     */
    public function write($class, $ids=null, $fields=null, $lang=null, $create=false) {
        return $this->update($class, $ids, $fields, $lang, $create);
    }

    /**
     * Updates specified fields of selected objects and stores changes into database.
     *
     * @param   string    $class        Class of the objects to write.
     * @param   mixed     $ids          Identifier(s) of the object(s) to update (accepted types: array, integer, numeric string).
     * @param   mixed     $fields       Array mapping fields names with the value (PHP) to which they must be set.
     * @param   string    $lang         Language under which fields have to be stored (only relevant for multilang fields).
     * @param   bool      $create       Flag to mark the call as originating from the create() method (disables the canupdate and events hooks call).
     *
     * @return  int|array Returns an array of updated ids, or an error identifier in case an error occurred.
     */
    public function update($class, $ids=null, $fields=null, $lang=null, $create=false) {
        // init result
        $res = [];
        $lang = ($lang)?$lang:constant('DEFAULT_LANG');

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();

            // 1) pre-processing - $ids sanitization

            // cast fields to an array (passing a single field is accepted)
            if(!is_array($fields)) {
                $fields = (array) $fields;
            }
            // keep only valid objects identifiers
            $ids = $this->sanitizeIdentifiers($ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) {
                trigger_error("ORM::ignoring call with empty ids ", QN_REPORT_INFO);
                return $res;
            }
            // ids that are left are the ones of the objects that will be written
            $res = $ids;


            // 2) pre-processing - $fields sanitization

            // get static instance (checks that given class exists)
            $object = $this->getStaticInstance($class);
            // retrieve schema
            $schema = $object->getSchema();
            // retrieve name of the DB table associated with the class
            $table_name = $this->getObjectTableName($class);
            // remove unknown fields and prevent updating reserved fields (id, creator, created)
            $fields = array_filter($fields, function($field) use ($schema) {
                        return isset($schema[$field]) && !in_array($field, ['id', 'creator', 'created']);
                    },
                    ARRAY_FILTER_USE_KEY
                );


            // 3) make sure objects in the collection can be updated
            // #memo - moved to Collection

            // #memo - writing an object does not change its state, unless when explicitly set in $fields
            $fields['modified'] = time();

            // 4) call 'onbeforeupdate' hook : notify objects that they're about to be updated with given values

            if(!$create) {
                if(method_exists($class, 'onbeforeupdate')) {
                    $this->callonce($class, 'onbeforeupdate', $ids, $fields, $lang);
                }
                else {
                    $this->callonce($class, 'onupdate', $ids, $fields, $lang);
                }
            }


            // 5) update objects

            // remember callbacks that are triggered by the update
            $onupdate_fields = [];
            $onrevert_fields = [];
            $instant_fields = [];

            // update internal buffer with given values
            foreach($ids as $oid) {
                foreach($fields as $field => $value) {
                    // remember fields whose modification triggers an onupdate event
                    // #memo - computed fields assigned to null are meant to be re-computed without triggering onupdate
                    if($schema[$field]['type'] != 'computed') {
                        if(isset($schema[$field]['onupdate'])) {
                            $onupdate_fields[] = $field;
                        }
                    }
                    else {
                        if(!is_null($value)) {
                            if(isset($schema[$field]['onupdate'])) {
                                $onupdate_fields[] = $field;
                            }
                        }
                        else {
                            if(isset($schema[$field]['onrevert'])) {
                                $onrevert_fields[] = $field;
                            }
                            if(isset($schema[$field]['instant']) && $schema[$field]['instant']) {
                                $instant_fields[$field] = true;
                            }
                        }
                    }
                    // assign cache to object values
                    $target_lang = $lang;
                    if($lang != constant('DEFAULT_LANG') && (!isset($schema[$field]['multilang']) || !$schema[$field]['multilang'])) {
                        // field is not multilang: make sure to assign the default lang
                        $target_lang = constant('DEFAULT_LANG');
                    }
                    $this->cache[$table_name][$oid][$target_lang][$field] = $value;
                }
            }


            // 6) write selected fields to DB

            $this->store($class, $ids, array_keys($fields), $lang);


            // 7) second pass : handle fields onupdate events, if any

            if(!$create || constant('ORM_EVENTS_FORCE_ONUPDATE_AT_CREATION')) {
                // #memo - this must be done after modifications otherwise object values might be outdated
                if(count($onupdate_fields)) {
                    // #memo - several onupdate callbacks can, in turn, trigger a same other callback, which must then be called as many times as necessary
                    foreach($onupdate_fields as $field) {
                        // run onupdate callback (ignore undefined methods)
                        $this->callonce($class, $schema[$field]['onupdate'], $ids, $fields, $lang);
                    }
                }
                if(count($onrevert_fields)) {
                    foreach($onrevert_fields as $field) {
                        // run onrevert callback (ignore undefined methods)
                        $this->callonce($class, $schema[$field]['onrevert'], $ids, $fields, $lang);
                    }
                }
            }


            // 8) handle the resetting of dependent computed fields

            // #memo - upon creation computed fields should remain to null: we want to avoid unnecessary computations at object creation
            if(!$create) {
                $dependents = [
                    'primary' => [],
                    'related' => []
                ];

                foreach($fields as $field => $value) {
                    // remember fields whose modification triggers resetting computed fields
                    // #todo - deprecate 'dependencies' : use dependents
                    if(isset($schema[$field]['dependencies'])) {
                        foreach((array) $schema[$field]['dependencies'] as $dependent) {
                            $dependents['primary'][$dependent] = true;
                        }
                    }

                    if(isset($schema[$field]['dependents'])) {
                        foreach((array) $schema[$field]['dependents'] as $key => $val) {
                            // handle array notation
                            if(!is_numeric($key)) {
                                if(!isset($dependents['related'][$key])) {
                                    $dependents['related'][$key] = [];
                                }
                                $dependents['related'][$key] = array_merge($dependents['related'][$key], (array) $val);
                            }
                            else {
                                $dependents['primary'][$val] = true;
                            }
                        }
                    }
                }

                // read all target fields at once
                $this->load($class, $ids, array_keys($dependents['related']), $lang);

                foreach($dependents['related'] as $field => $subfields) {
                    $values = [];
                    foreach($subfields as $subfield) {
                        $values[$subfield] = null;
                    }
                    foreach($ids as $oid) {
                        if(!isset($this->cache[$table_name][$oid][$lang][$field])) {
                            continue;
                        }
                        $target_ids = (array) $this->cache[$table_name][$oid][$lang][$field];
                        // allow cascade update (circular dependencies are checked in `core_test_package`)
                        $this->update($schema[$field]['foreign_object'], $target_ids, $values, $lang);
                    }
                }

                // handle fields that must be re-computed instantly
                $values = [];
                foreach(array_keys($dependents['primary']) as $field) {
                    if(isset($schema[$field]) && $schema[$field]['type'] == 'computed') {
                        if(isset($schema[$field]['instant']) && $schema[$field]['instant']) {
                            $instant_fields[$field] = true;
                        }
                        $values[$field] = null;
                    }
                }

                if(count($values)) {
                    // allow cascade update (circular dependencies are checked in `core_test_package`)
                    $this->update($class, $ids, $values, $lang);
                }

                if(count($instant_fields)) {
                    // re-compute local 'instant' computed field
                    $this->load($class, $ids, array_keys($instant_fields), $lang);
                }
            }


            // 9) handle automatic transitions

            foreach($ids as $object_id) {
                // for each object, we must retrieve the status field
                $transitions = $this->getTransitionCandidates($class, $object_id, array_keys($fields));
                foreach($transitions as $transition) {
                    $t_res = $this->transition($class, (array) $object_id, $transition);
                    // transition succeeded
                    if(count($t_res) == 0) {
                        // there should be only one applicable transition: process next object
                        break;
                    }
                }
            }

            if(!$create) {
                $this->callonce($class, 'onafterupdate', $ids, $fields, $lang);
            }


            // 10) upon state update (to 'archived' or 'deleted'), remove any pending alert related to the object
            // #todo - move this to a dedicated controller for CRON

            if(isset($fields['state']) && !in_array($fields['state'], ['draft', 'instance'])) {
                $messages_ids = $this->search('core\alert\Message', [ ['object_class', '=', $class], ['object_id', 'in', $ids] ] );
                if($messages_ids) {
                    $this->delete('core\alert\Message', $messages_ids, true);
                }
            }

        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            $this->last_error = $e->getMessage();
            $res = $e->getCode();
        }

        return $res;
    }

    /**
     * Reads a collection of objects from a given class, based on a list of identifiers.
     *
     * @param   string     $class       Class of the objects to retrieve.
     * @param   mixed      $ids         Identifier(s) of the object(s) to retrieve (accepted types: array, integer, string).
     * @param   mixed      $fields      Name(s) of the field(s) to retrieve (accepted types: array, string).
     * @param   string     $lang        Language under which return fields values (only relevant for multilang fields).
     *
     * @return  int|Model[]  Returns an associative array mapping Model instances for each requested id ($ids order is maintained). If an error occurs, the ID of the raised error is returned.
     */
    public function read($class, $ids=null, $fields=null, $lang=null) {
        // init result
        $res = [];
        $lang = ($lang)?$lang:constant('DEFAULT_LANG');

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();

            // 1) pre-processing: params sanitization

            // cast fields to an array (passing a single field is accepted)
            // #memo - duplicate fields are allowed in $fields array: the value will be loaded once and returned as many times as requested
            if(!is_array($fields)) {
                $fields = (array) $fields;
            }

            // get static instance (check that given class exists)
            /** @var \equal\orm\Model */
            $model = $this->getStaticInstance($class);
            $schema = $model->getSchema();
            // retrieve name of the DB table associated with the class
            $table_name = $this->getObjectTableName($class);
            // keep only valid objects identifiers
            $ids = $this->sanitizeIdentifiers($ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) {
                return $res;
            }
            // init resulting array
            foreach($ids as $oid) {
                // #memo - $model is a Model instance with default values
                $res[$oid] = clone $model;
                // discard fields that weren't requested
                foreach($schema as $field => $descriptor) {
                    if(!in_array($field, $fields)) {
                        unset($res[$oid][$field]);
                    }
                }
            }

            // 2) pre-processing: $fields preparation

            $requested_fields = [];
            $dot_fields = [];

            // check fields validity
            foreach($fields as $key => $field) {
                // handle fields with 'dot' notation
                if(strpos($field, '.') > 0) {
                    $dot_fields[] = $field;
                    // drop dot fields (they are handled in dedicated step)
                    unset($fields[$key]);
                }
                // invalid field
                elseif(!isset($schema[$field])) {
                    // drop invalid fields
                    unset($fields[$key]);
                    trigger_error("ORM::unknown field '$field' for class : '$class'", QN_REPORT_WARNING);
                }
                else {
                    // handle aliases
                    while($schema[$field]['type'] == 'alias') {
                        $field = $schema[$field]['alias'];
                    }
                    $requested_fields[] = $field;
                }
            }

            // #memo - this is necessary when non-ACL policies are set, otherwise it is redundant with access::isAllowed(R_READ)
            /*
            // #moved to Collection
            $canread = $this->callonce($class, 'canread', $ids, $requested_fields, $lang);
            if($canread > 0 && !empty($canread)) {
                throw new \Exception(serialize($canread), QN_ERROR_NOT_ALLOWED);
            }
            */

            // 3) check, amongst requested fields, which ones are not yet present in the internal buffer

            if(count($requested_fields)) {
                // if internal buffer is empty, query the DB to load all fields from requested objects
                if(empty($this->cache) || !isset($this->cache[$table_name])) {
                    $this->load($class, $ids, $requested_fields, $lang);
                }
                // check if all objects are fully loaded
                else {
                    // build the missing fields array by increment
                    // we need the 'broadest' fields array to be loaded to minimize # of SQL queries
                    $fields_missing = array();
                    foreach($requested_fields as $key => $field) {
                        foreach($ids as $oid) {
                            // prevent using cache for one2many fields : cache can become inconsistent in case of cross updates
                            // #todo - use the cache for m2m relations (using the rel table)
                            if(in_array($schema[$field]['type'], ['one2many', 'many2many']) || !isset($this->cache[$table_name][$oid][$lang][$field])) {
                                $fields_missing[] = $field;
                                break;
                            }
                        }
                    }
                    if(!empty($fields_missing)) {
                        $this->load($class, $ids, $fields_missing, $lang);
                    }
                }

                // 4) build result by reading from internal buffer

                foreach($ids as $oid) {
                    if(!isset($this->cache[$table_name][$oid]) || empty($this->cache[$table_name][$oid])) {
                        trigger_error("ORM::unknown or empty object $class [$oid]", QN_REPORT_WARNING);
                        continue;
                    }
                    // first pass : retrieve fields values
                    foreach($fields as $key => $field) {
                        // handle aliases
                        $target_field = $field;
                        while($schema[$target_field]['type'] == 'alias') {
                            $target_field = $schema[$target_field]['alias'];
                        }
                        // use final notation
                        $res[$oid][$field] = isset($this->cache[$table_name][$oid][$lang][$target_field]) ? $this->cache[$table_name][$oid][$lang][$target_field] : null;
                    }
                }
            }

            // 5) handle dot fields

            // create a map associating fields with all subfields
            $map_dot_fields = [];
            foreach($dot_fields as $path) {
                // extract sub field and remainder
                $parts = explode('.', $path, 2);
                // left side of the first dot
                $field = $parts[0];
                if(!isset($map_dot_fields[$field])) {
                    $map_dot_fields[$field] = [];
                }
                $map_dot_fields[$field][] = $parts[1];
            }

            // read all dot fields at once
            $this->load($class, $ids, array_keys($map_dot_fields), $lang);

            // recursively read sub objects
            foreach($map_dot_fields as $field => $sub_fields) {
                foreach($sub_fields as $sub_path) {
                    // retrieve final type of targeted field
                    $f = $model->getField($field);
                    $descriptor = $f->getDescriptor();
                    if(!$descriptor || !isset($descriptor['result_type']) || !isset($descriptor['foreign_object'])) {
                        // ignore invalid descriptors
                        continue;
                    }
                    $field_type = $descriptor['result_type'];
                    if(!$field_type || !in_array($field_type, ['many2one', 'one2many', 'many2many'])) {
                        // ignore non-relational fields
                        continue;
                    }
                    // recursively read sub objects for each object
                    // #todo - this could be improved by loading all targeted objects for current collection at once
                    foreach($ids as $oid) {
                        // #memo - for unreachable values, m2o are always set to null, and m2m or o2m to an empty array
                        $sub_ids = $this->cache[$table_name][$oid][$lang][$field];
                        if(isset($sub_ids)) {
                            $sub_values = $this->read($descriptor['foreign_object'], (array) $sub_ids, (array) $sub_path, $lang);
                            if($sub_values <= 0 || !count($sub_values)) {
                                continue;
                            }
                            if($field_type == 'many2one') {
                                $odata = reset($sub_values);
                                if(is_array($odata) || is_a($odata, Model::getType())) {
                                    $res[$oid][$field.'.'.$sub_path] = $odata[$sub_path];
                                }
                            }
                            else {
                                $res[$oid][$field.'.'.$sub_path] = $sub_values;
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            $res = $e->getCode();
        }
        return $res;
    }


    /**
     * Alias for delete()
     * @deprecated
     */
    public function remove($class, $ids, $permanent=false) {
        return $this->delete($class, $ids, $permanent);
    }


    /**
     * Deletes an object permanently or put it in the "trash bin" (i.e. setting the 'deleted' flag to 1).
     *
     * @param   string  $class          Class of the object to delete.
     * @param   array   $ids            Array of ids of the objects to delete.
     * @param   boolean $permanent      Flag for soft deleted (marked as deleted) or hard deletion (removed from DB).
     *
     * @return  integer|array   Returns a list of ids of deleted objects, or an error identifier in case an error occurred.
     */
    public function delete($class, $ids, $permanent=false) {
        $res = [];

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();

            // 1) pre-processing

            // keep only valid objects identifiers
            $ids = $this->sanitizeIdentifiers($ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) return $res;
            // ids that are left are the ones of the objects that will be (marked as) deleted
            $res = $ids;
            // retrieve targeted Model
            $model = $this->getStaticInstance($class);
            $schema = $model->getSchema();
            $table_name = $this->getObjectTableName($class);

            // 2) make sure objects in the collection can be deleted

            /*
            // #moved to Collection
            $candelete = $this->call($class, 'candelete', $ids, [], null, ['ids']);
            if(!empty($candelete)) {
                throw new \Exception(serialize($candelete), QN_ERROR_NOT_ALLOWED);
            }
            */

            // 3) call 'ondelete' hook : notify objects that they're about to be deleted

            if(method_exists($class, 'onbeforedelete')) {
                $this->callonce($class, 'onbeforedelete', $ids);
            }
            else {
                $this->callonce($class, 'ondelete', $ids);
            }

            // 4) cascade deletions / relations updates

            foreach($schema as $field => $def) {
                if(in_array($def['type'], ['file', 'one2many', 'many2many'])) {
                    switch($def['type']) {
                    case 'file':
                        if(constant('FILE_STORAGE_MODE') == 'FS') {
                            // build a unique name  (package/class/field/oid.lang)
                            $path = sprintf("%s/%s", str_replace('\\', '/', $class), $field);
                            $storage_location = realpath(QN_BASEDIR.'/bin').'/'.$path;
                            foreach($ids as $oid) {
                                // remove binary for all langs
                                foreach (glob(sprintf("$storage_location/%011d.*", $oid)) as $filename) {
                                    unlink($filename);
                                }
                            }
                        }
                        break;
                    case 'one2many':
                        $rel_res = $this->read($class, $ids, $field);
                        $rel_ids = [];
                        array_map(function ($item) use($field, &$rel_ids) { $rel_ids = array_merge($rel_ids, $item[$field]);}, $rel_res);
                        if(isset($def['foreign_object'])) {
                            // foreign field is many2one
                            $rel_schema = $this->getObjectSchema($def['foreign_object']);
                            // call ondelete method when defined (to allow cascade deletion)
                            if(isset($rel_schema[$def['foreign_field']]) && isset($rel_schema[$def['foreign_field']]['ondelete'])) {
                                $call_res = $this->callonce($class, $rel_schema[$def['foreign_field']]['ondelete'], $rel_ids);
                                // for unknown method, check special keywords 'cascade' and 'null'
                                if($call_res < 0) {
                                    switch($rel_schema[$def['foreign_field']]['ondelete']) {
                                        case 'cascade':
                                            $this->delete($def['foreign_object'], $rel_ids, $permanent);
                                            break;
                                        case 'null':
                                        default:
                                            $this->update($def['foreign_object'], $rel_ids, [$def['foreign_field'] => null]);
                                            break;
                                    }
                                }
                            }
                        }
                        break;
                    case 'many2many':
                        // delete * from $def['rel_table'] where $def['rel_local_key'] in $ids
                        $this->db->deleteRecords(
                            $def['rel_table'],
                            $ids,
                            null,
                            $schema[$field]['rel_local_key']
                        );
                        break;
                    }
                }
            }

            // 5) remove object

            // soft deletion
            if (!$permanent) {
                $db->setRecords($table_name, $ids, ['deleted' => 1]);
            }
            // hard deletion
            else {
                // delete targeted objects
                $db->deleteRecords($table_name, $ids);

                // remove translations, if any
                $this->db->deleteRecords(
                        'core_translation',
                        $ids, [
                            [
                                ['object_class', '=', $class]
                            ]
                        ],
                        'object_id'
                    );
            }

            // remove objects from cache
            foreach($ids as $oid) {
                unset($this->cache[$table_name][$oid]);
            }

            // 4) call 'onafterdelete' hook

            $this->callonce($class, 'onafterdelete', $ids);
        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            $this->last_error = $e->getMessage();
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * Create a recursive copy of an object.
     * This method does not check unique constraints. If creation fails, it returns an error code.
     *
     * @param   string    $class            Class name of the object to clone.
     * @param   integer   $id               Unique identifier of the object to clone.
     * @param   array     $values           Map of fields to override original object values.
     * @param   string    $lang             Language under which return fields values (only relevant for multilang fields).
     * @param   string    $parent_field     Name of foreign_field from parent to prevent losing relation with new children.
     *
     * @return  int|array  Error code OR resulting associative array.
     */
    // #todo - use same signature than other CRUD
    // #todo - when cloning, all multilang values should be duplicated as well
    public function clone($class, $id, $values=[], $lang=null, $parent_field='') {
        $res = 0;
        $lang = ($lang)?$lang:constant('DEFAULT_LANG');

        try {
            $object = $this->getStaticInstance($class);
            $schema = $object->getSchema();

            // read full object
            $res_r = $this->read($class, $id, array_keys($schema), $lang);

            // if write method generated an error, return error code instead of object id
            if($res_r < 0) {
                throw new Exception('object_not_found', $res_r);
            }

            $original = $res_r[$id];
            $new_values = [];

            // unset relations + id and parent_field (needs to be updated + could be part of unique constraint)
            foreach($original as $field => $value) {
                $def = $schema[$field];
                if(!in_array($def['type'], ['one2many', 'many2many']) && !in_array($field, ['id', $parent_field])) {
                    if(isset($values[$field])) {
                        $new_values[$field] = $values[$field];
                    }
                    else {
                        $new_values[$field] = $value;
                    }
                }
            }

            // create new object based on original
            $res_c = $this->create($class, $new_values, $lang);
            if($res_c < 0) {
                throw new Exception('creation_aborted', $res_c);
            }

            // assign result to returned ID
            $res = $res_c;

            // handle cascade cloning
            foreach($schema as $field => $def) {
                if($def['type'] == 'one2many' && isset($def['foreign_object'])) {
                    $rel_schema = $this->getObjectSchema($def['foreign_object']);
                    $rel_ids = $original[$field];
                    $new_rel_ids = [];

                    if(isset($rel_schema[$def['foreign_field']]) ) {
                        foreach($rel_ids as $oid) {
                            // perform cascade cloning (clone target objects)
                            $res_c = $this->clone($def['foreign_object'], $oid, [], $lang, $def['foreign_field']);
                            if($res_c > 0) {
                                $new_rel_ids[] = $res_c;
                            }
                        }
                        // set current clone as target for the relation
                        $rel_values = [ $def['foreign_field'] => $res ];
                        if(isset($values['creator'])) {
                            $rel_values['creator'] = $values['creator'];
                        }
                        $this->update($def['foreign_object'], $new_rel_ids, $rel_values, $lang);
                    }
                }
            }

            // call 'onclone' hook
            $this->callonce($class, 'onclone', (array) $id, [], $lang, ['ids']);

        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            $this->last_error = $e->getMessage();
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * Increment the field of an object by a given increment (integer value).
     *
     * note : We use this nomenclature because it has a recognized semantics and equivalence in many programming languages.
     *
     * @param   string    $class            Class name of the object to clone.
     * @param   array     $ids              Array of ids of the objects to update.
     * @param   string    $field            Name of the field to increment (must have a numeric type).
     * @param   integer   $increment        Value by witch increment the field (positive or negative).
     *
     * @return  int|array Returns an array of updated ids, or an error identifier in case an error occurred.
     */
    public function fetchAndAdd($class, $ids, $field, $increment) {
        $result = [];

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();
            // get static instance (checks that given class exists)
            $object = $this->getStaticInstance($class);
            // retrieve schema
            $schema = $object->getSchema();
            // make sure that the targeted field exists and has a numeric type
            if(!isset($schema[$field])) {
                throw new Exception('unknown_field', EQ_ERROR_INVALID_PARAM);
            }
            if(!in_array($schema[$field]['type'], ['integer', 'float'])) {
                throw new Exception('non_numeric_field', EQ_ERROR_INVALID_PARAM);
            }
            $table_name = $this->getObjectTableName($class);
            // increment the field as an atomic operation
            $res = $db->incRecords($table_name, (array) $ids, $field, $increment);
            while($row = $db->fetchArray($res)) {
                // maintain ids order provided by the SQL sort
                $result[$row['id']] = $row[$field];
            }
        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_WARNING);
            $this->last_error = $e->getMessage();
            $result = $e->getCode();
        }
        return $result;
    }

    /**
     * Returns applicable transitions based on a list of updated fields, according to the dependencies defined in the related workflow descriptors.
     * If no workflow is defined for the given class, an empty array is returned.
     *
     * @param string    $class
     * @param int[]     $id         Identifier of the object for which applicable transitions are requested (transitions depend on individual status).
     * @param string[]  $fields     List of field names.
     * @return array    Returns a list of transition names (id) that must be tested in case one or more fields amongst the $fields array is updated.
     */
    private function getTransitionCandidates($class, $id, $fields) {
        /** @var array */
        $res = [];
        $model = $this->getStaticInstance($class);
        $schema = $model->getSchema();
        if(!isset($schema['status'])) {
            return $res;
        }
        $workflow = $model->getWorkflow();
        $objects = $this->read($class, (array) $id, ['status']);
        if($objects > 0 && count($objects)) {
            $object = reset($objects);
            if(isset($object['status']) && isset($workflow[$object['status']]) && isset($workflow[$object['status']]['transitions'])) {
                foreach($workflow[$object['status']]['transitions'] as $t_name => $t_descr) {
                    if(isset($t_descr['watch']) && count(array_intersect($fields, $t_descr['watch'])) > 0 ) {
                        $res[] = $t_name;
                    }
                }
            }
        }
        return $res;
    }

    public function canTransition($class, $ids, $transition) {
        /** @var array */
        $res = [];
        $model = $this->getStaticInstance($class);
        $schema = $model->getSchema();
        // ignore models that do not have a status field
        if(!isset($schema['status'])) {
            trigger_error("ORM::invalid transition request on entity '$class' without status field.", QN_REPORT_WARNING);
            return $res;
        }
        $workflow = $model->getWorkflow();
        $objects = $this->read($class, $ids, ['status']);
        foreach($objects as $id => $object) {
            // ignore faulty objects
            if(!isset($object['status'])) {
                $res = ['invalid_object' => "Object {$id} is missing a status value."];
                break;
            }
            $match = false;
            if(isset($workflow[$object['status']]) && isset($workflow[$object['status']]['transitions'])) {
                foreach($workflow[$object['status']]['transitions'] as $t_name => $t_descr) {
                    if($t_name == $transition) {
                        $match = true;
                        if(isset($t_descr['domain'])) {
                            $domain = new Domain($t_descr['domain']);
                            $fields = $domain->extractFields();
                            $data = $this->read($class, $id, $fields);
                            $object = reset($data);
                            if(!$domain->evaluate($object)) {
                                $res = ['broken_constraint' => "Object state does not comply with the constraints of transition '$transition'."];
                                break 2;
                            }
                        }
                        if(isset($t_descr['policies'])) {
                            $access = $this->container->get('access');
                            $auth = $this->container->get('auth');
                            $user_id = $auth->userId();
                            foreach($t_descr['policies'] as $policy) {
                                $res = $access->isCompliant($user_id, $policy, $class, $ids);
                                if(count($res)) {
                                    break 2;
                                }
                            }
                        }
                        break;
                    }
                }
            }
            if(!$match) {
                $res = ['invalid_transition' => "No transition '$transition' from status '{$object['status']}' defined in {$class} workflow."];
                break;
            }
        }
        return $res;
    }

    /**
     * Attempts to apply a specific transition to a series of objects.
     * If no workflow is defined, the call is ignored (no action is taken).
     * If there is no match or if there are some conditions on the transition that are not met, it returns an error code.
     *
     * @param   string      $class            Class name of the object to clone.
     * @param   array       $ids              Array of ids of the objects to delete.
     * @param   string      $transition       Name of the requested workflow transition (signal).
     *
     * @return  array           Returns an associative array containing invalid fields with their associated error_message_id.
     *                          An empty array means all fields are valid. In case of error, the method returns a negative integer.
     */
    public function transition($class, $ids, $transition) {
        /** @var array */
        $res = $this->canTransition($class, $ids, $transition);
        // stop upon errors (all-or-nothing behavior)
        if(count($res)) {
            return $res;
        }
        $table_name = $this->getObjectTableName($class);
        $model = $this->getStaticInstance($class);
        $workflow = $model->getWorkflow();
        $lang = constant('DEFAULT_LANG');
        // read status field for retrieved objects
        $objects = $this->read($class, $ids, ['status']);
        foreach($objects as $id => $object) {
            // reaching this part means all objects have a status and a workflow in which given transition is defined and valid for requested mutation
            $t_descr = $workflow[$object['status']]['transitions'][$transition];
            // if a 'onbefore' method is defined for applied transition, call it
            if(isset($t_descr['onbefore'])) {
                $this->callonce($class, $t_descr['onbefore'], $id);
            }
            // status field is always writeable (we don't call `update()` to bypass checks)
            $this->cache[$table_name][$id][$lang]['status'] = $t_descr['status'];
            $this->store($class, (array) $id, ['status'], $lang);
            // if a 'onafter' method is defined for applied transition, call it
            if(isset($t_descr['onafter'])) {
                $this->callonce($class, $t_descr['onafter'], $id);
            }
        }
        return [];
    }

    /**
     * Search for the objects that comply with the domain (series of criteria).
     *
     * The domain syntax is : array( array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]]) [, array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]])])
     * Array of several series of clauses joined by logical ANDs themselves joined by logical ORs : disjunctions of conjunctions
     * i.e.: (clause[, AND clause [, AND ...]]) [ OR (clause[, AND clause [, AND ...]]) [ OR ...]]
     *
     * Accepted operators are : '=', '<', '>',' <=', '>=', '<>', 'like' (case-sensitive), 'ilike' (case-insensitive), 'in', 'contains'
     * Example : array( array( array('title', 'like', '%foo%'), array('id', 'in', array(1,2,18)) ) )
     *
     * @param   string     $class       Class of the objects to search for.
     * @param   array      $domain      Domain (disjunction of conjunctions) defining the criteria the objects have to match.
     * @param   array      $sort        Associative array mapping fields and orders on which result have to be sorted.
     * @param   integer    $start       The offset at which to start the segment of the list of matching objects.
     * @param   string     $limit       The maximum number of results/identifiers to return.
     *
     * @return  integer|array   Returns an array of matching objects ids sorted according to the $sort param, or a negative integer in case of error.
     */
    public function search($class, $domain=null, $sort=['id' => 'asc'], $start='0', $limit='0', $lang=null) {
        $result = [];
        $lang = ($lang)?$lang:constant('DEFAULT_LANG');

        try {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDbHandler();

            if(empty($sort)) {
                throw new Exception("sorting order field cannot be empty", QN_ERROR_MISSING_PARAM);
            }

            // check and fix domain format
            if($domain && !is_array($domain)) {
                throw new Exception("if domain is specified, it must be an array", QN_ERROR_INVALID_PARAM);
            }
            elseif($domain) {
                // valid format : [[['field', 'operator', 'value']]]
                // accepted shortcuts: [['field', 'operator', 'value']], ['field', 'operator', 'value']
                if( !is_array($domain[0]) ) {
                    $domain = array(array($domain));
                }
                elseif( isset($domain[0][0]) && !is_array($domain[0][0]) ) {
                    $domain = array($domain);
                }
            }

            $conditions = [[]];
            // join conditions that have to be additionally applied to all clauses
            $join_conditions = [];
            $tables = [];

            $table_name = $this->getObjectTableName($class);

            // use nested closure to store original table names and return corresponding aliases
            $add_table = function ($table_name) use (&$tables) {
                if(in_array($table_name, $tables)) {
                    return array_search($table_name, $tables);
                }
                $table_alias = 't'.count($tables);
                $tables[$table_alias] = $table_name;
                return $table_alias;
            };

            $schema = $this->getObjectSchema($class);
            $table_alias = $add_table($table_name);

            // first pass : build conditions and the tables names arrays
            if(!empty($domain) && !empty($domain[0]) && !empty($domain[0][0])) { // domain structure is correct and contains at least one condition

                // we check, for each clause, if it's about a "special field"
                $special_fields = Model::getSpecialColumns();

                for($j = 0, $max_j = count($domain); $j < $max_j; ++$j) {
                    // #todo : join conditions should be set at clause level (but at some history point it was set at domain level) - to confirm
                    $join_conditions = [];

                    for($i = 0, $max_i = count($domain[$j]); $i < $max_i; ++$i) {
                        if(!isset($domain[$j][$i]) || !is_array($domain[$j][$i])) {
                            throw new Exception("malformed domain", QN_ERROR_INVALID_PARAM);
                        }
                        if(!isset($domain[$j][$i][0]) || !isset($domain[$j][$i][1])) {
                            throw new Exception("invalid domain, a mandatory attribute is missing", QN_ERROR_INVALID_PARAM);
                        }
                        $field    = $domain[$j][$i][0];
                        $value    = (isset($domain[$j][$i][2])) ? $domain[$j][$i][2] : null;
                        $operator = strtolower($domain[$j][$i][1]);

                        // force operator 'is' for null values
                        if(is_null($value) || $value === 'null') {
                            if( $operator == '=') {
                                $operator = 'is';
                            }
                            elseif( $operator == '<>') {
                                $operator = 'is not';
                            }
                        }

                        // check field validity
                        if(!in_array($field, array_keys($schema))) {
                            throw new Exception("invalid domain, unknown field '$field' for object '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        // get final target field
                        while($schema[$field]['type'] == 'alias') {
                            $field = $schema[$field]['alias'];
                            if(!in_array($field, array_keys($schema))) {
                                throw new Exception("invalid schema, unknown field '$field' for object '$class'", QN_ERROR_INVALID_PARAM);
                            }
                        }
                        // get final type
                        $type = $schema[$field]['type'];
                        if($type == 'computed') {
                            if(!isset($schema[$field]['result_type'])) {
                                throw new Exception("invalid schema, missing result_type for field '$field' of object '$class'", QN_ERROR_INVALID_PARAM);
                            }
                            $type = $schema[$field]['result_type'];
                        }
                        // check the validity of the field name and the operator
                        if(!self::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) {
                            throw new Exception("missing at least one mandatory parameter for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        if(!in_array($operator, self::$valid_operators[$type])) {
                            throw new Exception("invalid domain, unknown operator '$operator' for field '$field' of type '{$schema[$field]['type']}' (result type: $type) in object '$class'", QN_ERROR_INVALID_PARAM);
                        }

                        // remember special fields involved in the domain (by removing them from the special_fields list)
                        if(isset($special_fields[$field])) {
                            unset($special_fields[$field]);
                        }

                        // note: we don't test user permissions on foreign objects here
                        switch($type) {
                            case 'many2one':
                                // use operator '=' instead of 'contains' (which is not sql standard)
                                if($operator == 'contains') {
                                    $operator = '=';
                                }
                                $field = $table_alias.'.'.$field;
                                break;
                            case 'one2many':
                                // add foreign table to sql query
                                $foreign_table_alias = $add_table($this->getObjectTableName($schema[$field]['foreign_object']));
                                // add the join condition
                                $join_conditions[] = array($foreign_table_alias.'.'.$schema[$field]['foreign_field'], '=', '`'.$table_alias.'`.`id`');
                                // as comparison field, use foreign table's 'foreign_key' if any, 'id' otherwise
                                if(isset($schema[$field]['foreign_key'])) {
                                    $field = $foreign_table_alias.'.'.$schema[$field]['foreign_key'];
                                }
                                else {
                                    $field = $foreign_table_alias.'.id';
                                }
                                // use operator 'in' instead of 'contains' (which is not sql standard)
                                if($operator == 'contains') {
                                    $operator = 'in';
                                }
                                break;
                            case 'many2many':
                                // add related table to sql query
                                $rel_table_alias = $add_table($schema[$field]['rel_table']);
                                // if the relation points out to objects of the same class
                                if($schema[$field]['foreign_object'] == $class) {
                                    // add the join condition on 'rel_foreign_key'
                                    $join_conditions[] = array($table_alias.'.id', '=', '`'.$rel_table_alias.'`.`'.$schema[$field]['rel_foreign_key'].'`');
                                    // use 'rel_local_key' column as comparison field
                                    $field = $rel_table_alias.'.'.$schema[$field]['rel_local_key'];
                                }
                                else {
                                    // add the join condition on 'rel_local_key'
                                    $join_conditions[] = array($table_alias.'.id', '=', '`'.$rel_table_alias.'`.`'.$schema[$field]['rel_local_key'].'`');
                                    // use 'rel_foreign_key' column as comparison field
                                    $field = $rel_table_alias.'.'.$schema[$field]['rel_foreign_key'];
                                }
                                // use operator 'in' instead of 'contains' (which is not sql standard)
                                if($operator == 'contains') {
                                    $operator = 'in';
                                }
                                break;
                            default:
                                // adapt value
                                if(!is_array($value)) {
                                    /** @var \equal\data\adapt\DataAdapterProvider */
                                    $dap = $this->container->get('adapt');
                                    /** @var \equal\data\adapt\DataAdapter */
                                    $adapterSql = $dap->get('sql');
                                    /** @var \equal\data\adapt\DataAdapter */
                                    $adapterJson = $dap->get('json');
                                    $f = new Field($schema[$field], $field);
                                    // #todo - json to php conversion should be done elsewhere (at this stage, we should be dealing with PHP values only)
                                    // 2023-04 - do we still need this ?
                                    $value = $adapterJson->adaptIn($value, $f->getUsage());
                                    // adapt value to SQL
                                    $value = $adapterSql->adaptOut($value, $f->getUsage());
                                }
                                // add some conditions if field is multilang (and the search is made on another language than the default one)
                                if($lang != constant('DEFAULT_LANG') && isset($schema[$field]['multilang']) && $schema[$field]['multilang']) {
                                    $translation_table_alias = $add_table('core_translation');
                                    // add join conditions
                                    $conditions[$j][] = array($table_alias.'.id', '=', '`'.$translation_table_alias.'.object_id`');
                                    $conditions[$j][] = array($translation_table_alias.'.object_class', '=', $class);
                                    $conditions[$j][] = array($translation_table_alias.'.object_field', '=', $field);
                                    $field = $translation_table_alias.'.value';
                                }
                                // simple fields always match table fields
                                else {
                                    $field = $table_alias.'.'.$field;
                                }
                                break;
                        }
                        // handle particular cases involving arrays
                        if( in_array($operator, ['in', 'not in']) ) {
                            if(!is_array($value)) {
                                $value = array($value);
                            }
                            if(!count($value)) {
                                $value = ['0'];
                            }
                        }
                        $conditions[$j][] = [$field, $operator, $value];
                    }
                    // search only among non-draft and non-deleted records
                    // (unless at least one clause was related to those fields - and consequently corresponding key in array $special_fields has been unset in the code above)
                    if(isset($special_fields['state'])) {
                        $conditions[$j][] = array($table_alias.'.state', '=', 'instance');
                    }
                    if(isset($special_fields['deleted'])) {
                        $conditions[$j][] = array($table_alias.'.deleted', '=', '0');
                    }
                    // add join conditions to current clause
                    foreach($join_conditions as $join_condition) {
                        $conditions[$j][] = $join_condition;
                    }
                }
                /*
                // #todo - confirm (see above)
                // add joins conditions to all clauses
                foreach($conditions as $i => $condition) {
                    foreach($join_conditions as $join_condition) {
                        $conditions[$i][] = $join_condition;
                    }
                }
                */

            }
            else { // no domain is specified
                // search only amongst non-draft and non-deleted records
                $conditions[0][] = array($table_alias.'.state', '=', 'instance');
                $conditions[0][] = array($table_alias.'.deleted', '=', '0');
            }

            // second pass : fetch the ids of matching objects
            $select_fields = array($table_alias.'.id');
            $order_table_alias = $table_alias;
            // build the ordering clause
            $order_clause = [];
            if(!is_array($sort)) {
                $sort = (array) $sort;
            }

            // if invalid order field is given, fallback to 'id'
            foreach($sort as $sort_field => $sort_order) {
                if(!isset($schema[$sort_field]) || ( $schema[$sort_field]['type'] == 'computed' && (!isset($schema[$sort_field]['store']) || !$schema[$sort_field]['store']) )) {
                    trigger_error("ORM::invalid order field '$sort_field' for class '$class'", QN_REPORT_WARNING);
                    // $order = 'id';
                    $sort_field = 'id';
                    $sort_order = 'asc';
                }
                while($schema[$sort_field]['type'] == 'alias') {
                    $sort_field = $schema[$sort_field]['alias'];
                }
                // we might need to request more than the id field (for example, for sorting purpose)
                if($lang != constant('DEFAULT_LANG') && isset($schema[$sort_field]['multilang']) && $schema[$sort_field]['multilang']) {
                    // #todo : validate this code (we should probably add join conditions in some cases)
                    $translation_table_alias = $add_table('core_translation');
                    $select_fields[] = $translation_table_alias.'.value';
                    $order_table_alias = $translation_table_alias;
                    $sort_field = 'value';
                }
                elseif($sort_field != 'id') {
                    // check the type of the field used for sorting : if it is a many2one, add the related table and use the field `name` instead of the id value
                    if($schema[$sort_field]['type'] == 'many2one' || (isset($schema[$sort_field]['result_type']) && $schema[$sort_field]['result_type'] == 'many2one') ) {
                        $related_table = $this->getObjectTableName($schema[$sort_field]['foreign_object']);
                        $related_table_alias = $add_table($related_table);
                        $related_schema = $this->getObjectSchema($schema[$sort_field]['foreign_object']);
                        // #todo - shouldn't this condition be added to all clauses?
                        $conditions[0][] = array($table_alias.'.'.$sort_field, '=', '`'.$related_table_alias.'.id'.'`');
                        $order_table_alias = $related_table_alias;
                        $sort_field = 'name';
                        while($related_schema[$sort_field]['type'] == 'alias') {
                            $sort_field = $related_schema[$sort_field]['alias'];
                        }
                        $select_fields[] = $related_table_alias.'.'.$sort_field;
                    }
                    else {
                        $order_table_alias = $table_alias;
                        $select_fields[] = $table_alias.'.'.$sort_field;
                    }
                }
                $order_clause[$order_table_alias.'.'.$sort_field] = $sort_order;
            }
            // make sure id field is amongst the sort fields
            if(!in_array('id', array_keys($sort))) {
                $order_clause[$table_alias.'.id'] = 'ASC';
            }
            // get the matching records by generating the resulting SQL query
            $res = $db->getRecords($tables, $select_fields, null, $conditions, $table_alias.'.id', $order_clause, $start, $limit);
            while ($row = $db->fetchArray($res)) {
                // maintain ids order provided by the SQL sort
                $result[] = $row['id'];
            }
            // remove duplicates, if any
            $result = array_unique($result);
        }
        catch(Exception $e) {
            trigger_error("ORM::".$e->getMessage(), QN_REPORT_ERROR);
            $this->last_error = $e->getMessage();
            $result = $e->getCode();
        }
        return $result;
    }
}
