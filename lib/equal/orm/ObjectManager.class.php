<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\organic\Service;
use equal\db\DBConnection;
use equal\data\DataValidator;

use \Exception as Exception;



class ObjectManager extends Service {
    /* Buffer for storing objects values as they are loaded.
    *  Structure is defined this way: `$cache[$class][$oid][$lang][$field]`
    *  Only methods load() and write() update its values.
    */
    private $cache;

    /* Array for keeeping track of identifiers matching actual objects
    *  Structure is defined this way: `$identifiers[$object_class][$object_id] = true;`
    */
    private $identifiers;

    /* Array holding static instances (i.e. one object of each class having fields set to default values)
    */
    private $instances;

    /* Array holding names of defined packages (folders under /package directory)
    */
    private $packages;

    /* Instance to a DBConnection Object
    */
    private $db;

    public static $virtual_types = array('alias');
    public static $simple_types	 = array('boolean', 'integer', 'float', 'string', 'text', 'date', 'time', 'datetime', 'file', 'binary', 'many2one');
    public static $complex_types = array('one2many', 'many2many', 'computed');

    public static $valid_attributes = array(
        'alias'		    => array('description', 'type', 'visible', 'default', 'usage', 'alias', 'required'),
        'boolean'		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange'),
        'integer'		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'selection', 'unique'),
        'float'			=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'selection', 'precision'),
        'string'		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'multilang', 'selection', 'unique'),
        'text'			=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'multilang'),
        'date'			=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange'),
        'time'			=> array('description', 'type', 'visible', 'default', 'required', 'onchange'),
        'datetime'		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange'),
        'file'  		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'multilang'),
        'binary'		=> array('description', 'type', 'visible', 'default', 'usage', 'required', 'onchange', 'multilang'),
        'many2one'		=> array('description', 'type', 'visible', 'default', 'required', 'foreign_object', 'domain', 'onchange', 'ondelete', 'multilang'),
        'one2many'		=> array('description', 'type', 'visible', 'default', 'foreign_object', 'foreign_field', 'domain', 'onchange', 'order', 'sort'),
        'many2many'		=> array('description', 'type', 'visible', 'default', 'foreign_object', 'foreign_field', 'rel_table', 'rel_local_key', 'rel_foreign_key', 'domain', 'onchange'),
        'computed'		=> array('description', 'type', 'visible', 'default', 'result_type', 'usage', 'function', 'onchange', 'store', 'multilang')
    );

    public static $mandatory_attributes = array(
        'alias'		    => array('type', 'alias'),
        'boolean'		=> array('type'),
        'integer'		=> array('type'),
        'float'			=> array('type'),
        'string'		=> array('type'),
        'text'			=> array('type'),
        'html'			=> array('type'),
        'date'			=> array('type'),
        'time'			=> array('type'),
        'datetime'		=> array('type'),
        'binary'		=> array('type'),
        'file'		    => array('type'),
        'many2one'		=> array('type', 'foreign_object'),
        'one2many'		=> array('type', 'foreign_object', 'foreign_field'),
        'many2many'		=> array('type', 'foreign_object', 'foreign_field', 'rel_table', 'rel_local_key', 'rel_foreign_key'),
        'computed'		=> array('type', 'result_type', 'function')
    );

    public static $valid_operators = [
        'boolean'		=> array('=', '<>', '<', '>', 'in'),
        'integer'		=> array('in', 'not in', '=', '<>', '<', '>', '<=', '>='),
        'float'			=> array('in', 'not in', '=', '<>', '<', '>', '<=', '>='),
        'string'		=> array('like', 'ilike', 'in', 'not in', '=', '<>'),
        'text'			=> array('like', 'ilike', '='),
        'date'			=> array('=', '<>', '<', '>', '<=', '>=', 'like'),
        'time'			=> array('=', '<>', '<', '>', '<=', '>='),
        'datetime'		=> array('=', '<>', '<', '>', '<=', '>='),
        'file'		    => array('like', 'ilike', '='),
        'binary'		=> array('like', 'ilike', '='),
        // for convenience, 'contains' is allowed for many2one field (in such case 'contains' operator means 'list contains *at least one* of the following ids')
        'many2one'		=> array('is', 'in', 'not in', '=', '<>', 'contains'),
        'one2many'		=> array('contains'),
        'many2many'		=> array('contains'),
    ];

    public static $types_associations = [
        'boolean' 		=> 'tinyint(4)',
        'integer' 		=> 'int(11)',
        'float' 		=> 'decimal(10,2)',
        'string' 		=> 'varchar(255)',
        'text' 			=> 'text',
        'date' 			=> 'date',
        'time' 			=> 'time',
        'datetime' 		=> 'datetime',
        'timestamp' 	=> 'timestamp',
        'file' 		    => 'mediumblob',
        'binary' 		=> 'mediumblob',
        'many2one' 		=> 'int(11)'
    ];

    public static $usages_associations = [
        'coordinate'			=> 'decimal(9,6)',
        'language/iso-639:2'	=> 'char(2)',
        'amount/money'			=> 'decimal(15,2)',
        'currency/iso-4217'		=> 'char(3)',
        'url/mailto'			=> 'varchar(255)',
        'markup/html'			=> 'mediumtext'
    ];

    protected function __construct(DBConnection $db) {
// #todo : declare required constants instead through constants() method
        // make sure mandatory constants are defined
        if(!defined('EXPORT_FLAG')) {
            if(!function_exists('\config\export_config')) die('mandatory config namespace or function export_config is missing');
            \config\export_config();
        }
        $this->db = &$db;
        $this->packages = null;
        $this->cache = [];
        $this->instances = [];
    }

    public function __destruct() {
        // close DB connection, if open
        if($this->db && $this->db->connected()) {
            $this->db->disconnect();
        }
    }

    public static function constants() {
// #todo
        return [];
    }

    public function getDB() {
        return $this->db;
    }

    private function getDBHandler() {
        // open DB connection
        if(!$this->db->connected()) {
            if($this->db->connect() === false) {
                // fatal error
                trigger_error(	'Error raised by '.__CLASS__.'::'.__METHOD__.'@'.__LINE__.' : '.
                                'unable to establish connection to database: check connection parameters '.
                                '(possibles reasons: non-supported DBMS, unknown database name, incorrect username or password, ...)',
                                E_USER_ERROR);
            }
        }
        return $this->db;
    }

    public function __toString() {
        return "ObjectManager instance";
    }




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
     * @param string $class
     * @throws Exception
     */
    private function &getStaticInstance($class, $fields=[]) {
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
            preg_match('/class(\s*)'.$class_name.'(\s.*)\{/iU', $file_content, $matches);
            if(!isset($matches[1])) {
                throw new Exception("malformed class file for model '$class': class name do not match file name", QN_ERROR_INVALID_PARAM);
            }
            preg_match('/\bextends\b(.*)\{/iU', $file_content, $matches);
            if(!isset($matches[1])) {
                throw new Exception("malformed class file for model '$class': parent class name not found in file", QN_ERROR_INVALID_PARAM);
            }
            $parent_class = trim($matches[1]);
            if(!(include_once $filename)) {
                throw new Exception("unknown model: '$class'", QN_ERROR_UNKNOWN_OBJECT);
            }
            if(!class_exists($class)) {
                throw new Exception("unknown model (check file syntax): '$class'", QN_ERROR_UNKNOWN_OBJECT);
            }
        }
        if(!isset($this->instances[$class])) {
            $this->instances[$class] = new $class($this, $fields);
        }
        return $this->instances[$class];
    }


    /**
     * Gets the name of the table associated to the specified class (required to convert namespace notation).
     *
     * @param string $object_class
     * @return mixed string|integer Returns the name of the table related to the Class or an error code.
     */
    public function getObjectTableName($object_class) {
        try {
            $object = &$this->getStaticInstance($object_class);
        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return $e->getCode();
        }
        return strtolower($object->getTable());
    }

    /**
     * Gets the filename containing the class definition of an object, including a part of its path (required to convert namespace notation).
     *
     * @param string $object_class
     * @return string
     */
    public static function getObjectClassFile($object_class) {
        $parts = explode('\\', $object_class);
        array_shift($parts);
        return implode('/', $parts).'.class.php';
    }

    /**
     * Retrieve the package in which is defined the class of an object (required to convert namespace notation).
     *
     * @param string $object_class
     * @return string
     */
    public static function getObjectPackage($object_class) {
        $parts = explode('\\', $object_class);
        return array_shift($parts);
    }

    /**
     * Retrieve the complete schema of an object (including special fields).
     *  note: this method is not set as static since we need to load class file in order to retrieve the schema
     * (and this is only done in the getStaticInstance method)
     *
     * @param string $object_class
     * @return string
     */
    public function getObjectSchema($object_class) {
        $object = &$this->getStaticInstance($object_class);
        return $object->getSchema();
    }

    /**
    * Checks if all the given attributes are defined in the specified schema for the given field.
    *
    * @param array $check_array
    * @param array $schema
    * @param string $field
    * @return bool
    */
    public static function checkFieldAttributes($check_array, $schema, $field) {
        if (!isset($schema) || !isset($schema[$field])) throw new Exception("empty schema or unknown field name '$field'", QN_ERROR_UNKNOWN_OBJECT);
        $attributes = $check_array[$schema[$field]['type']];
        return !(count(array_intersect($attributes, array_keys($schema[$field]))) < count($attributes));
    }


    /**
     * Filter given identifiers and return only valid ones (whatever the status of the objects).
     * Ids that do not match an object in the database are removed from the list.
     * Ids of soft-deleted object are considered valid.
     * 
     * @param $class
     * @param $ids
     */
    private function filterValidIdentifiers($class, $ids) {
        $valid_ids = [];
        // sanitize $ids
        if(!is_array($ids)) {
            $ids = (array) $ids;
        }
        // ensure ids are positive integer values
        foreach($ids as $key => $oid) {
            $id = intval($oid);
            if(!is_numeric($oid) || $id <= 0) {
                unset($ids[$key]);
                continue;
            }
            $ids[$key] = $id;
        }
        // remove duplicate ids, if any
        $ids = array_unique($ids);
        // remove already loaded objects from missing list
        $missing_ids = $ids;
        foreach($ids as $index => $id) {
            if(isset($this->identifiers[$class][$id]) || isset($this->cache[$class][$id])) {
                $valid_ids[] = $id;
                unset($missing_ids[$index]);
            }
        }
        // process remaining identifiers
        if(!empty($missing_ids)) {
            // get DB handler (init DB connection if necessary)
            $db = $this->getDBHandler();
            $table_name = $this->getObjectTableName($class);
            // get all records at once
            $result = $db->getRecords($table_name, 'id', $missing_ids);
            // store all found ids in an array
            while($row = $db->fetchArray($result)) {
                $valid_ids[] = (int) $row['id'];
                // remember valid identifiers
                $this->identifiers[$class][$row['id']] = true;
            }
        }
        return $valid_ids;
    }

    /**
     * Load given fields from specified class into the cache
     *
     * @return void
     * @throw Exception
     */
    private function load($class, $ids, $fields, $lang) {
        // get the object instance
        $object = &$this->getStaticInstance($class);
        // get the complete schema of the object (including special fields)
        $schema = $object->getSchema();
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            // array holding functions to load each type of fields
            $load_fields = array(
            // 'alias'
            'alias'	=>	function($om, $ids, $fields) {
                // nothing to do : this type is handled in read methods
            },
            // 'multilang' is a particular case of simple field
            'multilang'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
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
                    // do some pre-treatment if necessary (this step is symetrical to the one in store method)
                    $value = $this->container->get('adapt')->adapt($row['value'], $schema[$field]['type'], 'php', 'sql', $class, $oid, $field, $lang);
                    // update the internal buffer with fetched value
                    $om->cache[$class][$oid][$lang][$field] = $value;
                }
                // force assignment to NULL if no result was returned by the SQL query
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        if(!isset($om->cache[$class][$oid][$lang][$field])) $om->cache[$class][$oid][$lang][$field] = NULL;
                    }
                }
            },
            'simple'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                // get the name of the DB table associated to the object
                $table_name = $om->getObjectTableName($class);
                // make sure to load the 'id' field (we need it to map fetched values to their object)
                $fields[] = 'id';
                // get all records at once
                $result = $om->db->getRecords(array($table_name), $fields, $ids);

                // treat sql result in the same order than the ids list
                while($row = $om->db->fetchArray($result)) {
                    // retrieve the id of the object associated with current record
                    $oid = $row['id'];
                    foreach($row as $field => $value) {
                        // do some pre-treatment if necessary (this step is symetrical to the one in store method)
                        $type = $schema[$field]['type'];
                        if(isset($schema[$field]['result_type'])) $type = $schema[$field]['result_type'];

                        if(!is_null($value)) {
                            $value = $this->container->get('adapt')->adapt($value, $type, 'php', 'sql', $class, $oid, $field, $lang);
                        }

                        // update the internal buffer with fetched value
                        $om->cache[$class][$oid][$lang][$field] = $value;
                    }
                }
            },
            'one2many'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($fields as $field) {
                    if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                    $order = (isset($schema[$field]['order']))?$schema[$field]['order']:'id';
                    $sort = (isset($schema[$field]['sort']))?$schema[$field]['sort']:'asc';
// #todo : handle alias fields (require subsequent schema)
// #todo : add support for domain, when present (merge with conditions below)
                    // obtain the ids by searching inside the foreign object's table
                    $result = $om->db->getRecords(
                        $om->getObjectTableName($schema[$field]['foreign_object']),
                        array('id', $schema[$field]['foreign_field'], $order),
                        NULL,
                         [
                            [
                                [$schema[$field]['foreign_field'], 'in', $ids],
                                ['state', '=', 'instance'],
                                ['deleted', '=', '0'],
                            ]
                        ],
                        'id',
                        [$order => $sort]
                    );
                    $lists = array();
                    while ($row = $om->db->fetchArray($result)) {
                        $oid = $row[$schema[$field]['foreign_field']];
                        $lists[$oid][] = $row['id'];
                    }
                    foreach($ids as $oid) $om->cache[$class][$oid][$lang][$field] = (isset($lists[$oid]))?$lists[$oid]:[];
                }
            },
            'many2many'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($fields as $field) {
                    if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                    // obtain the ids by searching inside relation table
                    $result = $om->db->getRecords(
                        array('t0' => $om->getObjectTableName($schema[$field]['foreign_object']), 't1' => $schema[$field]['rel_table']),
                        array('t1.'.$schema[$field]['rel_foreign_key'], 't1.'.$schema[$field]['rel_local_key']),
                        NULL,
                        array(array(
                                // note :we have to escape right field because there is no way for dbManipulator to guess it is not a value
                                array('t0.id', '=', "`t1`.`{$schema[$field]['rel_foreign_key']}`"),
                                array('t1.'.$schema[$field]['rel_local_key'], 'in', $ids),
                                array('t0.state', '=', 'instance'),
                                array('t0.deleted', '=', '0'),
                            )
                        ),
                        't0.id'
                    );
                    $lists = array();
                    while ($row = $om->db->fetchArray($result)) {
                        $oid = $row[$schema[$field]['rel_local_key']];
                        $lists[$oid][] = $row[$schema[$field]['rel_foreign_key']];
                    }
                    foreach($ids as $oid) $om->cache[$class][$oid][$lang][$field] = (isset($lists[$oid]))?$lists[$oid]:[];
                }
            },
            'computed'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($fields as $field) {
                    if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                    if(!is_callable($schema[$field]['function'])) throw new Exception("error in schema parameter for function field '$field' of class '$class' : function cannot be called");
                    $res = call_user_func($schema[$field]['function'], $om, $ids, $lang);
                    foreach($ids as $oid) {
                        if(isset($res[$oid])) {
                            $value = $this->container->get('adapt')->adapt($res[$oid], $schema[$field]['result_type'], 'php', 'sql', $class, $oid, $field, $lang);
                        }
                        else {
                            $value = null;
                        }
                        $om->cache[$class][$oid][$lang][$field] = $value;
                    }
                }
            }
            );

            // build an associative array ordering given fields by their type
            $fields_lists = array();
            // remember computed fields having store attibute set (we need this as $type will hold the $schema['result_type'] value)
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
                    if($lang != DEFAULT_LANG && isset($schema[$field]['multilang']) && $schema[$field]['multilang'])
                        $fields_lists['multilang'][] = $field;
                    // note: if $lang differs from DEFAULT_LANG and field is not set as multilang, no change will be stored for that field
                    else $fields_lists['simple'][] = $field;
                }
                else  $fields_lists[$type][] = $field;
            }

            // 2) load fields values, grouping fields by type
            foreach($fields_lists as $type => $list) {
                $load_fields[$type]($this, $ids, $list);
            }

            // 3) check if some computed fields were not set in database
            foreach($stored_fields as $field) {
                // for each computed field, build an array holding ids of incomplete objects
                $oids = array();
                // if store attribute is set and no result was found, we need to compute the value
                // note : we use is_null() rather than empty() because an empty value could be the result of a calculation
                // (this implies that the DB schema has 'DEFAULT NULL' for columns associated to computed fields)
                foreach($ids as $oid) if(is_null($this->cache[$class][$oid][$lang][$field])) $oids[] = $oid;
                // compute field for incomplete objects
                $load_fields['computed']($this, $oids, array($field));
                // store newly computed fields to database, if required ('store' attribute set to true)
                $this->store($class, $oids, array($field), $lang);
            }

        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            throw new Exception('unable to load object fields', $e->getCode());
        }
    }

    /**
     * Stores specified fields of selected objects into database.
     * Values are read from the $cache.
     */
    private function store($class, $ids, $fields, $lang) {
        // get the object instance
        $object = &$this->getStaticInstance($class);
        // get the complete schema of the object (including special fields)
        $schema = $object->getSchema();
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            // array holding functions to store each type of fields
            $store_fields = array(
            // 'multilang' is a particular case of simple field)
            'multilang'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
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
                $values_array = [];
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $values_array[] = [
                            $lang, $class, $field, $oid,
                            $this->container->get('adapt')->adapt($om->cache[$class][$oid][$lang][$field], $schema[$field]['type'], 'sql', 'php', $class, $oid, $field, $lang)
                        ];
                    }
                }
                $om->db->addRecords(
                    'core_translation',
                    array('language', 'object_class', 'object_field', 'object_id', 'value'),
                    $values_array
                );

            },
            'simple'	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($ids as $oid) {
                    $fields_values = array();
                    foreach($fields as $field) {
                        $type = $schema[$field]['type'];
                        // support computed fields (handled as simple fields)
                        if($type == 'computed') {
                            $type = $schema[$field]['result_type'];
                        }
                        $fields_values[$field] = $this->container->get('adapt')->adapt($om->cache[$class][$oid][$lang][$field], $type, 'sql', 'php', $class, $oid, $field, $lang);
                    }
                    $om->db->setRecords($om->getObjectTableName($class), array($oid), $fields_values);
                }
            },
            'one2many' 	=>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $value = $om->cache[$class][$oid][$lang][$field];
                        if(!is_array($value)) throw new Exception("wrong value for field '$field' of class '$class', should be an array", QN_ERROR_INVALID_PARAM);
                        $ids_to_remove = array();
                        $ids_to_add = array();
                        foreach($value as $id) {
                            $id = intval($id);
                            if($id < 0) $ids_to_remove[] = abs($id);
                            if($id > 0) $ids_to_add[] = $id;
                        }
                        $foreign_table = $om->getObjectTableName($schema[$field]['foreign_object']);
                        // remove relation by setting pointing id to 0
                        if(count($ids_to_remove)) $om->db->setRecords($foreign_table, $ids_to_remove, array($schema[$field]['foreign_field']=>0));
                        // add relation by setting the pointing id (overwrite previous value if any)
                        if(count($ids_to_add)) $om->db->setRecords($foreign_table, $ids_to_add, array($schema[$field]['foreign_field']=>$oid));
                        // invalidate cache (field partially loaded)
                        unset($om->cache[$class][$oid][$lang][$field]);
                    }
                }
            },
            'many2many' =>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                foreach($ids as $oid) {
                    foreach($fields as $field) {
                        $value = $om->cache[$class][$oid][$lang][$field];
                        if(!is_array($value)) {
                            trigger_error("wrong value for field '$field' of class '$class', should be an array", E_USER_ERROR);
                            continue;
                        }
                        $ids_to_remove = array();
                        $values_array = array();
                        foreach($value as $id) {
                            $id = intval($id);
                            if($id < 0) $ids_to_remove[] = abs($id);
                            if($id > 0) $values_array[] = array($oid, $id);
                        }
                        // delete relations of ids having a '-'(minus) prefix
                        if(count($ids_to_remove)) {
                            $om->db->deleteRecords(
                                $schema[$field]['rel_table'],
                                array($oid),
                                array(
                                    array(
                                        array($schema[$field]['rel_foreign_key'], 'in', $ids_to_remove)
                                    )
                                ),
                                $schema[$field]['rel_local_key']
                            );
                        }
                        // create relations for other ids
                        $om->db->addRecords(
                            $schema[$field]['rel_table'],
                            array(
                                $schema[$field]['rel_local_key'],
                                $schema[$field]['rel_foreign_key']
                            ),
                            $values_array
                        );
                        // invalidate cache (field partially loaded)
                        unset($om->cache[$class][$oid][$lang][$field]);
                    }
                }
            },
            'computed' =>	function($om, $ids, $fields) use ($schema, $class, $lang) {
                // nothing to store for computed fields (ending up here means that the 'store' attribute is not set)
            },
            'alias' =>	function($om, $ids, $fields) use ($schema, $class, $lang) {
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
                    if($lang != DEFAULT_LANG && isset($schema[$field]['multilang']) && $schema[$field]['multilang'])
                        $fields_lists['multilang'][] = $field;
                    // note: if $lang differs from DEFAULT_LANG and field is not set as multilang, no change will be stored for that field
                    else $fields_lists['simple'][] = $field;
                }
                else  $fields_lists[$type][] = $field;
            }
            // 2) store fields according to their types
            foreach($fields_lists as $type => $list) $store_fields[$type]($this, $ids, $list);

        }
        catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            throw new Exception('unable to store object fields', $e->getCode());
        }
    }


    public function getStatic($object_class) {
        $instance = false;
        $packages = $this->getPackages();
        $package = self::getObjectPackage($object_class);
        if(in_array($package, $packages)) {
            try {
                $instance = $this->getStaticInstance($object_class);
            }
            catch(Exception $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }
        return $instance;
    }

    // alias for getStatic
    public function getModel($object_class) {
        $instance = $this->getStatic($object_class);
        return $instance;
    }

    /**
     * Checks whether the values of given object fields are valid.
     * This is done using the class validation method.
     *
     * Ex.:
     *	       "INVALID_PARAM": {
     *             "login": {
     *                 "invalid_email": "Login must be a valid email address."
     *               }
     *			}
     *
     * @param string $class object class
     * @param array $values
     * @param boolean $check_unique
     * @return array Returns an associative array containing invalid fields with their associated error_message_id. An empty array means all fields are valid.
     */
    public function validate($class, $ids, $values, $check_unique=false, $check_required=false) {

// #todo : check relational fields consistency (make sure that targeted object(s) actually exist)
// #todo : support partial object check (individual field validation) and full object check (with support for the 'required' attribute)

        $res = [];

        $model = &$this->getStaticInstance($class);

        // get constraints defined in the model (schema)
        $constraints = $model->getConstraints();
        $schema = $model->getSchema();

        foreach($values as $field => $value) {
            // add constraints based on field type : check that given value is not bigger than related DBMS column capacity
            if(isset($schema[$field]['type'])) {
                switch($schema[$field]['type']) {
                    case 'string':
                        $constraints[$field]['size_overflow'] = [
                            'message' 	=> 'String length must be maximum 255 chars.',
                            'function'	=> function($val, $obj) {return (strlen($val) <= 255);}
                        ];
                        break;
                    case 'text':
                        $constraints[$field]['size_overflow'] = [
                            'message' 	=> 'String length must be maximum 65,535 chars.',
                            'function'	=> function($val, $obj) {return (strlen($val) <= 65535);}
                        ];
                        break;
                    case 'file':
                    case 'binary':
                        $constraints[$field]['size_overflow'] = [
                            'message' 	=> 'String length must be maximum 16,777,215 chars.',
                            'function'	=> function($val, $obj) {return (strlen($val) <= 16777215);}
                        ];
                        break;
                }
            }
            // add constraints based on `usage` attribute
            if(isset($schema[$field]['usage']) && !empty($value)) {
                $constraint = DataValidator::getConstraintFromUsage($schema[$field]['usage']);
                $constraints[$field]['type_misuse'] = [
                    'message' 	=> 'Value does not comply with usage \''.$schema[$field]['usage'].'\'.',
                    'function'	=> $constraint['rule']
                ];
            }
            // add constraints based on `required` attribute
            if(isset($schema[$field]['required']) && $schema[$field]['required']) {
                $constraints[$field]['missing_mandatory'] = [
                    'message' 	=> 'Missing mandatory value.',
                    'function'	=> function($a) { return (isset($a) && !empty($a)); }
                ];
            }            
            // check constraints
            if(isset($constraints[$field])) {
                foreach($constraints[$field] as $error_id => $constraint) {
                    if(isset($constraint['function']) ) {
                        $validation_func = $constraint['function'];
                        if(is_callable($validation_func) && !call_user_func($validation_func, $value, $values)) {
                            if(!isset($constraint['message'])) {
                                $constraint['message'] = 'invalid field';
                            }
                            trigger_error("field {$field} violates constraint : {$constraint['message']}", E_USER_WARNING);
                            $error_code = QN_ERROR_INVALID_PARAM;
                            if(!isset($res[$field])) {
                                $res[$field] = [];
                            }
                            $res[$field][$error_id] = $constraint['message'];
                        }
                    }
                }
            }
        }

        // REQUIRED constraint check
        if($check_required) {
            foreach($schema as $field => $def) {
                if(isset($def['required']) && $def['required'] && !isset($values[$field])) {
                    $error_code = QN_ERROR_INVALID_PARAM;
                    $res[$field]['missing_mandatory'] = 'Missing mandatory value.';
                    trigger_error("mandatory field {$field} is missing", E_USER_WARNING);
                }
            }
        }
        // UNIQUE constraint check
        if($check_unique && !count($res) && method_exists($model, 'getUnique')) {

            $uniques = $model->getUnique();

            // load all fields impacted by 'unique' constraints
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
            
            foreach($ids as $id) {
                foreach($uniques as $unique) {
                    $conflict_ids = [];
                    $domain = [];
                    foreach($unique as $field) {
                        // map unique fields with the given values
                        if(!isset($values[$field])) {
                            $domain[] = [ $field, '=', $extra_values[$id][$field] ];
                        }
                        else {
                            // map unique fields with the given values
                            $domain[] = [$field, '=', $values[$field]];
                        }
                    }

                    // search for objects already set with unique values
                    if(count($domain)) {
                        // overload default 'state' condition (set in search method): no restriction on state
                        $domain[] = ['state', 'like', '%%'];
                        // overload default 'deleted' condition (set in search method): we must be able to restore deleted objects
                        $domain[] = ['deleted', 'in', ['0', '1']];
                        if(count($ids)) {
                            // ids are always unique
                            $domain[] = ['id', 'not in', $ids];
                        }
                        $conflict_ids = $this->search($class, $domain);
                    }
                    // there is a violation : stop and fetch info about it
                    if(count($conflict_ids)) {
                        $objects = $this->read($class, $conflict_ids, $unique);
                        foreach($objects as $oid => $odata) {
                            foreach($odata as $field => $value) {
                                $original = isset($values[$field])?$values[$field]:$extra_values[$id][$field];
                                if($value == $original) {
                                    trigger_error("field {$field} violates unique constraint with object {$oid}", E_USER_WARNING);
                                    $error_code = QN_ERROR_CONFLICT_OBJECT;
                                    $res[$field] = ['duplicate_index' => 'unique constraint violation'];
                                }
                            }
                        }
                        break 2;
                    }
                }

                // loop only if some field impacted by the 'unique' constraint are not present in the received $values
                if(count($extra_fields) <= 0) break;
            }

        }
        return (count($res))?[$error_code => $res]:[];
    }


    /**
     * Create a new instance of given class.
     * Upon creation, the objecft remains in 'draft' state until it has been written:
     *
     * - if $fields is empty, a draft object is created with fields set to default values defined by the class Model.
     * - if $field contains some values, object is created and its state is set to 'instance' (@see write method)
     *
     *
     * @param  $class        string
     * @param  $fields       array
     * @param  $lang         string
     * @param  $use_draft    boolean    If set to false, disables the re-use of outdated drafts (objects created but not saved afterward).
     * @return integer       The result is an identfier of the newly created object or, in case of error, the code of the error that was raised (by convention, error codes are negative integers).
     */
    public function create($class, $fields=NULL, $lang=DEFAULT_LANG, $use_draft=true) {
        $res = 0;
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            $object = &$this->getStaticInstance($class, $fields);

            $object_table = $this->getObjectTableName($class);

            // 1) define default values
            $creation_array = ['creator' => ROOT_USER_ID, 'created' => date("Y-m-d H:i:s"), 'state' => (isset($fields['state']))?$fields['state']:'instance'];

            // set creation array according to received fields: `id` and `creator` can be set to force resulting object
            if(!empty($fields)) {
                // use given id field as identifier, if any and valid
                if(isset($fields['id'])) {
                    if(is_numeric($fields['id'])) {
                        $creation_array['id'] = (int) $fields['id'];
                    }
                }
                // use given creator id, if any and valid
                if(isset($fields['creator'])) {
                    if(is_numeric($fields['creator'])) {
                        $creation_array['creator'] = (int) $fields['creator'];
                    }
                }
            }

            // 2) garbage collect: check for expired draft object

            // by default, request a new object ID
            $oid = 0;

            if(!isset($creation_array['id'])) {
                if($use_draft) {
                    // list ids of records having creation date older than DRAFT_VALIDITY
                    $ids = $this->search($class, [['state', '=', 'draft'],['created', '<', date("Y-m-d H:i:s", time()-(3600*24*DRAFT_VALIDITY))]], ['id' => 'asc']);                    
                    if(count($ids) && $ids[0] > 0) {
                        // use the oldest expired draft
                        $oid = $ids[0];
                        // store the id to reuse
                        $creation_array['id'] = $oid;
                        // and delete the associated record (might contain obsolete data)
                        $db->deleteRecords($object_table, array($oid));
                    }
                }
            }
            else {
                $ids = $this->filterValidIdentifiers($class, [$creation_array['id']]);
                if(!empty($ids)) throw new Exception('duplicate_object_id', QN_ERROR_CONFLICT_OBJECT);
                $oid = (int) $creation_array['id'];
            }

            // 3) create a new record with the found value, (if no id is given, the autoincrement will assign a value)
            $db->addRecords($object_table, array_keys($creation_array), [ array_values($creation_array) ]);

            if($oid <= 0) {
                $oid = $db->getLastId();
                $this->cache[$class][$oid][$lang] = $creation_array;
            }
            // in any case, we return the object id
            $res = $oid;

            // 4) update new object with given fiels values, if any

            // update creation array with actual object values (fields described as PHP values - not SQL)
            $creation_array = array_merge( $creation_array, $object->getValues(), $fields );
            $res_w = $this->write($class, $oid, $creation_array, $lang);
            // if write method generated an error, return error code instead of object id
            if($res_w < 0) $res = $res_w;
        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * Updates specifield fields of seleced objects and stores changes into database
     *
     * @param   string    $class	     class of the objects to write
     * @param   mixed	  $ids	         identifier(s) of the object(s) to update (accepted types: array, integer, numeric string)
     * @param   mixed	  $fields	     array mapping fields names with the value (PHP) to which they must be set
     * @param   string    $lang	         language under which fields have to be stored (only relevant for multilang fields)
     * @return  mixed    (int or array)  error code OR array of updated ids
     */
    public function write($class, $ids=NULL, $fields=NULL, $lang=DEFAULT_LANG) {
        $res = [];
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            // 1) do some pre-treatment

            // cast fields to an array (passing a single field is accepted)
            if(!is_array($fields))	$fields = (array) $fields;
            // keep only valid objects identifiers
            $ids = $this->filterValidIdentifiers($class, $ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) return $res;
            // ids that are left are the ones of the objects that will be writen
            $res = $ids;
            // get stattic instance (checks that given class exists)
            $object = &$this->getStaticInstance($class);

            // 2) check $fields arg validity

            // remove unknown fields
            $allowed_fields = $object->getFields();
            // prevent updating id field (reserved)
            if(isset($fields['id'])) unset($fields['id']);

            foreach($fields as $field => $values) {
                // remove fields not defined in related schema
                if(!in_array($field, $allowed_fields)) {
                    unset($fields[$field]);
                    trigger_error('unknown field ('.$field.') in $fields arg', E_USER_WARNING);
                }
            }
            // #memo - writing an object does not change its state, unless when explicitely set in $fields
            // $fields['state'] = (isset($fields['state']))?$fields['state']:'instance';
            $fields['modified'] = time();

            // 3) update internal buffer with given values
            $schema = $object->getSchema();
            $onchange_fields = array();
            foreach($ids as $oid) {
                foreach($fields as $field => $value) {
                    // remember fields whose modification triggers an onchange event
                    if(isset($schema[$field]['onchange'])) $onchange_fields[] = $field;
                    // assign cache to object values
                    $this->cache[$class][$oid][$lang][$field] = $value;
                }
            }

            // 4) write selected fields to DB
            $this->store($class, $ids, array_keys($fields), $lang);

            // 5) second pass : handle onchange events, if any
            // #memo this must be done after modifications otherwise object values might be outdated
            if(count($onchange_fields)) {
                // remember which methods have been invoked (to trigger each only once)
                $onchange_methods = [];
                // call methods associated with onchange events of related fields
                foreach($onchange_fields as $field) {
                    if(!isset($onchange_methods[$schema[$field]['onchange']])) {
                        if(is_callable($schema[$field]['onchange'])) call_user_func($schema[$field]['onchange'], $this, $ids, $lang);
                        $onchange_methods[$schema[$field]['onchange']] = true;
                    }
                }
            }

        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * Returns either an error code or an associative array containing, for each requested object id,
     * an array maping each selected field to its value.
     * Note : The process maintains order inside $ids and $fields arrays.
     *
     * @param   string	$class	        class of the objects to retrieve
     * @param   mixed	$ids	        identifier(s) of the object(s) to retrieve (accepted types: array, integer, string)
     * @param   mixed	$fields	        name(s) of the field(s) to retrieve (accepted types: array, string)
     * @param   string  $lang	        language under which return fields values (only relevant for multilang fields)
     * @return  mixed   (int or array)  error code OR resulting associative array
     */
    public function read($class, $ids=NULL, $fields=NULL, $lang=DEFAULT_LANG) {        
        $res = [];
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            
            // 1) do some pre-treatment

            // get static instance (check that given class exists)
            $object = &$this->getStaticInstance($class);
            // cast fields to an array (passing a single field is accepted)
            // #memo - duplicate fields are allowed: the value will be loaded once and returned as many times as requested
            if(!is_array($fields)) $fields = (array) $fields;
            // keep only valid objects identifiers
            $ids = $this->filterValidIdentifiers($class, $ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) return $res;
            // init resulting array
            foreach($ids as $oid) $res[$oid] = [];

            // 2) check $fields arg validity

            $schema = $object->getSchema();
            $requested_fields = [];
            $dot_fields = [];
            // check fields validity
            foreach($fields as $key => $field) {
                // handle fields with 'dot' notation
                if(strpos($field, '.') > 0) {
                    $dot_fields[] = $field;
                    // drop dot fields (they will be handled later on)
                    unset($fields[$key]);
                }
                // invalid field
                else if(!isset($schema[$field])) {
                    // drop invalid fields
                    unset($fields[$key]);
                    trigger_error("unknown field '$field' for class : '$class'", E_USER_WARNING);
                }
                else {
                    // handle aliases
                    while($schema[$field]['type'] == 'alias') {
                        $field = $schema[$field]['alias'];
                    }
                    $requested_fields[] = $field;
                }
            }

            // 3) check among requested fields wich ones are not yet present in the internal buffer
            if(count($requested_fields)) {
                // if internal buffer is empty, query the DB to load all fields from requested objects
                if(empty($this->cache) || !isset($this->cache[$class])) {
                    $this->load($class, $ids, $requested_fields, $lang);
                }
                // check if all objects are fully loaded
                else {
                    // build the missing fields array by increment
                    // we need the 'broadest' fields array to be loaded to minimize # of SQL queries
                    $fields_missing = array();
                    foreach($requested_fields as $key => $field) {
                        foreach($ids as $oid) {
                            if(!isset($this->cache[$class][$oid][$lang][$field])) {
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
                    if(!isset($this->cache[$class][$oid]) || empty($this->cache[$class][$oid])) {
                        trigger_error("unknown or empty object $class($oid)", E_USER_WARNING);
                        continue;
                    }
                    // init result for given id, if missing
                    if(!isset($res[$oid])) $res[$oid] = array();
                    // first pass : retrieve fields values
                    foreach($fields as $key => $field) {
                        // handle aliases
                        $target_field = $field;
                        while($schema[$target_field]['type'] == 'alias') {
                            $target_field = $schema[$target_field]['alias'];
                        }
                        // use final notation
                        $res[$oid][$field] = $this->cache[$class][$oid][$lang][$target_field];
                    }
                }
            }

            // 5) handle dot fields
            
            foreach($dot_fields as $field) {
                // init result set (values will be used as keys for assigning the loaded values)
                foreach($ids as $oid) {
                    $res[$oid][$field] = $oid;
                }
                // class of the object at the current position (of the 'path' variable), start with the class given as parameter
                $path_class = $class;
                // list of the parent objects ids, start with the objects ids given as parameter
                $path_prev_ids = $ids;
                // schema of the object at the current position, start with the schema of the class given as parameter
                $path_schema = $schema;
                $path_fields = explode('.', $field);
                $n = count($path_fields);
                $i = 0;
                // walk through the path variable (containing the fields hierarchy)
                foreach($path_fields as $path_field) {
                    // fetch all ids for every parent object (whose ids are stored in $path_prev_ids)
                    // #caution there might be a recursion here
                    $path_values = $this->read($path_class, $path_prev_ids, array($path_field), $lang);
                    // assign result set with loaded values for current level
                    foreach($ids as $oid) {
                        if($res[$oid][$field] && isset($path_values[$res[$oid][$field]])) {
                            $res[$oid][$field] = $path_values[$res[$oid][$field]][$path_field];
                        }
                        else {
                            $res[$oid][$field] = null;
                        }
                    }
                    // if target field is a relational type, keep on processing the path
                    if(isset($path_schema[$path_field]['foreign_object']) && $i < ($n-1) ) {
                        // obtain the next object name (given the name of the current field, $path_field, and the schema of the current object, $path_schema)
                        $path_class = $path_schema[$path_field]['foreign_object'];
                        // targeted values is a list of ids for the object at the current position of the path
                        $path_prev_ids = array_map(function ($odata) use($path_field) { return $odata[$path_field]; }, $path_values);
                        $object = &$this->getStaticInstance($path_class);
                        $path_schema = $object->getSchema();
                    }
                    else {
                        // do not follow a malformed path
                        break;
                    }
                    ++$i;
                }
            }

        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res = $e->getCode();
        }
        return $res;
    }


    /**
     * Delete an object permanently or put it in the "trash bin" (i.e. setting the 'deleted' flag to 1).
     * The returned structure is an associative array containing ids of the objects actually deleted.
     *
     * @param   string  $class              object class
     * @param   array   $ids                ids of the objects to remove
     * @param   boolean $permanent          flag for soft (marked as deleted) or hard deletion (removed from DB)
     * @return  mixed   (integer or array)  error code OR array of ids of deleted objects
     */
    public function remove($class, $ids, $permanent=false) {
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();
        $res = [];

        try {

            // 1) do some pre-treatment
            // keep only valid objects identifiers
            $ids = $this->filterValidIdentifiers($class, $ids);
            // if no ids were specified, the result is an empty list (array)
            if(empty($ids)) return $res;
            // ids that are left are the ones of the objects that will be (marked as) deleted
            $res = $ids;

            // 2) remove object
            $object = &$this->getStaticInstance($class);
            $schema = $object->getSchema();
            $table_name = $this->getObjectTableName($class);
            if (!$permanent) {
                // soft deletion
                $db->setRecords($table_name, $ids, ['deleted' => 1]);
            }
            else {
                // hard deletion
                $db->deleteRecords($table_name, $ids);
                // 3) cascade deletions / relations updates
                foreach($schema as $field => $def) {
                    if(in_array($def['type'], ['file', 'one2many', 'many2many'])) {
                        switch($def['type']) {
                        case 'file':
                            if(FILE_STORAGE_MODE == 'FS') {
                                // build a unique name  (package/class/field/oid.lang)
                                $path = sprintf("%s/%s", str_replace('\\', '/', $class), $field);
                                $storage_location = realpath(FILE_STORAGE_DIR).'/'.$path;

                                foreach($ids as $oid) {
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
                                    $ondelete = $rel_schema[$def['foreign_field']]['ondelete'];
                                    if(is_callable($ondelete)) {
                                        call_user_func($ondelete, $this, $rel_ids);
                                    }
                                    else {
                                        switch($ondelete) {
                                            case 'cascade':
                                                $this->remove($def['foreign_object'], $rel_ids, $permanent);
                                                break;
                                            case 'null':                                                
                                            default:                                                
                                                $this->write($def['foreign_object'], $rel_ids, [$def['foreign_field'] => '0']);
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
            }
        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res = $e->getCode();
        }
        return $res;
    }

    /**
     * @param $class    string
     * @param $id       integer
     * @param $values   array           Map of fields to override orinal object values.
     * 
     * @param   string  $lang	        Language under which return fields values (only relevant for multilang fields).
     * @return  mixed   (int or array)  Error code OR resulting associative array.
     */
    public function clone($class, $id, $values=[], $lang=DEFAULT_LANG) {
        $res = 0;

        try {
            $object = &$this->getStaticInstance($class);
            $schema = $object->getSchema();

            $res_r = $this->read($class, $id, array_keys($schema), $lang);

            // if write method generated an error, return error code instead of object id
            if($res_r < 0) {
                throw new Exception('object_not_found', $res_r);
            }

            $original = $res_r[$id];
            $original = array_merge($original, $values);
            unset($original['id']);

            // create new object based on original
            $res_c = $this->create($class, $original, $lang);
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
                    foreach($rel_ids as $oid) {
                        // perform cascade cloning (clone target objects)
                        if(isset($rel_schema[$def['foreign_field']]) ) {
                            $res_c = $this->clone($def['foreign_object'], $oid, $lang);
                            if($res_c > 0) {
                                $new_rel_ids[] = $res_c;
                            }
                        }
                    }
                    // set current clone as target for the relation
                    if(isset($rel_schema[$def['foreign_field']]) ) {
                        $rel_values = [ $rel_schema[$def['foreign_field']] => $res ];
                        if(isset($values['creator'])) {
                            $rel_values['creator'] = $values['creator'];
                        }
                        $this->write($def['foreign_object'], $new_rel_ids, $rel_values, $lang);
                    }
                }
            }
        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res = $e->getCode();
        }            
        return $res;
    }

    /**
     * Search for the objects corresponding to the domain criteria.
     * This method essentially generates an SQL query and returns an array of matching objects ids.
     *
     * 	The domain syntax is : array( array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]]) [, array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]])])
     * 	Array of several series of clauses joined by logical ANDs themselves joined by logical ORs : disjunctions of conjunctions
     * 	i.e.: (clause[, AND clause [, AND ...]]) [ OR (clause[, AND clause [, AND ...]]) [ OR ...]]
     *
     * 	accepted operators are : '=', '<', '>',' <=', '>=', '<>', 'like' (case-sensitive), 'ilike' (case-insensitive), 'in', 'contains'
     * 	example : array( array( array('title', 'like', '%foo%'), array('id', 'in', array(1,2,18)) ) )
     *
     *
     * @param   string     $class
     * @param   array      $domain
     * @param   array      $sort           array of fields which result have to be sorted on, possibly precedded by sign (+ for 'asc', - for 'desc')
     * @param   integer    $start
     * @param   string     $limit
     * @return  mixed      (integer or array)
     */
    public function search($class, $domain=NULL, $sort=['id' => 'asc'], $start='0', $limit='0', $lang=DEFAULT_LANG) {
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();

        try {
            if(empty($sort)) throw new Exception("sorting order field cannot be empty", QN_ERROR_MISSING_PARAM);

            // check and fix domain format
            if($domain && !is_array($domain)) throw new Exception("if domain is specified, it must be an array", QN_ERROR_INVALID_PARAM);
            else if($domain) {
                // valid format : [[['field', 'operator', 'value']]]
                // accepted shortcuts: [['field', 'operator', 'value']], ['field', 'operator', 'value']
                if( !is_array($domain[0]) ) $domain = array(array($domain));
                else if( isset($domain[0][0]) && !is_array($domain[0][0]) ) $domain = array($domain);
            }

            $res_list = array();

            $conditions = array(array());
            // join conditions that have to be additionaly applied to all clauses
            $join_conditions = array();
            $tables = array();

            // we use a nested closure to define a function that stores original table names and returns corresponding aliases
            $add_table = function ($table_name) use (&$tables) {
                if(in_array($table_name, $tables)) return array_search($table_name, $tables);
                $table_alias = 't'.count($tables);
                $tables[$table_alias] = $table_name;
                return $table_alias;
            };

            $schema = $this->getObjectSchema($class);
            $table_alias = $add_table($this->getObjectTableName($class));

            // first pass : build conditions and the tables names arrays
            if(!empty($domain) && !empty($domain[0]) && !empty($domain[0][0])) { // domain structure is correct and contains at least one condition

                // we check, for each clause, if it's about a "special field"
                $special_fields = Model::getSpecialColumns();

                for($j = 0, $max_j = count($domain); $j < $max_j; ++$j) {
// #todo : join conditions should be set at clause level (but at some history point it was set at domain level) - to confirm
                    $join_conditions = array();

                    for($i = 0, $max_i = count($domain[$j]); $i < $max_i; ++$i) {
                        if(!isset($domain[$j][$i]) || !is_array($domain[$j][$i])) throw new Exception("malformed domain", QN_ERROR_INVALID_PARAM);
                        if(!isset($domain[$j][$i][0]) || !isset($domain[$j][$i][1]) || !isset($domain[$j][$i][2])) throw new Exception("invalid domain, a mandatory attribute is missing", QN_ERROR_INVALID_PARAM);
                        $field		= $domain[$j][$i][0];
                        $value		= $domain[$j][$i][2];
                        $operator	= strtolower($domain[$j][$i][1]);

                        // check field validity
                        if(!in_array($field, array_keys($schema))) throw new Exception("invalid domain, unexisting field '$field' for object '$class'", QN_ERROR_INVALID_PARAM);
                        // get final target field
                        while($schema[$field]['type'] == 'alias') {
                            $field = $schema[$field]['alias'];
                            if(!in_array($field, array_keys($schema))) throw new Exception("invalid schema, unexisting field '$field' for object '$class'", QN_ERROR_INVALID_PARAM);
                        }
                        // get final type
                        $type = $schema[$field]['type'];
                        if($type == 'computed') {
                            if(!isset($schema[$field]['result_type'])) throw new Exception("invalid schema, missing result_type for field '$field' of object '$class'", QN_ERROR_INVALID_PARAM);
                            $type = $schema[$field]['result_type'];
                        }
                        // check the validity of the field name and the operator
                        if(!self::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory parameter for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
                        if(!in_array($operator, self::$valid_operators[$type])) throw new Exception("invalid domain, unknown operator '$operator' for field '$field' of type '{$schema[$field]['type']}' (result type: $type) in object '$class'", QN_ERROR_INVALID_PARAM);

                        // remember special fields involved in the domain (by removing them from the special_fields list)
                        if(isset($special_fields[$field])) unset($special_fields[$field]);                        

                        // note: we don't test user permissions on foreign objects here
                        switch($type) {
                            case 'many2one':
                                // use operator '=' instead of 'contains' (which is not sql standard)
                                if($operator == 'contains') $operator = '=';
                                $field = $table_alias.'.'.$field;
                                break;
                            case 'one2many':
                                // add foreign table to sql query
                                $foreign_table_alias =  $add_table($this->getObjectTableName($schema[$field]['foreign_object']));
                                // add the join condition
                                $join_conditions[] = array($foreign_table_alias.'.'.$schema[$field]['foreign_field'], '=', '`'.$table_alias.'`.`id`');
                                // as comparison field, use foreign table's 'foreign_key' if any, 'id' otherwise
                                if(isset($schema[$field]['foreign_key'])) $field = $foreign_table_alias.'.'.$schema[$field]['foreign_key'];
                                else $field = $foreign_table_alias.'.id';
                                // use operator 'in' instead of 'contains' (which is not sql standard)
                                if($operator == 'contains') $operator = 'in';
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
                                if($operator == 'contains') $operator = 'in';
                                break;
                            default:
                                // adapt value
                                if(!is_array($value)) {
                                    $value = $this->container->get('adapt')->adapt($value, $type, 'php', 'txt');
                                    $value = $this->container->get('adapt')->adapt($value, $type, 'sql', 'php');    
                                }
                                // add some conditions if field is multilang (and the search is made on another language than the default one)
                                if($lang != DEFAULT_LANG && isset($schema[$field]['multilang']) && $schema[$field]['multilang']) {
                                    $translation_table_alias = $add_table('core_translation');
                                    // add join conditions
                                    $conditions[$j][] = array($table_alias.'.id', '=', '`'.$translation_table_alias.'.object_id`');
                                    $conditions[$j][] = array($translation_table_alias.'.object_class', '=', $class);
                                    $conditions[$j][] = array($translation_table_alias.'.object_field', '=', $field);
                                    $field = $translation_table_alias.'.value';
                                }
                                // simple fields always match table fields
                                else $field = $table_alias.'.'.$field;
                                break;
                        }
                        // handle particular cases involving arrays
                        // if(in_array($type, ['many2one', 'one2many', 'many2many'])) {
                            if( in_array($operator, ['in', 'not in']) ) {
                                if(!is_array($value)) $value = array($value);
                                if(!count($value))    $value = ['0'];
                            }
                        // }
                        $conditions[$j][] = array($field, $operator, $value);
                    }
                    // search only among non-draft and non-deleted records
                    // (unless at least one clause was related to those fields - and consequently corresponding key in array $special_fields has been unset in the code above)
                    if(isset($special_fields['state']))	    $conditions[$j][] = array($table_alias.'.state', '=', 'instance');
                    if(isset($special_fields['deleted']))	$conditions[$j][] = array($table_alias.'.deleted', '=', '0');
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
            if(!is_array($sort)) $sort = (array) $sort;

            // if invalid order field is given, fallback to 'id'
            foreach($sort as $sort_field => $sort_order) {
                if(!isset($schema[$sort_field]) || ( $schema[$sort_field]['type'] == 'computed' && (!isset($schema[$sort_field]['store']) || !$schema[$sort_field]['store']) )) {
                    trigger_error("invalid order field '$sort_field' for class '$class'", E_USER_WARNING);
                    // $order = 'id';
                    $sort_field = 'id';
                    $sort_order = 'asc';
                }
                while($schema[$sort_field]['type'] == 'alias') {
                    $sort_field = $schema[$sort_field]['alias'];
                }
                // we might need to request more than the id field (for example, for sorting purpose)
                if($lang != DEFAULT_LANG && isset($schema[$sort_field]['multilang']) && $schema[$sort_field]['multilang']) {
// #todo : validate this code (we should probably add join conditions in some cases)
                    $translation_table_alias = $add_table('core_translation');
                    $select_fields[] = $translation_table_alias.'.value';
                    $order_table_alias = $translation_table_alias;
                    $sort_field = 'value';
                }
                else if($sort_field != 'id') {
                    $select_fields[] = $table_alias.'.'.$sort_field;
                }
                $order_clause[$order_table_alias.'.'.$sort_field] = $sort_order;
            }
            // make sure id field is amongst the sort fields
            if(!in_array('id', array_keys($sort))) {
                $order_clause[$table_alias.'.id'] = 'ASC';
            }
            // get the matching records by generating the resulting SQL query
            $res = $db->getRecords($tables, $select_fields, NULL, $conditions, $table_alias.'.id', $order_clause, $start, $limit);
            while ($row = $db->fetchArray($res)) {
                // maintain ids order provided by the SQL sort
                $res_list[] = $row['id'];
            }
            // remove duplicates, if any
            $res_list = array_unique($res_list);
            // mark resulting identifiers as safe (matching existing objets)
            foreach($res_list as $object_id) {
                $this->identifiers[$class][$object_id] = true;
            }
        }
        catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            $res_list = $e->getCode();
        }
        return $res_list;
    }
}