<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class Field {

    /**
     * Descriptor of the field.
     * In addition to properties from `Model::getColumns()`, `Field::descriptor` always as a `result_type` property.
     *
     * @var array
     */
    private $descriptor = [];

    /** @var Usage */
    private $usage = null;

    /**
     * Field name, if provided.
     * @var string
     */
    private $name = '';

    /**
     * ORM type of the Field instance.
     * @var string
     */
    private $type = null;

    /**
     * @param array $descriptor Associative array mapping field properties and their values.
     */
    public function __construct(array $descriptor, string $name='') {
        if(isset($descriptor['type'])) {
            $this->type = $descriptor['type'];
        }
        $this->descriptor = $descriptor;
        $this->name = $name;
        // ensure local descriptor always has a result_type property
        if(!isset($descriptor['result_type'])) {
            $this->descriptor['result_type'] = $this->type;
        }
    }

    public function getDescriptor(): array {
        return $this->descriptor;
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Provides the usage string equivalent of the type of the Field instance.
     * This method maps `types` (implicit usage format) with explicit usage formats.
     */
    protected function getUsageString(): string {
        $result = $this->descriptor['usage'] ?? '';
        static $map = [
            'boolean'       => 'number/boolean',
            'integer'       => 'number/integer:9',
            'float'         => 'number/real:10.2',
            'string'        => 'text/plain:255',
            'text'          => 'text/plain:32000',
            'date'          => 'date/plain',
            'datetime'      => 'date/time',
            'time'          => 'time/plain',
            'binary'        => 'binary/plain:16000000',
            'many2one'      => 'number/integer:9',
            'one2many'      => 'array',
            'many2many'     => 'array',
            'array'         => 'array'
        ];
        if(!strlen($result)) {
            $type = $this->descriptor['result_type'];
            $result = $map[$type] ?? $type;
        }
        return $result;
    }

    /**
     * Provides the pseudo Content-Type associated with the ORM type of the field.
     * Such Content-Types should be recognized by any DataAdapter.
     */
    public function getContentType(): string {
        static $map = [
            'boolean'       => 'number/boolean',
            'integer'       => 'number/integer',
            'float'         => 'number/real',
            'string'        => 'text/plain',
            'date'          => 'date/plain',
            'datetime'      => 'date/datetime',
            'time'          => 'time/plain',
            'binary'        => 'binary/plain',
            'many2one'      => 'number/natural',
            'one2many'      => 'array',
            'many2many'     => 'array',
            'array'         => 'array'
        ];
        $type = $this->descriptor['result_type'];
        return $map[$type];
    }

    public function getUsage(): Usage {
        if(is_null($this->usage)) {
            // by default, use the usage string for which the field type is an alias
            $this->usage = UsageFactory::create($this->getUsageString());
        }
        return $this->usage;
    }

    /**
     * Retrieves the constraints that apply on the field, according to its usage (explicit or implicit).
     *
     * @return array
     */
    public function getConstraints(): array {
        $constraints = $this->getUsage()->getConstraints();

        // #memo - strict type constraint is not relevant since lose conversion is possible for some types (e.g. "30" is an accepted integer)

        // add constraint based on 'selection', if present
        if(isset($this->descriptor['selection']) && count($this->descriptor['selection'])) {
            $selection = $this->descriptor['selection'];
            $constraints['invalid_value'] = [
                    'message'   => "Value is not amongst selection choices.",
                    'function'  =>  function($value) use($selection) {
                        return (isset($selection[$value]) || in_array($value, $selection));
                    }
                ];
        }

        // add constraint based on 'pattern', if present
        if(isset($this->descriptor['pattern']) && count($this->descriptor['pattern'])) {
            $pattern = $this->descriptor['pattern'];
            $constraints['invalid_value'] = [
                    'message'   => "Value does not match provided pattern.",
                    'function'  =>  function($value) use($pattern) {
                        return preg_match($pattern, $value);
                    }
                ];
        }

        // #todo - handle other possible descriptor attributes :'min', 'max', 'in', 'not in'
        // @see DataValidator

        return $constraints;
    }
}
