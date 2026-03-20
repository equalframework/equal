<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
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


    public function updateMethod(string $methodName, array $newData) {
        try {
            $class = new ReflectionClass($this->full_name);

            if (!$class->hasMethod($methodName)) {
                throw new Exception("missing_method", QN_ERROR_UNKNOWN);
            }

            $method_code = "    public static function $methodName() {\n" .
                           "        return " . (empty($newData) ? '[]' : $this->arrayExport($newData, 4, 2, true)) . ";\n" .
                           "    }";

            $this->updateMethodCode($class, $methodName, $method_code);
        } catch (ReflectionException $e) {
            throw new Exception("reflection_error: " . $e->getMessage(), QN_ERROR_UNKNOWN);
        }
    }

    public function updateMethodLine(string $methodName, string $newKey, $defaultValue = []) {
        try {
            $class = new ReflectionClass($this->full_name);

            if (!$class->hasMethod($methodName)) {
                throw new Exception("missing_method", QN_ERROR_UNKNOWN);
            }

            $oldData = call_user_func([$this->full_name, $methodName]);
            if (!is_array($oldData)) {
                $oldData = [];
            }

            if (!array_key_exists($newKey, $oldData)) {
                $oldData[$newKey] = $defaultValue;
            }

            $method_code = "    public static function $methodName() {\n" .
                           "        return " . $this->arrayExport($oldData, 4, 2, true) . ";\n" .
                           "    }";

            $this->updateMethodCode($class, $methodName, $method_code);
        } catch (ReflectionException $e) {
            throw new Exception("reflection_error: " . $e->getMessage(), QN_ERROR_UNKNOWN);
        }
    }


    private function updateMethodCode(ReflectionClass $class, string $methodName, string $newCode) {
        try {
            $file = $class->getFileName();
            if (!$file || !file_exists($file)) {
                throw new Exception("File not found: $file", QN_ERROR_UNKNOWN);
            }

            $code = file_get_contents($file);
            if ($code === false) {
                throw new Exception("Failed to read file: $file", QN_ERROR_UNKNOWN);
            }

            $lines = explode("\n", $code);

            try {
                $method = new ReflectionMethod($class->getName(), $methodName);
            } catch (ReflectionException $e) {
                throw new Exception("Method $methodName not found in class " . $class->getName(), QN_ERROR_UNKNOWN);
            }

            $start_index = $method->getStartLine() - 1;
            $end_index = $method->getEndLine() - 1;

            if ($start_index < 0 || $end_index < 0 || $end_index < $start_index) {
                throw new Exception("Invalid method boundaries for $methodName", QN_ERROR_UNKNOWN);
            }

            $result = '';
            foreach ($lines as $index => $line) {
                if ($index < $start_index) {
                    $result .= $line . "\n";
                } elseif ($index == $start_index) {
                    $result .= $newCode . "\n";
                } elseif ($index > $end_index) {
                    $result .= $line . "\n";
                }
            }

            if (file_put_contents($file, rtrim($result) . "\n") === false) {
                throw new Exception("Failed to write to file: $file", QN_ERROR_UNKNOWN);
            }
        } catch (Exception $e) {
            error_log("Error in updateMethodCode: " . $e->getMessage());
            throw $e;
        }
    }




    private function arrayExport($array, $indent_spaces = 4, $pad_indents = 0, $ignore_first_indent = false) {
        if (!is_array($array)) {
            return '';
        }

        $export = var_export($array, true);

        $patterns = [
            "/array \(/"                        => '[',
            "/^([ ]*)\)(,?)$/m"                 => '$1]$2',
            "/=>[ ]?\n[ ]+\[/"                  => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/"  => '$1$2 => $3',
            "/[0-9]+ => /"                      => ''
        ];

        $result = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if (empty($result)) {
            return '';
        }

        $lines = explode("\n", $result);
        foreach ($lines as $index => $line) {
            if (!$ignore_first_indent || $index > 0) {
                $code = ltrim($line);
                $indents = (strlen($line) - strlen($code)) / 2;
                $lines[$index] = str_pad('', $pad_indents * $indent_spaces, ' ') .
                                 str_pad('', $indents * $indent_spaces, ' ') .
                                 $code;
            }
        }

        return implode("\n", $lines);
    }
}