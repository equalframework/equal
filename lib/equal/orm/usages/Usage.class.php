<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


/**
 * Default class from which all Usages inherit.
 */
class Usage {

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

    public function getConstraints(): array {
        return [];
    }

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
     * The precision indicates the number of digits of a floating number.
     * It is expected to be an integer value completed with a scale (that defaults to 0).
     * In all other situations, precision and length are synonyms and scale is always 0.
     *
     */
    final public function getPrecision(): string {
        return $this->length;
    }

    /**
     * Provides the scale assigned to the usage instance.
     * For floating numbers, the scale indicates the number of digits of the decimal part.
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
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/?([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?/', $usage_str,  $matches)) {
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
            $this->type = isset($matches[1])?$matches[1]:'';
            $this->is_array = isset($matches[2]) && strlen($matches[2]);
            $this->size = (isset($matches[3]) && strlen($matches[3]))?intval($matches[3]):0;
            $this->subtype = isset($matches[4])?$matches[4]:'';
            $tree = isset($matches[6])?$matches[6]:'';
            // accepts various formats ({length} (ex.'255'), {precision}.{scale} (ex. '5:3'), or {shortcut} (ex. 'medium'))
            $this->length = (isset($matches[9]) && strlen($matches[9]))?$matches[9]:0;
            $this->scale = (isset($matches[10]) && strlen($matches[10]))?$matches[10]:0;
        }

    }

}
