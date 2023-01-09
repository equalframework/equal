<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


/**
 * Abstract class from which all Usages inherit.
 */
abstract class Usage {

    /** @var string */
    private $usage_str = '';

    /**
     * Usage main type.
     * @var string
     */
    private $type = '';

    /**
     * Usage subtype.
     * Subtype might have several nodes (ex: node1.node2.node3)
     * @var string
     */
    private $subtype = '';

    /**
     * Flag marking if the usage targets an array.
     * @var boolean
     */
    private $is_array = false;

    /** @var string */
    private $length = '';

    /** @var int */
    private $scale = 0;

    /**
     * Size of the array (when usage targets an array of values)
     * @var int
     */
    private $size = 0;

    abstract public function getConstraints(): array;

    /**
     * Provides the generic (display) name of the type.
     */
    final public function getType(): string {
        return $this->type;
    }

    final public function getSubtype(): string {
        return $this->subtype;
    }

    final public function getLength(): string {
        return $this->length;
    }

    /**
     * Size is a naming convention that only makes sense if Usage targets an array.
     *
     */
    final public function getSize(): string {
        return $this->size;
    }

    /**
     * Precision is a naming convention that only makes sense if Usage targets a number with floating point number.
     * In such situation precision is expected to be an integer value completed with a scale (that defaults to 0).
     * In all other situations, precision and length are synonyms and scale is always 0.
     *
     */
    final public function getPrecision(): string {
        return $this->length;
    }

    /**
     * Provides the scale assigned to the usage instance.
     * This method can be overloaded by children classes for handling subtypes with implicit scale.
     */
    public function getScale(): int {
        return $this->scale;
    }

    /**
     * Associative array mapping accepted subtypes
     * @var array */
    private $variations = [];


    /**
     * @param string $usage_str   Usage string: string describing the usage.
    */
    public function __construct(string $usage_str) {

        // check usage string consistency
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?/', $usage_str,  $matches)) {
            // error
        }
        else {
            /*
                group 1 = type
                group 3 = array size
                group 4 = subtype
                group 6 = subtype tree
                group 8 = length
                group 9 = precision
                group 10 = scale
            */

            // store original usage string
            $this->usage_str = $usage_str;
            $this->type = $matches[1];
            $this->is_array = strlen($matches[2]);
            $this->size = strlen($matches[3])?intval($matches[3]):0;
            $this->subtype = $matches[4];
            $tree = $matches[6];
            // accepts various formats ({length} (ex.'255'), {precision}.{scale} (ex. '5:3'), or {shortcut} (ex. 'medium'))
            $this->length = strlen($matches[9])?$matches[9]:0;
            $this->scale = strlen($matches[10])?$matches[10]:0;
        }

    }

}
