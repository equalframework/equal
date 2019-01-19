<?php
/**
*	This file is part of the easyObject project.
*	http://www.cedricfrancoys.be/easyobject
*
*	Copyright (C) 2012  Cedric Francoys
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace qinoa\orm;

use qinoa\organic\Service;

use qinoa\db\DBConnection;

use \Exception as Exception;



class ObjectManager extends Service {
    /* Buffer for storing objects values as they are loaded
    *  structure is defined this way:
    *  $cache[$class][$oid][$field][$lang] = $value;
    */
    private $cache;
    
    /* Array for keeeping track of identifiers matching actual objects
    *  structure is defined this way:
    *  $identifiers[$object_class][$object_id] = true;
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
	public static $simple_types	 = array('boolean', 'integer', 'float', 'string', 'text', 'html', 'array', 'date', 'time', 'datetime', 'file', 'binary', 'many2one');
	public static $complex_types = array('one2many', 'many2many', 'function');

// todo : add large integer support (using GNU GMP library)    
	public static $valid_attributes = array(
			'alias'		    => array('type', 'alias'),    
			'boolean'		=> array('type', 'onchange'),
			'integer'		=> array('type', 'onchange', 'selection', 'unique'),
			'float'			=> array('type', 'onchange', 'precision'),
			'string'		=> array('type', 'onchange', 'multilang', 'selection', 'unique'),
			'text'			=> array('type', 'onchange', 'multilang'),
			'html'			=> array('type', 'onchange', 'multilang'),            
			'date'			=> array('type', 'onchange'),
			'time'			=> array('type', 'onchange'),
			'datetime'		=> array('type', 'onchange'),
			'file'  		=> array('type', 'onchange', 'multilang'),           
			'binary'		=> array('type', 'onchange', 'multilang'),
			'many2one'		=> array('type', 'foreign_object', 'onchange', 'multilang'),
			'one2many'		=> array('type', 'foreign_object', 'foreign_field', 'onchange', 'order', 'sort'),
			'many2many'		=> array('type', 'foreign_object', 'foreign_field', 'rel_table', 'rel_local_key', 'rel_foreign_key', 'onchange'),
			'function'		=> array('type', 'result_type', 'function', 'onchange', 'store', 'multilang')
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
			'function'		=> array('type', 'result_type', 'function')
	);

    public static $valid_operators = array(
            'boolean'		=> array('=', '<>', '<', '>'),
            'integer'		=> array('in', 'not in', '=', '<>', '<', '>', '<=', '>='),
            'float'			=> array('=', '<>', '<', '>', '<=', '>='),
            'string'		=> array('like', 'ilike', 'in', '=', '<>'),
            'text'			=> array('like', 'ilike', '='),
            'html'			=> array('like', 'ilike', '='),                
            'date'			=> array('=', '<>', '<', '>', '<=', '>=', 'like'),
            'time'			=> array('=', '<>', '<', '>', '<=', '>='),
            'datetime'		=> array('=', '<>', '<', '>', '<=', '>='),
            'file'		    => array('like', 'ilike', '='),                
            'binary'		=> array('like', 'ilike', '='),
            // for compatibilty reasons, 'contains' is allowed for many2one field (in such case 'contains' operator means 'list contains *at least one* of the following ids')
            'many2one'		=> array('is', 'in', '=', '<>', 'contains'),
            'one2many'		=> array('contains'),
            'many2many'		=> array('contains'),
    );



	protected function __construct(DBConnection $db) {
// todo : declare required constants instead        
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
// todo        
        return [];
    }
    
	/**
	* Returns the instance of the Manager.
	* The instance is stored in the $GLOBALS array and is created at first call to this method.
	*
	* @return object
	*/
/*    
	public static function &getInstance()	{
		if (!isset($GLOBALS['ObjectManager_instance'])) $GLOBALS['ObjectManager_instance'] = new ObjectManager();
		return $GLOBALS['ObjectManager_instance'];
	}
    */

    public function getDB() {
        return $this->db;        
    }
    
    private function getDBHandler() {
/*        
        if(!$this->db) {
            if(!defined('DB_HOST') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_DBMS')) {
                // fatal error
                trigger_error(	'Error raised by '.__CLASS__.'::'.__METHOD__.'@'.__LINE__.' : '.
                                'unable to establish connection to database: check config connection parameters '.
                                '(possibles reasons: non-supported DBMS, unknown database name, incorrect username or password, ...)', 
                                E_USER_ERROR);                
            }
            $this->db = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);
        }
*/        
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
            $package_directory = 'packages';
            if(is_dir($package_directory) && ($list = scandir($package_directory))) {
                foreach($list as $node) if(is_dir($package_directory.'/'.$node) && !in_array($node, array('.', '..'))) $this->packages[] = $node;
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
	private function &getStaticInstance($class) {
        // if class is unknown, load the file containing the class declaration of the requested object
        if(!class_exists($class)) {
            // first, read the file to see if the class extends from another (which could not be loaded yet)
            $filename = 'packages/'.self::getObjectPackageName($class).'/classes/'.self::getObjectName($class).'.class.php';
            if(!is_file($filename)) {
                // we might find ourselves in the 'private' directory (where classes definition is not mandatory)
                $filename = '../public/'.$filename;
                if(!is_file($filename)) throw new Exception("unknown model: '$class'", QN_ERROR_UNKNOWN_OBJECT);
            }
            preg_match('/\bextends\b(.*)\{/iU', file_get_contents($filename), $matches);
            if(!isset($matches[1])) throw new Exception("malformed class file for model '$class': parent class name not found in file", QN_ERROR_INVALID_PARAM);
            else $parent_class = trim($matches[1]);
            if(!(include $filename)) throw new Exception("unknown model: '$class'", QN_ERROR_UNKNOWN_OBJECT);
        }
        if(!isset($this->instances[$class])) $this->instances[$class] = new $class();
        return $this->instances[$class];
	}

	
	/**
	* Gets the name of the table associated to the specified class (required to convert namespace notation).
	*
	* @param string $object_class
	* @return string
	*/
	public function getObjectTableName($object_class) {
		try {
			$object = &$this->getStaticInstance($object_class);
		}
		catch(Exception $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
			return $e->getCode();
		}
		return $object->getTable();
	}

	/**
	* Gets the filename containing the class definition of an object, including a part of its path (required to convert namespace notation).
	*
	* @param string $object_class
	* @return string
	*/
	public static function getObjectClassFileName($object_class) {
		return str_replace('\\', '/', $object_class);
	}

	/**
	* Gets the package in which is defined the class of an object (required to convert namespace notation).
	*
	* @param string $object_class
	* @return string
	*/
	public static function getObjectPackageName($object_class) {
		return str_replace('\\', '', substr($object_class, 0, strrpos($object_class, '\\')));
	}

   	/**
	* Gets the name of the object (equivalent to its class without namespace / package).
	*
	* @param string $object_class
	* @return string
	*/
	public static function getObjectName($object_class) {
		return substr($object_class, strrpos($object_class, '\\')+1);
	}

	/**
	* Gets the complete schema of an object (including special fields).
	* note: this method is not set as static since we need to load class file in order to retrieve the schema
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
    * Filters given object ids and returns on valid identifiers (from existing objects)
    *
    * Ids that do not match an object in the database are removed from the list.
    */
    private function filterValidIdentifiers($class, $ids) {
        $valid_ids = [];
        // remove duplicate ids, if any        
        $ids = array_unique($ids);
        // ensure ids are positive numeric values
        foreach($ids as $key => $oid) {
            $ids[$key] = intval($oid);
            if($ids[$key] <= 0) unset($ids[$key]);
        }                
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
                $valid_ids[] = $row['id'];
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
			'multilang'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
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
// todo : by default, we should do nothing, to maintain performance - and allow user to pickup a method among some pre-defined default conversions
						// $value = DataAdapter::adapt('db', 'orm', $schema[$field]['type'], $row['value'], $class, $oid, $field, $lang);                        
                        $value = $this->container->get('adapt')->adapt($row['value'], $schema[$field]['type'], 'php', 'sql', $class, $oid, $field, $lang);
						// update the internal buffer with fetched value
						$om->cache[$class][$oid][$field][$lang] = $value;
					}
					// force assignment to NULL if no result was returned by the SQL query
					foreach($ids as $oid) {
						foreach($fields as $field) {
							if(!isset($om->cache[$class][$oid][$field][$lang])) $om->cache[$class][$oid][$field][$lang] = NULL;
						}
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);                    
				}
			},
			'simple'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
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
							$om->cache[$class][$oid][$field][$lang] = $value;
						}
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'one2many'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
					foreach($fields as $field) {
						if(!ObjectManager::checkFieldAttributes(ObjectManager::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
						$order = (isset($schema[$field]['order']))?$schema[$field]['order']:'id';
						$sort = (isset($schema[$field]['sort']))?$schema[$field]['sort']:'asc';
// todo : handle alias fields (require subsequent schema)
						// obtain the ids by searching inside the foreign object's table
						$result = $om->db->getRecords(	
							$om->getObjectTableName($schema[$field]['foreign_object']), 
							array('id', $schema[$field]['foreign_field'], $order), 
							NULL, 
							array(array(
									array($schema[$field]['foreign_field'], 'in', $ids),
									array('state', '<>', 'draft'),
									array('deleted', '=', '0'),																						
								)
							), 
							'id',
							[$order => $sort]
						);
						$lists = array();
						while ($row = $om->db->fetchArray($result)) {
							$oid = $row[$schema[$field]['foreign_field']];
							$lists[$oid][] = $row['id'];
						}
						foreach($ids as $oid) $om->cache[$class][$oid][$field][$lang] = (isset($lists[$oid]))?$lists[$oid]:[];                        
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'many2many'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
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
									array('t0.state', '<>', 'draft'),
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
						foreach($ids as $oid) $om->cache[$class][$oid][$field][$lang] = (isset($lists[$oid]))?$lists[$oid]:[];                        
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'function'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
					foreach($fields as $field) {
						if(!ObjectManager::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory attribute for field '$field' of class '$class'", QN_ERROR_INVALID_PARAM);
						if(!is_callable($schema[$field]['function'])) throw new Exception("error in schema parameter for function field '$field' of class '$class' : function cannot be called");
						$res = call_user_func($schema[$field]['function'], $om, $ids, $lang);
						foreach($ids as $oid) {
                            if(isset($res[$oid][$field])) {
                                $value = $this->container->get('adapt')->adapt($res[$oid][$field], $schema[$field]['result_type'], 'php', 'sql', $class, $oid, $field, $lang);
                            }
                            else {
                                $value = null;
                            }
                            $om->cache[$class][$oid][$field][$lang] = $value;
                        }
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			});

			// build an associative array ordering given fields by their type
			$fields_lists = array();
			// remember computed fields having store attibute set (we need this as $type will hold the $schema['result_type'] value)
			$stored_fields = array();

			// 1) retrieve fields types
			foreach($fields as $key => $field) {
                // retrieve field type
				$type = $schema[$field]['type'];
				// stored computed fields are handled following their resulting type
				if( $type == 'function'
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
                // (this implies that the DB schema has 'DEFAULT NULL' for the associated column)
				foreach($ids as $oid) if(is_null($this->cache[$class][$oid][$field][$lang])) $oids[] = $oid;
				// compute field for incomplete objects
				$load_fields['function']($this, $oids, array($field));
				// store newly computed fields to database (to avoid computing them at each object load)
				$this->store($class, $oids, array($field), $lang);
			}

		}
		catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
			throw new Exception('unable to load object fields', $e->getCode());
		}
	}

	/*
		Stores specified fields of selected objects into database

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
			'multilang'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
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
                                $this->container->get('adapt')->adapt($om->cache[$class][$oid][$field][$lang], $schema[$field]['type'], 'sql', 'php', $class, $oid, $field, $lang)
                            ];
                        }
                    }
                    $om->db->addRecords(
                        'core_translation', 
                        array('language', 'object_class', 'object_field', 'object_id', 'value'), 
                        $values_array
                    );                    
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'simple'	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
					foreach($ids as $oid) {
						$fields_values = array();
						foreach($fields as $field) {                            
                            // $fields_values[$field] = DataAdapter::adapt('orm', 'db', $schema[$field]['type'], $om->cache[$class][$oid][$field][$lang], $class, $oid, $field, $lang);
                            $fields_values[$field] = $this->container->get('adapt')->adapt($om->cache[$class][$oid][$field][$lang], $schema[$field]['type'], 'sql', 'php', $class, $oid, $field, $lang);
                        }
						$om->db->setRecords($om->getObjectTableName($class), array($oid), $fields_values);
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'one2many' 	=>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
					foreach($ids as $oid) {
						foreach($fields as $field) {
							$value = $om->cache[$class][$oid][$field][$lang];
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
							unset($om->cache[$class][$oid][$field][$lang]);
						}
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'many2many' =>	function($om, $ids, $fields) use ($schema, $class, $lang){
				try {
					foreach($ids as $oid) {
						foreach($fields as $field) {
							$value = $om->cache[$class][$oid][$field][$lang];
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
							unset($om->cache[$class][$oid][$field][$lang]);													
						}
					}
				}
				catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
				}
			},
			'function' =>	function($om, $ids, $fields) use ($schema, $class, $lang){
				// nothing to store for computed fields (ending up here means that the 'store' attribute is not set)
			});

			// build an associative array ordering given fields by their type
			$fields_lists = array();
            
			// 1) retrieve fields types
			foreach($fields as $field) {
                // retrieve field type
				$type = $schema[$field]['type'];
				// stored computed fields are handled following their resulting type
				if( $type == 'function'
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
		}
	}


	public function &getStatic($object_class) {
        $instance = false;        
        $packages = $this->getPackages();
        $package = self::getObjectPackageName($object_class);
        if(in_array($package, $packages)) {
            try {
                $instance = &$this->getStaticInstance($object_class);
            }
            catch(Exception $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }
		return $instance;
	}
    
    // alias for getStatic
    public function &getModel($object_class) {
        $instance = $this->getStatic($object_class);
        return $instance;
    }

	/**
	* Checks whether the values of given object fields are valid or not.
	* Checks if the given array contains valid values for related fields.
	* This is done using the class validation method.
	* Returns an associative array containing invalid fields with their associated error_message_id 
    * (an empty array means all fields are valid)

	* @param string $class object class
	* @param array $values
	* @return array resulting associative array
	*/
	public function validate($class, $values) {
// todo : check unicity in case the 'unique' attribute is set in field description        
// todo : based on types, check that given value is not bigger than storage capacity (DBMS type)
		$res = array();

        $static_instance = &$this->getStaticInstance($class);	
        if(method_exists($static_instance, 'getConstraints')) {
            $constraints = $static_instance->getConstraints();
            //(unexisting fields are ignored by write method)
            foreach($values as $field => $value) {
                if(isset($constraints[$field]) 
                && isset($constraints[$field]['function']) ) {
                    $validation_func = $constraints[$field]['function'];
                    if(is_callable($validation_func) && !call_user_func($validation_func, $value)) {
                        if(!isset($constraints[$field]['error_message_id'])) $res[$field] = 'invalid';
                        else $res[$field] = $constraints[$field]['error_message_id'];
                    }
                }
            }
        }

		return $res;
	}


	/*
    if $fields is empty, a draft object is created 
    if $field contains some values, object is created and its state is set to 'instance' (@see write method)
todo : to validate
	*/
	public function create($class, $fields=NULL, $lang=DEFAULT_LANG) {
		$res = 0;
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();
        
		try {
			$object = &$this->getStaticInstance($class);
            
			$object_table = $this->getObjectTableName($class);
			// $creation_array = array('created' => date("Y-m-d H:i:s"), 'creator' => $uid);
			$creation_array = array_merge( ['created' => date("Y-m-d H:i:s"), 'state' => 'draft'], $object->getValues() );
            
			// list ids of records having creation date older than DRAFT_VALIDITY
            // $ids = $this->search($uid, $class, array(array(array('state', '=', 'draft'),array('created', '<', date("Y-m-d H:i:s", time()-(3600*24*DRAFT_VALIDITY))))), 'id', 'asc'); 
            $ids = $this->search($class, [['state', '=', 'draft'],['created', '<', date("Y-m-d H:i:s", time()-(3600*24*DRAFT_VALIDITY))]], ['id' => 'asc']);
			// use the oldest expired draft, if any            
			if(!count($ids)) $oid = 0;
            else $oid = $ids[0];
            
			if($oid  > 0) {
				// store the id to reuse
				$creation_array['id'] = $oid;
				// and delete the associated record (which might contain obsolete data)
				$db->deleteRecords($object_table, array($oid));
			}
			// create a new record with the found value, or let the autoincrement do the job
			$db->addRecords($object_table, array_keys($creation_array), array(array_values($creation_array)));
			if($oid <= 0) $oid = $db->getLastId();
            // in any case, we return the object id
			$res = $oid;
            
            // update new object with given fiels values
            
			// check $fields arg validity			
			$allowed_fields = array_diff($object->getFields(), ['id']);

            foreach($fields as $field => $values) {
                // handle 'dot' notation (ignore '.' and following chars)
                $field = explode('.', $field)[0];
                // remove fields not defined in related schema
                if(!in_array($field, $allowed_fields)) {
                    unset($fields[$field]);
                    trigger_error('unknown field ('.$field.') in $fields arg', E_USER_WARNING);
                }
            }
            
            if(!empty($fields)) {
                $res_w = $this->write($class, $oid, $fields, $lang);
                // if write method generated an error, return error code instead of object id
                if($res_w < 0) $res = $res_w;
            }
		}
		catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
			$res = $e->getCode();
		}
		return $res;
	}
	
	/*
todo: signature differs from other methods	(returned value)
		Updates specifield fields of seleced objects
		and stores changes into database
	*/
	public function write($class, $ids=NULL, $fields=NULL, $lang=DEFAULT_LANG) {
		$res = true;
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();
        
		try {
			// 1) do some pre-treatment
            
			if(!is_array($ids))		$ids = (array) $ids;
			// cast fields to an array (passing a single field is accepted)
			if(!is_array($fields))	$fields = (array) $fields;
			// if no ids were specified, we do nothing
			if(empty($ids))	return $res;
            // get stattic instance (checks that given class exists)
			$object = &$this->getStaticInstance($class);
			// check validity of objects identifiers
            $ids = $this->filterValidIdentifiers($class, $ids);

   			// 2) check $fields arg validity
            
            // remove unknown fields
			$allowed_fields = $object->getFields();
            foreach($fields as $field => $values) {
                // handle 'dot' notation (ignore '.' and following chars)
                $field = explode('.', $field)[0];
                // remove fields not defined in related schema
                if(!in_array($field, $allowed_fields)) {
                    unset($fields[$field]);
                    trigger_error('unknown field ('.$field.') in $fields arg', E_USER_WARNING);
                }
            }
            $fields = array_merge($fields, ['state' => 'instance', 'modified' => time()]);

            
			// 3) update internal buffer with given values
			$schema = $object->getSchema();
			$onchange_fields = array();
			foreach($ids as $oid) {
				foreach($fields as $field => $value) {
					// remember fields whose modification triggers an onchange event
					if(isset($schema[$field]['onchange'])) $onchange_fields[] = $field;
					// $this->cache[$class][$oid][$field][$lang] = DataAdapter::adapt('ui', 'orm', $schema[$field]['type'], $value, $class, $oid, $field, $lang);
                    $this->cache[$class][$oid][$field][$lang] = $value;
				}
			}

			
			// 4) write selected fields to DB
			$this->store($class, $ids, array_keys($fields), $lang);

			// second pass : handle onchange events, if any 
			// note : this must be done after modifications otherwise object values might be outdated
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
	* Note : The process maintains $ids and $fields order.
	*
	* @param string	$class	class of the objects to retrieve
	* @param mixed	$ids	identifier(s) of the object(s) to retrieve (accepted types: array, integer, string)
	* @param mixed	$fields	name(s) of the field(s) to retrieve (accepted types: array, string)
	* @param string $lang	language under which return fields values (only relevant for multilang fields)
	* @return mixed (int or array) error code OR resulting associative array
	*/
	public function read($class, $ids=null, $fields=null, $lang=DEFAULT_LANG) {

		$res = array();
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();
        
		try {
			// 1) do some pre-treatment
            
            if(!isset($ids)) {
                throw new Exception('missing mandatory $ids argument', QN_ERROR_MISSING_PARAM);
            }            
			// force cast ids to array (passing a single id is accepted)
			if(!is_array($ids)) {
                $ids = (array) $ids;
            }
			// force cast fields to array (passing a single field is accepted)
			if(!is_array($fields)) {
                $fields = (array) $fields;
            }
            // get static instance (checks that given class exists)
			$object = &$this->getStaticInstance($class);
			// checks validity of objects identifiers
            $ids = $this->filterValidIdentifiers($class, $ids);
            // if no ids were specified, the result is an empty list (array)
			if(empty($ids)) return $res;

            // 2) check $fields arg validity

            $schema = $object->getSchema();            
            $requested_fields = [];
            // check fields validity
            foreach($fields as $key => $field) {
                // invalid field
                if(!isset($schema[$field])) {
                    // drop invalid fields
                    unset($fields[$key]);
                    trigger_error("unknown field '$field' for class : '$class'", E_USER_WARNING);
                }
                else {
                    // handle aliases
                    if($schema[$field]['type'] == 'alias') {
                        // assign fields array with target field
                        $requested_fields[] = $schema[$field]['alias'];
                    }
                    else {
                        $requested_fields[] = $field;
                    }
                }
            }
            
			// remove duplicate fields, if any 
            // removed: this is not an issue, since the value will be loaded once and returned as many times as requested
              
			// 3) check among requested fields wich ones are not yet present in the internal buffer
			// if internal buffer is empty, query the DB to load all fields from requested objects 
			if(empty($this->cache) || !isset($this->cache[$class])) $this->load($class, $ids, $requested_fields, $lang);			
            // check if all objects are fully loaded            
			else {
                // build the missing fields array by increment 
                // we need the 'broadest' fields array to be loaded to minimize # of SQL queries
                $fields_missing = array();
                foreach($requested_fields as $key => $field) {
                    foreach($ids as $oid) {                   
                        if(!isset($this->cache[$class][$oid][$field][$lang])) {                            
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
                    trigger_error("unknown object #'$oid' for class : '$class'", E_USER_WARNING);
                    continue;
                }
                // init result for given id, if missing
                if(!isset($res[$oid])) $res[$oid] = array();
                // first pass : retrieve fields values
                foreach($fields as $key => $field) {
                    // handle aliases                                                
                    if($schema[$field]['type'] == 'alias') {
                        $res[$oid][$field] = $this->cache[$class][$oid][$schema[$field]['alias']][$lang];
                    }
                    else {
                        // use final notation 
                        $res[$oid][$field] = $this->cache[$class][$oid][$field][$lang];
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
	* Deletes an object permanently or puts it in the "trash bin" (ie setting the 'deleted' flag to 1).
	* The returned structure is an associative array containing ids of the objects actually deleted.
	*
	* @param string $object_class object class
	* @param array $ids ids of the objects to remove
	* @param boolean $permanent
	* @return mixed (integer or array)
	*/
	public function remove($object_class, $ids, $permanent=false) {
        // get DB handler (init DB connection if necessary)
        $db = $this->getDBHandler();
        $result = [];
        
		try {
            // cast ids to an array (passing a single id is accepted)
			if(!is_array($ids)) $ids = [$ids];            
            // ensure ids are numerical values
            foreach($ids as $key => $oid) {      
                 if(!is_numeric($oid)) unset($ids[$key]);
            }
         
            if(empty($ids)) throw new Exception("argument is not an array of objects identifiers (emtpy array or wrong type): '$ids'", QN_ERROR_INVALID_PARAM);
            
			// 1) check rights and object schema
			$object = &$this->getStaticInstance($object_class);
			$schema = $object->getSchema();
			// checks validity of objects identifiers
            $ids = $this->filterValidIdentifiers($object_class, $ids);
            $result = $ids;
            
			// 2) remove object from DB
			$table_name = $this->getObjectTableName($object_class);
			if (!$permanent) {
                // soft deletion
                $db->setRecords($table_name, $ids, ['deleted' => 1]);
            }
            else {
                // hard deletion
				$db->deleteRecords($table_name, $ids);
                // 3) cascade deletions / relations updates
                foreach($schema as $field => $def) {
                    if(in_array($def['type'], ['one2many', 'many2many'])) {
                        switch($def['type']) {
                        case 'one2many':
                            $res = $this->read($object_class, $ids, $field);
                            $rel_ids = array_map(function ($item) use($field) { return $item[$field];}, array_values($res));
                            // foregin field is many2one
                            $rel_schema = $this->getObjectSchema($def['foreign_object']);
                            // call ondelete method when defined (to allow cascade deletion)
                            if(isset($rel_schema[$def['foreign_field']]['ondelete'])) {
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
                                            $this->write($def['foreign_object'], $rel_ids, [$def['foreign_field'] => '0']);
                                            break;
                                        default:
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
			$result = $e->getCode();
		}
		return $result;	
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
	* @param integer    $user_id
	* @param string     $object_class
	* @param array      $domain
	* @param array      $sort           array of fields which result have to be sorted on, possibly precedded by sign (+ for 'asc', - for 'desc')
	* @param integer    $start
	* @param string     $limit
	* @return mixed (integer or array)
	*/    
	public function search($object_class, $domain=NULL, $sort=['id' => 'asc'], $start='0', $limit='0', $lang=DEFAULT_LANG) {
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
			$tables = array();

			// we use a nested closure to define a function that stores original table names and returns corresponding aliases
			$add_table = function ($table_name) use (&$tables) {
				if(in_array($table_name, $tables)) return array_search($table_name, $tables);
				$table_alias = 't'.count($tables);
				$tables[$table_alias] = $table_name;
				return $table_alias;
			};

			$schema = $this->getObjectSchema($object_class);
			$table_alias = $add_table($this->getObjectTableName($object_class));

			// first pass : build conditions and the tables names arrays
			if(!empty($domain) && !empty($domain[0]) && !empty($domain[0][0])) { // domain structure is correct and contains at least one condition

				// we check, for each clause, if it's about a "special field"
				$special_fields = Model::getSpecialColumns();

				for($j = 0, $max_j = count($domain); $j < $max_j; ++$j) {
					for($i = 0, $max_i = count($domain[$j]); $i < $max_i; ++$i) {
						if(!isset($domain[$j][$i]) || !is_array($domain[$j][$i])) throw new Exception("malformed domain", QN_ERROR_INVALID_PARAM);
						if(!isset($domain[$j][$i][0]) || !isset($domain[$j][$i][1]) || !isset($domain[$j][$i][2])) throw new Exception("invalid domain, a mandatory attribute is missing", QN_ERROR_INVALID_PARAM);
						$field		= $domain[$j][$i][0];
                        $value		= $domain[$j][$i][2];                            
						$operator	= strtolower($domain[$j][$i][1]);						
                        
                        // check field validity
						if(!in_array($field, array_keys($schema))) throw new Exception("invalid domain, unexisting field '$field' for object '$object_class'", QN_ERROR_INVALID_PARAM);
                        // get final target field
                        if($schema[$field]['type'] == 'alias') {
                            $field = $schema[$field]['alias'];
                            if(!in_array($field, array_keys($schema))) throw new Exception("invalid domain, unexisting field '$field' for object '$object_class'", QN_ERROR_INVALID_PARAM);
                        }
                        // get final type
                        $type = $schema[$field]['type'];
						if($type == 'function') $type = $schema[$field]['result_type'];                        
                        // check the validity of the field name and the operator
                        if(!self::checkFieldAttributes(self::$mandatory_attributes, $schema, $field)) throw new Exception("missing at least one mandatory parameter for field '$field' of class '$object_class'", QN_ERROR_INVALID_PARAM);						                        
						if(!in_array($operator, self::$valid_operators[$type])) throw new Exception("invalid operator '$operator' for field '$field' of type '{$schema[$field]['type']}' (result type: $type) in object '$object_class'", QN_ERROR_INVALID_PARAM);						
						
                        // remember special fields involved in the domain (by removing them from the special_fields list)
						if(isset($special_fields[$field])) unset($special_fields[$field]);

						// note: we don't test user permissions on foreign objects here
						switch($type) {
							case 'many2one':
								// use operator '=' instead of 'contains' (which is not sql standard)
								if($operator == 'contains') $operator = '=';
								break;
							case 'one2many':
								// add foreign table to sql query
								$foreign_table_alias =  $add_table($this->getObjectTableName($schema[$field]['foreign_object']));
								// add the join condition
								$conditions[$j][] = array($foreign_table_alias.'.'.$schema[$field]['foreign_field'], '=', '`'.$table_alias.'`.`id`');
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
								if($schema[$field]['foreign_object'] == $object_class) {
									// add the join condition on 'rel_foreign_key'
									$conditions[$j][] = array($table_alias.'.id', '=', '`'.$rel_table_alias.'`.`'.$schema[$field]['rel_foreign_key'].'`');
									// use 'rel_local_key' column as comparison field
									$field = $rel_table_alias.'.'.$schema[$field]['rel_local_key'];
								}
								else {
									// add the join condition on 'rel_local_key'
									$conditions[$j][] = array($table_alias.'.id', '=', '`'.$rel_table_alias.'`.`'.$schema[$field]['rel_local_key'].'`');
									// use 'rel_foreign_key' column as comparison field
									$field = $rel_table_alias.'.'.$schema[$field]['rel_foreign_key'];
								}
								// use operator 'in' instead of 'contains' (which is not sql standard)
								if($operator == 'contains') $operator = 'in';
								break;
							default:
								// add some conditions if field is multilang (and the search is made on another language than the default one)
								if($lang != DEFAULT_LANG && isset($schema[$field]['multilang']) && $schema[$field]['multilang']) {
									$translation_table_alias = $add_table('core_translation');
									// add joint conditions
									$conditions[$j][] = array($table_alias.'.id', '=', '`'.$translation_table_alias.'.object_id`');
									$conditions[$j][] = array($translation_table_alias.'.object_class', '=', $object_class);
									$conditions[$j][] = array($translation_table_alias.'.object_field', '=', $field);
									$field = $translation_table_alias.'.value';
								}
								// simple fields always match table fields
								else $field = $table_alias.'.'.$field;
								break;
						}
                        // handle particular cases involving arrays
                        if(in_array($type, ['many2one', 'one2many', 'many2many'])) {
                            if( in_array($operator, ['in', 'not in']) ) {
                                if(!is_array($value)) $value = array($value);
                                if(!count($value))    $value = ['0'];
                            }
                        }
						$conditions[$j][] = array($field, $operator, $value);
					}
					// search only among non-draft and non-deleted records
					// (unless at least one clause was related to those fields - and consequently corresponding key in array $special_fields has been unset in the code above)
					if(isset($special_fields['state']))	    $conditions[$j][] = array($table_alias.'.state', '<>', 'draft');
					if(isset($special_fields['deleted']))	$conditions[$j][] = array($table_alias.'.deleted', '=', '0');
				}
			}
			else { // no domain is specified
				// search only among non-draft and non-deleted records
				$conditions[0][] = array($table_alias.'.state', '<>', 'draft');
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
                if(!isset($schema[$sort_field]) || ($schema[$sort_field]['type'] == 'function' && (!isset($schema[$sort_field]['store']) || !$schema[$sort_field]['store']) )) {
                    trigger_error("invalid order field '$sort_field' for class '$object_class'", E_USER_WARNING);
                    // $order = 'id';
                    $sort_field = 'id';
                    $sort_order = 'asc';
                }
                if($schema[$sort_field]['type'] == 'alias') {
                    $sort_field = $schema[$sort_field]['alias'];
                }
                // we might need to request more than the id field (for example, for sorting purpose)
                if($lang != DEFAULT_LANG && isset($schema[$sort_field]['multilang']) && $schema[$sort_field]['multilang']) {
// todo : validate this code (we should probabily add join conditions in some cases)
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
                $this->identifiers[$object_class][$object_id] = true;
            }
		}
		catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
			$res_list = $e->getCode();
		}
		return $res_list;
	}
}