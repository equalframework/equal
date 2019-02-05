<?php
/**
*    This file is part of the easyObject project.
*    http://www.cedricfrancoys.be/easyobject
*
*    Copyright (C) 2012  Cedric Francoys
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.

*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace qinoa\orm;


/**
	This class holds the description of an object (and not the object itself)
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
		$this->schema = array_merge(self::getSpecialColumns(), $this->getColumns());
		
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
		if(method_exists($this, 'getDefaults')) {
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
	}

	public final static function getSpecialColumns() {
		static $special_columns = array(
			'id'		=> array('type' => 'integer'),
            // 'name' field is set in constructor if not defined in getColumns method
			'creator'	=> array('type' => 'integer'),            
			'created'	=> array('type' => 'datetime'),
			'modifier'	=> array('type' => 'integer'),
			'modified'	=> array('type' => 'datetime'),
			'deleted'	=> array('type' => 'boolean'),		
			'state'		=> array('type' => 'string'),			
		);
		return $special_columns;
	}
    
    public final function setField($field, $value) {
        $this->values[$field] = $value;
    }
    
    public final function setColumn($column, array $description) {
        $this->schema[$column] = $description;
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
    * returns values of static instance (default values)
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
		return array();
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
            $class_name = get_class($this);
        }
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