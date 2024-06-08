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
    protected $usage_str = '';

    /**
     * Usage main type.
     * @var string
     */
    protected $type = '';

    /**
     * Usage subtype.
     * Subtype might have several nodes (ex: node1.node2.node3)
     * @var string
     */
    protected $subtype = '';

    /**
     * Flag marking if the usage targets an array.
     * @var boolean
     */
    protected $is_array = false;

    /** @var string
     * Accepts various formats ({length} (ex.'255'), {precision}.{scale} (ex. '5:3'), or {shortcut} (ex. 'medium'))
    */
    protected $length = '';

    /** @var int */
    protected $precision = 0;

    /** @var int */
    protected $scale = 0;

    /**
     * Size of the array (when usage targets an array of values)
     * @var int
     */
    protected $size = 0;

    /**
     * Return the constraints descriptors, according to the Usage instance.
     * Since `function` properties returned by this method expect a non-static context,
     * using the ORM, those callbacks are bound to a Usage instance using `bindTo()`.
     */
    public function getConstraints(): array {
        return [];
    }

    final public function getName() : string {
        return $this->usage_str;
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

    /**
     * Size is a naming convention that only makes sense if Usage targets an array.
     *
     */
    final public function getSize(): int {
        return $this->size;
    }

    public function getLength(): int {
        return $this->length;
    }

    /**
     * The precision indicates the number of digits in the integer part of a floating number.
     * It is expected to be an integer value completed with a scale (that defaults to 0).
     * In all other situations, precision and length are synonyms and scale is always 0.
     *
     */
    public function getPrecision(): int {
        return $this->precision;
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
     *
     * @example
     *  urn/isbn.10
     *  number[3]/integer:2
     *  language/iso-639:3
     *  number/real:5.2
     *  date/weekday.mon:short
     *
    */
    public function __construct(string $usage_str) {

        // check usage string consistency
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/?([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?({([0-9]+)(,([0-9]+))?})?/', $usage_str,  $matches)) {
            trigger_error("ORM::invalid usage format $usage_str", QN_REPORT_WARNING);
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
                group 12 = min
                group 14 = max
            */
            // store original usage string
            $this->usage_str = $usage_str;
            $this->type = isset($matches[1])?$matches[1]:'';
            $this->is_array = isset($matches[2]) && strlen($matches[2]);
            $this->size = (isset($matches[3]) && strlen($matches[3]))?intval($matches[3]):0;
            $this->subtype = isset($matches[4])?$matches[4]:'';
            $tree = isset($matches[6])?$matches[6]:'';
            if(strlen($tree) > 0) {
                $this->subtype .= '.'.$tree;
            }
            // accepts various formats ({length} (ex.'255'), {precision}.{scale} (ex. '5:3'), or {shortcut} (ex. 'medium'))
            $this->length = (isset($matches[8]) && strlen($matches[8]))?intval($matches[8]):0;
            $this->precision = (isset($matches[9]) && strlen($matches[9]))?intval($matches[9]):0;
            $this->scale = (isset($matches[10]) && strlen($matches[10]))?intval($matches[10]):0;
        }

    }

}
