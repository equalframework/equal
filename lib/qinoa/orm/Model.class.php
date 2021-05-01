<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\orm;


/**
 *  Root Model for all objects.
 *	This class holds the description of an object (and not the object itself)
 */
class Model {
	
	/**
	 * Complete object schema, containing all columns (including special ones as object id)
	 *
	 * @var array
	 * @access private
	 */
	private $schema;
    
    private $fields;

    private $values;
    
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public final function __construct() {
        // schema is the concatenation of spcecial-columns and custom-defined columns
		$this->schema = self::getSpecialColumns();
		
		// piles up the getColumns methods from oldest ancestor to called class
		$parent_class = get_parent_class(get_called_class());
		$parents_classes = [get_called_class()];
		while( $parent_class != __CLASS__) {
			array_unshift($parents_classes, $parent_class);
			$parent_class = get_parent_class($parent_class);
		}
		foreach($parents_classes as $class) {
			$this->schema = array_merge($this->schema, call_user_func_array([$class, 'getColumns'], []));
		}		
		
        // make sure that a field 'name' is always defined 
		if( !isset($this->schema['name']) ) {
            // if no field 'name' is defined, fall back to 'id' field
            $this->schema['name'] = array( 'type' => 'alias', 'alias' => 'id' );
        }
        // set array holding fields names
        $this->fields = array_keys($this->schema);
        
        // set fields to default values, if any
		$this->setDefaults();
	}

	private final function setDefaults() {
        $this->values = [];

		$defaults = $this->getDefaults();
		// get default values, set fields for default language, and mark fields as modified
		foreach($defaults as $field => $default) {
			if(isset($this->schema[$field])) {
				if(is_callable($default)) {
					$this->values[$field] = call_user_func($default);
				}
				else {
					$this->values[$field] = $default;
				}
			}
		}

	}

	// note: 'name' field is set in constructor if not defined in getColumns method
	public final static function getSpecialColumns() {
		static $special_columns = [
			'id' => [				
				'type'				=> 'integer'
			],            
            'creator' => [
                'type'              => 'many2one',
				'foreign_object'    => 'core\User'
			],
			'created' => [
				'type' 				=> 'datetime'
			],
			'modifier' => [
                'type'              => 'many2one',
				'foreign_object'    => 'core\User'
			],
			'modified' => [
				'type' 				=> 'datetime'
			],
			'deleted' => [
				'type' 				=> 'boolean'
			],
			'state' => [
				'type' 				=> 'string'
			]
		];
		return $special_columns;
	}
    
    public final function setField($field, $value) {
        $this->values[$field] = $value;
    }
    
    public final function setColumn($column, array $description) {
        $this->schema[$column] = $description;
    }

	/**
	 * Gets Model readable name.
	 * This method is meant to be overridden by children classes.
	 * 
	 * @access public
	 * @return array
	 */
	public static function getName() {
		return 'Model';
	}


	/**
	 * Gets Model description.
	 * This method is meant to be overridden by children classes.
	 * 
	 * @access public
	 * @return array
	 */
	public static function getDescription() {
		return 'Interface for model defintion classes.';
	}

	/**
	 * Gets object schema
	 *
	 * @access public
	 * @return array
	 */
	public final function getSchema() {
		return $this->schema;
	}

    /**
	* Returns all fields names 
	*
	*/
	public final function getFields() {
		return $this->fields;
	}	

    /**
     * Provide the final field targeted by a field (handle aliases)
     * This method should be used for type comparisons and when checking field structure validity
     *
     */
    public final function field($field) {
        $type = $this->schema[$field]['type'];
        while($type == 'alias') {
            $field = $this->schema[$field]['alias'];
            $type = $this->schema[$field]['type'];
        }
        return $this->schema[$field];
    }
    
    /**
     * Returns values of static instance (default values)
     *
     */
	public final function getValues() {
		return $this->values;
	}	
    
	/**
 	 * Returns the user-defined part of the schema (i.e. fields list with types and other attributes)
	 * This method must be overridden by children classes.
	 *
	 * @access public
	 */
	public static function getColumns() {
		return [];
	}

	/**
 	 * Returns a map of constraints associating fields with validation functions.
	 * This method is meant to be overridden by children classes.
	 *
	 * @access public
	 */
	public static function getConstraints() {
		return [];
	}


	public function getDefaults() {
		$defaults = [];
		foreach($this->schema as $field => $definition) {
			if(isset($definition['default'])) {
				$defaults[$field] = $definition['default'];
			}
		}
		return $defaults;
	}



	/**
	* Returns the name of the database table related to this object
	* This method may be overridden by children classes
	*
	* @access public
	*/
	public function getTable() {
        $class_name = get_parent_class($this);		
        if($class_name == __CLASS__) {
			// class directly inherits from qinoa\orm\Model : use its name
            $class_name = get_class($this);
        }
		// otherwise use the parent class to obtain targeted table
		return strtolower(str_replace('\\', '_', $class_name));
	}

    // qinoa integration : if available
    public static function __callStatic($name, $arguments) {            
        if(is_callable('qinoa\orm\Collections::getInstance')) {
            $factory = \qinoa\orm\Collections::getInstance();
            $collection = $factory->create(get_called_class());
            // check that the method actually exists
            if(is_callable([$collection, $name])) {
                return call_user_func_array([$collection, $name], $arguments);
            }
            else {
                throw new \Exception('call to non-existing method in class Collection', QN_ERROR_INVALID_PARAM);
            }
        }
        return null;
    }   

}