<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

class Entity {
    /**
     * @var string
     *
     */
    private $full_name;

    /**
     * @param string $full_name Full name of the entity, including namespace.
     */
    public final function __construct(string $full_name) {
        $this->full_name = trim($full_name, '_\\');
    }

    private function extractParts(): array {
        return explode(' ', str_replace(['\\', '_'], ' ', $this->full_name));
    }

    private function getPath() {
        $parts = $this->extractParts();
        array_shift($parts);
        array_pop($parts);
        return implode('/', $parts);
    }

    public function getFullName() {
        return $this->full_name;
    }

    public function getName() {
        $parts = $this->extractParts();
        return end($parts);
    }

    public function getPackageName() {
        $parts = $this->extractParts();
        return reset($parts);
    }

    public function getNamespace() {
        $parts = $this->extractParts();
        array_pop($parts);
        return implode('\\', $parts);
    }

    /**
     * This method returns the theoretical parent Entity (current entity might not relate to an existing file).
     */
    public function getParent(): ?Entity {
        if($this->getType() == 'class') {
            if(file_exists($this->getFullFilePath())) {
                $parent = get_parent_class($this->full_name);
                if($parent) {
                    return new Entity($parent);
                }
            }
            else {
                $parts = $this->extractParts();
                array_shift($parts);
                return new Entity(implode('\\', $parts));
            }
        }
        else {
            // #todo (call controller with announce)
        }
        return null;
    }

    /**
     * @return string Returns 'class' or 'controller', according to the kind of entity.
     */
    public function getType(): string {
        return (strpos($this->full_name, '\\') === false) ? 'controller' : 'class';
    }

    public function getFullFilePath(): string {
        $filepath = '';
        $package = $this->getPackageName();
        if($this->getType() == 'class') {
            $filepath = EQ_BASEDIR.'/packages/'.$package.'/classes/';
            $path = $this->getPath();
            if(strlen($path)) {
                $filepath .= $path.'/';
            }
            $filepath .= $this->getName().'.class.php';
        }
        else {
            $filepath = EQ_BASEDIR.'/packages/'.$package.'/data/';
            $path = $this->getPath();
            if(strlen($path)) {
                $filepath .= $path.'/';
            }
            $filepath .= $this->getName().'.php';
        }
        return $filepath;
    }
}