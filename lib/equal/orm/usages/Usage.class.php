<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


abstract class Usage {

    /** @var string */
    private $def = '';

    /** @var string */
    // subtype is a tree (ex: node1.node2.node3)
    private $subtype = '';

    /** @var string */
    private $length = '';

    abstract public function getSqlType(): string;

    abstract public function getType(): string;

    abstract public function getConstraints(): array;

    final public function getSubtype() {
        return $this->subtype;
    }

    final public function getLength(): string {
        return $this->length;
    }

    /**
     * Associative array mapping accepted subtypes
     * @var array */
    private $variations = [];

    final public function __construct(string $def) {
        $this->def = $def;
        // extract subtype and length
        $parts = explode(':', $def);
        $this->subtype = $parts[0];
        // accepts various formats (single int ('255'), precision.scale ('5:3')), defaults to '0'.
        $this->length = isset($parts[1])?$parts[1]:'0';
    }

    /**
     * Export a given value following the usage definition, according to given locale.
     * If necessary, format is retrieved from the locale.json file of the core package.
     *
     * @return mixed     Returns the value adapted to the usage, according to a specific locale, if given.
     */
    abstract public function export($value, $lang='en'): string;

}
