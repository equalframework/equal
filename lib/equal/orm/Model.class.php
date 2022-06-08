<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;


/**
 *  Root Model for all objects.
 *  This class holds the description of an object (and not the object itself)
 */
class Model {

    /**
     * Complete object schema, containing all columns (including special ones as object id).
     *
     * @var array
     */
    private $schema;

    /**
     * List of all fields names (columns) defined in the model.
     *
     * @var array
     */
    private $fields;

    /**
     * Associative array mapping fields with their value.
     *
     * @var array
     */
    private $values;


    /**
     * Constructor
     *
     */
    public final function __construct($orm, $values=[]) {
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
            $this->schema = array_merge($this->schema, (array) call_user_func_array([$class, 'getColumns'], []));
        }

        // make sure that a field 'name' is always defined
        if( !isset($this->schema['name']) ) {
            // if no field 'name' is defined, fall back to 'id' field
            $this->schema['name'] = array( 'type' => 'alias', 'alias' => 'id' );
        }
        // set array holding fields names
        $this->fields = array_keys($this->schema);

        // set fields to default values
        $this->setDefaults($orm, $values);
    }

    private function setDefaults($orm, $values=[]) {
        $this->values = [];

        $defaults = $this->getDefaults();
        // get default values, set fields for default language, and mark fields as modified
        foreach($this->fields as $field) {
            if(isset($defaults[$field])) {
                // #memo - default value should be either a simple type, a PHP expression, or a PHP function (executed at definition parsing)
                $this->values[$field] = $defaults[$field];
            }
            if(isset($values[$field])) {
                $this->values[$field] = $values[$field];
            }
        }
    }

    /**
     * Return the static part of the schema (common to all objects).
     * #memo - 'name' field is set in constructor if not defined in getColumns method
     *
     * @return array        Returns an associative array of the mandatory (system) fields.
     */
    public final static function getSpecialColumns() {
        $special_columns = [
            'id' => [
                'type'              => 'integer',
                'readonly'          => true
            ],
            'creator' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User'
            ],
            'created' => [
                'type'              => 'datetime',
                'default'           => time(),
                'readonly'          => true
            ],
            'modifier' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User'
            ],
            'modified' => [
                'type'              => 'datetime',
                'default'           => time(),
                'readonly'          => true
            ],
            'deleted' => [
                'type'              => 'boolean',
                'default'           => false
            ],
            'state' => [
                'type'              => 'string',
                'selection'         => ['draft', 'instance', 'archive'],
                'default'           => 'instance'
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
     * Get Model readable name.
     * This method is meant to be overridden by children classes.
     *
     * @access public
     * @return array
     */
    public static function getName() {
        return 'Model';
    }

    /**
     * Get the URL associated to the class, to browse to the main form of a single object.
     */
    public static function getLink() {
        return '';
    }

    /**
     * Get Model description.
     * This method is meant to be overridden by children classes.
     *
     * @access public
     * @return array
     */
    public static function getDescription() {
        return 'Interface for model defintion classes.';
    }

    /**
     * Get object schema
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
     * This method should be used for type comparisons and when checking field structure validity.
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
     * Returns a map of constraint items associating fields with validation functions.
     * This method is meant to be overridden by children classes.
     *
     * Items should have following structure :
     *        'field_name' =>  [
     *            'error_id' => [
     *                'message'       => 'error message',
     *                 'function'     => function ($field_new_value, $other_new_values) {
     *                     return (bool) $result;
     *                 }
     *             ]
     *            ]
     *
     */
    public static function getConstraints() {
        return [];
    }

    /**
     *
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
     * Provide the list of unique rules (array of combinations of fields).
     * This method can be overriden to define a more precise set of unique constraints (i.e when keys are formed of several fields).
     *
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
     * @return string   Returns the name of the SQL table relating the the entity.
     */
    public function getTable() {
        $parent = get_parent_class($this);
        $entity = get_class($this);

        // class uses its parent class name, unless it directly inherits from root class (equal\orm\Model)
        while($parent != __CLASS__) {
            $entity = $parent;
            $parent = get_parent_class($parent);
        }

        return strtolower(str_replace('\\', '_', $entity));
    }


    /**
     * Check wether an object can be created.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overriden to define a more precise set of tests.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $values     Associative array holding the values to be assigned to the new instance (not all fields might be set).
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be created.
     */
    public static function cancreate($om, $values, $lang) {
        return [];
    }

    /**
     * Check wether an object can be updated.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overriden to define a more precise set of tests.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @param  array            $values     Associative array holding the new values to be assigned.
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be updated.
     */
    public static function canupdate($om, $oids, $values, $lang) {
        return [];
    }

    /**
     * Check wether an object can be cloned.
     * These tests come in addition to the unique constraints return by method `getUnique()`.
     * This method can be overriden to define a more precise set of tests.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @return array            Returns an associative array mapping fields with their error messages. En empty array means that object has been successfully processed and can be updated.
     */
    public static function canclone($om, $oids) {
        return [];
    }

    /**
     * Check wether an object can be deleted.
     * This method can be overriden to define a more precise set of tests.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @return array            Returns an associative array mapping fields with their error messages. An empty array means that object has been successfully processed and can be deleted.
     */
    public static function candelete($om, $oids) {
        return [];
    }

    /**
     * Hook invoked after object creation for performing object-specific additional operations.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers. Should contain only the id of the object just created.
     * @param  array            $values     Associative array holding the newly assigned values.
     * @param  string           $lang       Language in which multilang fields are being created.
     * @return void
     */
    public static function oncreate($om, $oids, $values, $lang) {
    }

    /**
     * Hook invoked before object update for performing object-specific additional operations.
     * Current values of the object can still be read for comparing with new values.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @param  array            $values     Associative array holding the new values that have been assigned.
     * @param  string           $lang       Language in which multilang fields are being updated.
     * @return void
     */
    public static function onupdate($om, $oids, $values, $lang) {
    }

    /**
     * Hook invoked after object cloning for performing object-specific additional operations.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @return void
     */
    public static function onclone($om, $oids) {
    }

    /**
     * Hook invoked after object deletion for performing object-specific additional operations.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @return void
     */
    public static function ondelete($om, $oids) {
    }

    /**
     * Signature for single object values change in UI.
     * This mehtod does not imply an actual update of the model, but a potential one (not made yet) and is intended for front-end only.
     *
     * @param  ObjectManager    $om         ObjectManager instance.
     * @param  array            $oids       List of objects identifiers.
     * @param  array            $event      Associative array holding changed fields as keys, and their related new values.
     * @param  array            $values     Copy of the current (partial) state of the object.
     * @return array            Returns an associative array mapping fields with their resulting values.
     */
    public static function onchange($om, $event, $values, $lang) {
        return [];
    }

    /**
     * Handler for virtual static methods: use classname to invoke a Collection method, if available.
     *
     * @param  string   $name       Name of the called method.
     * @param  array    $arguments  Array holding a list of arguments to relay to the invoked method.
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
                throw new \Exception('call to non-existing method on Collection class', QN_ERROR_INVALID_PARAM);
            }
        }
        return null;
    }

}