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

    /** @var string */
    private $length = '';

    /** @var int */
    private $scale = 0;


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
        if(!preg_match('/([a-z]+)\/([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?/', $usage_str,  $matches)) {
            // error
        }
        else {
            // store original usage string
            $this->usage_str = $usage_str;

            $this->type = $matches[1];
            $this->subtype = $matches[2];
            $tree = isset($matches[4])?$matches[4]:'';
            // accepts various formats ({length} (ex.'255'), {precision}.{scale} (ex. '5:3'), or {shortcut} (ex. 'medium'))
            $this->length = isset($matches[7])?$matches[7]:0;
            $this->scale = isset($matches[8])?$matches[8]:0;
        }

    }

}
