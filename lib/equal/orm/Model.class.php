<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\services\Container;

/**
 * Root Model for all Object definitions.
 * This class holds the description of an object along with the values of the currently loaded/assigned fields.
 *
 * List of static methods for building new Collection objects (accessed through magic methods):
 * @method static \equal\orm\Collection id($id)
 * @method static \equal\orm\Collection static ids(array $ids=[])
 * @method static \equal\orm\Collection static search(array $domain=[], array $params=[], $lang=null)
 * @method static \equal\orm\Collection static create(array $values=null, $lang=null)
 *
 * List of static methods with variable parameters:
 * (These methods are handled through __callStatic method to prevent PHP strict errors & aot warnings.)
 *
 * 1) `can...()` methods - consistency checkers:
 * These methods return an associative array mapping fields with their error messages. An empty array means that use can perform the action.
 * @method array    canread(mixed ...$params)       Check wether an object can be read by current user.
 * @method array    cancreate(mixed ...$params)     Check wether an object can be created.
 * @method array    canupdate(mixed ...$params)     Check wether an object can be updated.
 * @method array    candelete(mixed ...$params)     Check wether an object can be deleted.
 * @method array    canclone(mixed ...$params)      Check wether an object can be cloned.
 *
 * 2) `on...()` methods - event handlers:
 * @method array    onchange(mixed ...$params)      Hook invoked by UI for single object values change. Returns an associative array mapping fields with new (virtual) values to be set in UI (not saved yet).
 * @method void     oncreate(mixed ...$params)      Hook invoked AFTER object creation for performing object-specific additional operations.
 * @method void     onupdate(mixed ...$params)      Hook invoked BEFORE object update for performing object-specific additional operations.
 * @method void     ondelete(mixed ...$params)      Hook invoked BEFORE object deletion for performing object-specific additional operations.
 * @method void     onclone(mixed ...$params)       Hook invoked AFTER object cloning for performing object-specific additional operations.
 *
 * Possible params:
 * - Collection                         $self       Collection holding a series of objects of current class.
 * - array                              $ids        List of objects identifiers in current collection.
 * - array                              $event      Associative array holding changed fields as keys, and their related new values.
 * - array                              $values     Copy of the current (partial) state of the object.
 * - string                             $lang       Lang ISO code, falls back to DEFAULT_LANG.
 * - equal\orm\ObjectManager            $orm        Instance of ObjectManager service.
 * - equal\access\AccessController      $access     Instance of AccessController service.
 * - equal\auth\AuthenticationManager   $auth       Instance of AuthenticationManager service.
 * - equal\php\Context                  $context    Instance of Context service.
 * - equal\error\Reporter               $reporter   Instance of error Reporter service.
 * - equal\data\DataAdapter             $adapt      Instance of DataAdapter service.
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
        $called_class = get_called_class();
        $parent_class = get_parent_class($called_class);
        $parents_classes = [$called_class];
        while($parent_class != __CLASS__) {
            array_unshift($parents_classes, $parent_class);
            $parent_class = get_parent_class($parent_class);
        }
        // build schema
        foreach($parents_classes as $class) {
            $this->schema = array_merge($this->schema, (array) call_user_func_array([$class, 'getColumns'], []));
        }
        // inject dependencies (constants)
        if(method_exists($called_class, 'constants')) {
            $constants = $called_class::constants();
            foreach($constants as $name) {
                if(\config\defined($name) && !defined($name)) {
                    $value = \config\constant($name);
                    \config\export($name, $value);
                }
                if(!defined($name)) {
                    throw new \Exception("Requested constant {$name} is missing from configuration", QN_ERROR_INVALID_CONFIG);
                }
            }
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
            $this->fields[$field] = new Field($this->schema[$field], $field);
        }
        // set fields to default values
        $this->setDefaults($values);
    }

    private function setDefaults($values=[]) {
        $container = Container::getInstance();
        $orm = $container->get('orm');
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
                if(is_callable($defaults[$field])) {
                    // either a php function (or a function from the global scope) or a closure object
                    if(is_object($defaults[$field])) {
                        // default is a closure
                        $this->values[$field] = $defaults[$field]();
                    }
                    else {
                        // do not call since there is an ambiguity with scalar (e.g. 'time')
                        $this->values[$field] = $defaults[$field];
                    }
                }
                elseif(is_string($defaults[$field]) && method_exists($this->getType(), $defaults[$field])) {
                    // default is a method of the class (or parents')
                    $this->values[$field] = $orm->callonce($this->getType(), $defaults[$field]);
                }
                else {
                    // default is a scalar value
                    $this->values[$field] = $defaults[$field];
                }
            }
        }
    }

    public function toArray() {
        return $this->values;
    }


    /**
     * Magic properties
     */

    public function __get($field) {
        return $this->values[$field];
    }

    public function __set($field, $value) {
        $this->values[$field] = $value;
    }


    /**
     * ArrayAccess methods
     */

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
        if(isset($this->fields[$field])) {
            unset($this->fields[$field]);
        }
        if(isset($this->values[$field])) {
            unset($this->values[$field]);
        }
    }

    public function offsetGet($field) {
        return isset($this->values[$field]) ? $this->values[$field] : null;
    }


    /**
     * Iterator methods
     */

    public function rewind(): void {
        reset($this->values);
    }

    public function current() {
        return current($this->values);
    }

    public function key(): string {
        return key($this->values);
    }

    public function next(): void {
        next($this->values);
    }

    public function valid(): bool {
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
                'default'           => function() { return time(); },
                'readonly'          => true
            ],
            'modifier' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'default'           => QN_ROOT_USER_ID
            ],
            'modified' => [
                'type'              => 'datetime',
                'default'           => function() { return time(); },
                'readonly'          => true
            ],
            // #memo - when set to true, modifier points to the user who deleted the object and modified is the time of the deletion
            'deleted' => [
                'type'              => 'boolean',
                'default'           => false
            ],
            // system related state of the object
            'state' => [
                'type'              => 'string',
                'selection'         => ['draft', 'instance', 'archive'],
                'default'           => 'instance'
            ]
        ];
        return $special_columns;
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
        return 'Interface for model definition classes.';
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

    /**
     * Returns a Field object that corresponds to the descriptor of the given field (from schema).
     * If a field is an alias, it is the final descriptor of the alias chain that is returned.
     *
     * @return Field        Associative array mapping fields names with their related Field instances.
     */
    public final function getField($field): ?Field {
        if(isset($this->schema[$field])) {
            $type = $this->schema[$field]['type'];
            while($type == 'alias') {
                $field = $this->schema[$field]['alias'];
                $type = $this->schema[$field]['type'];
            }
            if(!isset($this->fields[$field])) {
                if(isset($this->schema[$field])) {
                    $this->fields[$field] = new Field($this->schema[$field], $field);
                }
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
     * Returns values of static instance (default values).
     *
     */
    public final function getValues() {
        return $this->values;
    }

    /**
     * Returns the user-defined part of the schema (i.e. fields list with types and other attributes).
     * This method must be overridden by children classes.
     *
     */
    public static function getColumns() {
        return [];
    }

    /**
     * Returns the workflow associated with the entity.
     * This method is meant to be overridden by children classes.
     *
     */
    public static function getWorkflow() {
        return [];
    }


    /**
     * Returns an associative array describing the roles that are defined on the current entity.
     *
     * Roles descriptor example:
     *   return [
     *       'owner': {
     *            'description': ""
     *        },
     *        'admin': {
     *            'description': "",
     *            'implied_by': ['owner']
     *        },
     *        'editor': {
     *            'description': "",
     *            'implied_by': ['admin']
     *        },
     *        'viewer': {
     *            'description': "",
     *            'implied_by': ['editor']
     *        }
     *    ]
     */
    public static function getRoles() {
        return [];
    }

    /**
     * Returns the associative array mapping policies names with related descriptors.
     * This method is meant to be overridden by children classes.
     *
     * A policy descriptor contains a description and a pointer to the policy handler (method).
     * Policy handlers are meant to receive a collection and a user_id, and return an associative array mapping errors ids with their related description.
     * If returned array is empty, the context complies with the policy.
     *
     * Policy descriptor example :
     *
     *  'publishable'   => [
	 *		'description'		=> "Policy defining the rules for an object to be publishable.",
	 *		'function'          => 'policyPublishable'
	 *	]
     *
     */
    public static function getPolicies() {
        return [];
    }

    /**
     * Returns the associative array mapping actions names with related descriptors of the actions defined by the entity.
     * This method is meant to be overridden by children classes.
     *
     */
    public static function getActions() {
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
     * Returns an associative array mapping field names with their default values.
     * If overloaded, methods from children classes must merge their result with values returned from parent class.
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
     * Handler for virtual static methods: use classname to invoke a Collection method, if available.
     *
     * @param  string   $name       Name of the called method.
     * @param  array    $arguments  Array holding a list of arguments to relay to the invoked method.
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
