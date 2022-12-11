<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;


/**
 * Root Model for all Object definitions.
 * This class holds the description of an object along with the values of the currently assigned fields.
 *
 * List of static methods for building new Collection objects (accessed through magic methods):
 *
 * @method \equal\orm\Collection id($id)
 * @method \equal\orm\Collection ids(array $ids=[])
 * @method \equal\orm\Collection search(array $domain=[], array $params=[], $lang=null)
 * @method \equal\orm\Collection create(array $values=null, $lang=null)
 */
class Model implements \ArrayAccess, \Iterator {

    /**
     * Complete object schema: an associative array mapping fields names with their definition.
     * Schema is the concatenation of special-columns and custom-defined columns.
     * @var array
     */
    private $schema;

    /**
     * Associative array holing Field instances defined in the model.
     *
     * @var Field[]
     */
    private $fields;

    /**
     * Associative array mapping fields with their value.
     *
     * @var array
     */
    private $values;


    /**
     * Constructor: initializes members vars and set fields to values, if given.
     *
     */
    public final function __construct($values=[]) {
        $this->values = [];
        $this->fields = [];
        // build the schema based on current class and ancestors
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
            $this->schema['name'] = ['type' => 'alias', 'alias' => 'id'];
        }
        $fields = array_keys($this->schema);
        // get default values, set fields for default language, and mark fields as modified
        foreach($fields as $field) {
            // init related field instance
            $this->fields[$field] = new Field($this->schema[$field]);
        }
        // set fields to default values
        $this->setDefaults($values);
    }

    private function setDefaults($values=[]) {
        $defaults = $this->getDefaults();
        // reset fields values
        $this->values = [];
        $fields = array_keys($this->schema);
        // get default values, set fields for default language, and mark fields as modified
        foreach($fields as $field) {
            // init related field instance
            if(isset($values[$field])) {
                $this->values[$field] = $values[$field];
            }
            elseif(isset($defaults[$field])) {
                // #memo - default value should be either a simple type, a PHP expression, or a PHP function (executed at definition parsing)
                $this->values[$field] = $defaults[$field];
            }
        }
    }

    public function toArray() {
        return $this->values;
    }


    /* Magic properties */

    public function __get($field) {
        return $this->values[$field];
    }

    public function __set($field, $value) {
        $this->values[$field] = $value;
    }


    /* ArrayAccess methods */

    public function offsetSet($field, $value): void {
        if (!is_null($field)) {
            $this->values[$field] = $value;
            // #memo - properties should remain virtual (we cannot intercept direct value assignation)
        }
    }

    public function offsetExists($field): bool {
        return isset($this->values[$field]);
    }

    public function offsetUnset($field): void {
        unset($this->values[$field]);
    }

    public function offsetGet($field) {
        return isset($this->values[$field]) ? $this->values[$field] : null;
    }


    /* Iterator methods */

    public function rewind() : void {
        reset($this->values);
    }

    public function current() {
        return current($this->values);
    }

    public function key() : string {
        return key($this->values);
    }

    public function next() : void {
        next($this->values);
    }

    public function valid() : bool {
        $key = key($this->values);
        $res = ($key !== null && $key !== false);
        return $res;
    }


    /**
     * Returns a list of constant names that the entity expects to be available.
     * This method must be overridden by children classes.
     *
     * @return array
     */
    public static function constants() {
        return [];
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
                'foreign_object'    => 'core\User',
                'default'           => QN_ROOT_USER_ID
            ],
            'created' => [
                'type'              => 'datetime',
                'default'           => time(),
                'readonly'          => true
            ],
            'modifier' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'default'           => QN_ROOT_USER_ID
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

    /**
     * #todo - deprecate : this method should no longer be used
     * @deprecated
     */
    public final function setField($field, $value) {
        $this->values[$field] = $value;
    }

    /**
     * #todo - deprecate : this method doesn't seem to be used
     * @deprecated
     */
    public final function setColumn($column, array $description) {
        $this->schema[$column] = $description;
    }

    /**
     * Gets the Model full name (class including namespace).
     * Return the name of the current class (same as declaration) with its full namespace.
     *
     * @return string
     */
    public static function getType() {
        return get_called_class();
    }

    /**
     * Gets the Model readable name (meant for UI).
     * This method is meant to be overridden by children classes to define their own name.
     *
     * @access public
     * @return array
     */
    public static function getName() {
        return 'Model';
    }

    /**
     * Gets the URL associated with the class (for UI to browse main form of a single object).
     *
     * @return string
     */
    public static function getLink() {
        return '';
    }

    /**
     * Gets the Model description.
     * This method is meant to be overridden by children classes.
     *
     * @access public
     * @return array
     */
    public static function getDescription() {
        return 'Interface for model defintion classes.';
    }

    /**
     * Gets the object schema.
     *
     * @access public
     * @return array
     */
    public final function getSchema() {
        return $this->schema;
    }

    /**
     * Returns fields as Field objects.
     * #memo - $fields member might be incomplete (fields instances are created when requested for the first time)
     *
     * @return Field[]    Associative array mapping fields names with their related Field instances.
     * @deprecated
     */
    public final function getFields() {
        return $this->fields;
    }

    public final function getField($field) {
        $type = $this->schema[$field]['type'];
        while($type == 'alias') {
            $field = $this->schema[$field]['alias'];
            $type = $this->schema[$field]['type'];
        }
        if(!isset($this->fields[$field])) {
            if(isset($this->schema[$field])) {
                $this->fields[$field] = new Field($this->schema[$field]);
            }
        }
        return (isset($this->fields[$field]))?$this->fields[$field]:null;
    }

    /**
     * Provide the final field targeted by a field (handle aliases)
     * This method should be used for type comparisons and when checking field structure validity.
     *
     * @deprecated
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
     * This method can be overridden to define a more precise set of unique constraints (i.e when keys are formed of several fields).
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

        // class uses its parent class name, unless it directly inherits from root Model class (equal\orm\Model)
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
        // upon state update (to 'archived' or 'deleted'), remove any pending alert related to the object
        if(isset($values['state']) && $values['state'] != 'instance') {
            $messages_ids = $om->search('core\alert\Message', [ ['object_class', '=', get_called_class()], ['object_id', 'in', $oids] ] );
            if($messages_ids) {
                $om->delete('core\alert\Message', $messages_ids, true);
            }
        }
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
     * Hook invoked before object deletion for performing object-specific additional operations.
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

    public static function id($id) {
        return self::ids((array) $id);
    }

    public static function ids($ids) {
        if(is_callable('equal\orm\Collections::getInstance')) {
            /** @var Collections */
            $factory = Collections::getInstance();
            /** @var Collection */
            $collection = $factory->create(get_called_class());
            return $collection->ids($ids);
        }
        return null;
    }

    public static function search(array $domain=[], array $params=[], $lang=null) {
        if(is_callable('equal\orm\Collections::getInstance')) {
            /** @var Collections */
            $factory = Collections::getInstance();
            /** @var Collection */
            $collection = $factory->create(get_called_class());
            return $collection->search($domain, $params, $lang);
        }
        return null;
    }

    public static function create(array $values=null, $lang=null) {
        if(is_callable('equal\orm\Collections::getInstance')) {
            /** @var Collections */
            $factory = Collections::getInstance();
            /** @var Collection */
            $collection = $factory->create(get_called_class());
            return $collection->create($values, $lang);
        }
        return null;
    }

    /**
     * Handler for virtual static methods: use classname to invoke a Collection method, if available.
     * #todo - deprecate : since we cover all entry points with static methods, magic methods should no longer be involved.
     *
     * @param  string   $name       Name of the called method.
     * @param  array    $arguments  Array holding a list of arguments to relay to the invoked method.
     * @deprecated
     */
    public static function __callStatic($name, $arguments) {
        if(is_callable('equal\orm\Collections::getInstance')) {
            $factory = Collections::getInstance();
            $collection = $factory->create(get_called_class());
            // check that the method actually exists
            if(is_callable([$collection, $name])) {
                return call_user_func_array([$collection, $name], $arguments);
            }
            else {
                throw new \Exception("call to non-existing method `$name` on Collection class", QN_ERROR_INVALID_PARAM);
            }
        }
        return null;
    }

}