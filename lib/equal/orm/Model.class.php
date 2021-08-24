<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;


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
    public final function __construct($orm) {
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
        
        // set fields to default values
        $this->setDefaults($orm);
    }

    private final function setDefaults($orm) {
        $this->values = [];

        $defaults = $this->getDefaults();
        // get default values, set fields for default language, and mark fields as modified
        foreach($defaults as $field => $default) {
            if(isset($this->schema[$field])) {
                if(is_callable($default)) {
                    $this->values[$field] = call_user_func($default, $orm);
                }
                else {
                    $this->values[$field] = $default;
                }
            }
        }

    }

    // note: 'name' field is set in constructor if not defined in getColumns method
    public final static function getSpecialColumns() {
        $special_columns = [
            'id' => [				
                'type'				=> 'integer'
            ],            
            'creator' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User'
            ],
            'created' => [
                'type' 				=> 'datetime',
                'default'			=> time()
            ],
            'modifier' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User'
            ],
            'modified' => [
                'type' 				=> 'datetime',
                'default'			=> time()				
            ],
            'deleted' => [
                'type' 				=> 'boolean',
                'default'			=> false
            ],
            'state' => [
                'type' 				=> 'string',
                'selection'         => ['draft', 'instance', 'archive'],
                'default'			=> 'instance'
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
     * @access public
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
     * @access public
     */
    public final function getValues() {
        return $this->values;
    }	
    
    /**
      * Returns the user-defined part of the schema (i.e. fields list with types and other attributes)
     * This method must be overridden by children classes.
     *
     * @access public
     * @static
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

    /**
     * 
     * @access public
     */
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
     * This method can be overriden to define a more precise set of unique constraints (i.e when keys are formed of several fields).
     * 
     * @access public
     */
    public function getUnique() {
        $uniques = [];
        foreach($this->schema as $field => $definition) {
            if(isset($definition['unique']) && $definition['unique']) {
                $uniques[] = [$field];
            }
        }
        return $uniques;
    }



    /**
     * Return the name of the DB table to be used for storing objects of current class.
     * This method can be overridden by children classes to allow polymorphism at class level.
     *
     * @access public
     * 
     */
    public function getTable() {
        $parent = get_parent_class($this);
        $entity = get_class($this);

        // if class directly inherits from root class (equal\orm\Model), use its own name
        while($parent != __CLASS__) {            
            $entity = $parent;
            $parent = get_parent_class($parent);            
        }

        return str_replace('\\', '_', $entity);
    }

    /**
     * Handle virtual static methods: use classname to invoke a Collection method, if available
     * 
     * @access public
     * @static
     */
    public static function __callStatic($name, $arguments) {            
        if(is_callable('equal\orm\Collections::getInstance')) {
            $factory = \equal\orm\Collections::getInstance();
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