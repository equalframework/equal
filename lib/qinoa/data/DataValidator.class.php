<?php
namespace qinoa\data;

use qinoa\organic\Service;

class DataValidator extends Service {
    

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     * the value will be returned unchanged if:
     * - a conversion is not explicitely defined
     * - a conversion cannot be made
     */
    protected function __construct(/* no dependency */) {
        // initial configuration
    }
    
    /**  
     * Tells if given $value comply with related $constraints set.
     *
     * Accepted elementary types are: 'boolean' (or 'bool'), 'integer' (or 'int'), 'double' (or 'float'), 'string', 'array', 'file'
     * 'file' is a pseudo type whic covers PHP file structure from multipart/form-data, base64 encoded binary value
     *
     * Constraints is an array holding constraints description specific to the given value 
     * it is an array of validation rules, each rule consist of a kind
     * - type (boolean, integer, double, string, date, array)
     * - 'min', 'max', 'in', 'not in'
     * - 'regex' or 'pattern'
     * - (custom) function (any callable accepting one parameter and returning a boolean)
     *
     * and the description of the rule itself
     *
     * examples:
     * [ ['kind' => 'function', 'rule' => function() {return true;}],
     *   ['kind' => 'min', 'rule' => 0 ],      
     *   ['kind' => 'in', 'rule' => [1, 2, 3] ]     
     * ]
     *
     */
    public function validate($value, $constraints) {        
        if(!is_array($constraints) || empty($constraints)) return true;
        foreach($constraints as $id => $constraint) {
            if(!isset($constraint['kind']) || !isset($constraint['rule'])) {
                // raise error
                continue;
            }
            switch($constraint['kind']) {
            case 'type':
                // fix alternate names to the expected value
                foreach(['bool' => 'boolean', 'int' => 'integer', 'float' => 'double'] as $key => $type) {
                    if($constraint['rule'] == $key) {
                        $constraint['rule'] = $type;
                        break;
                    }
                }
                // $value type should be amongst elementary PHP types
                if(!in_array($constraint['rule'], ['boolean', 'integer', 'double', 'string', 'date', 'array', 'file'])) {
                    throw new \Exception("Invalid type {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if($constraint['rule'] == 'file') {
                    if(!in_array(gettype($value), ['string', 'array'])) return false;
                }
                else if($constraint['rule'] == 'date') {
                    // dates are internally handed as Unix timestamps
                    if(!gettype($value) == 'integer') return false;
                }
                else if(gettype($value) != $constraint['rule']) return false;
                break;
            case 'pattern':
            case 'regex':
                if(!preg_match("/^\/.+\/[a-z]*$/i", $constraint['rule'])) {
                    throw new \Exception("Invalid pattern {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if(!preg_match($constraint['rule'], $value)) return false;
                break;                
            case 'function':            
                if(!is_callable($constraint['rule'])) {
                    throw new \Exception("Unknown function {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if(call_user_func($constraint['rule'], $value) !== true) return false;
                break;
            case 'min':
                if(!is_numeric($constraint['rule'])) {
                    throw new \Exception("Non numeric min constraint {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                switch(gettype($value)) {
                case 'string':
                    if(strlen($value) < $constraint['rule']) return false;
                    break;                        
                case 'integer':
                case 'double':                    
                    if($value < $constraint['rule']) return false;
                    break;
                case 'array': 
                    if(count($value) < $constraint['rule']) return false;
                    break;
                default:
                    // error : unhandled value type for contraint 'min'
                    break;
                }
                break;
            case 'max':
                if(!is_numeric($constraint['rule'])) {
                    throw new \Exception("Non numeric max constraint {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                switch(gettype($value)) {
                case 'string':
                    if(strlen($value) > $constraint['rule']) return false;
                    break;                        
                case 'integer':
                case 'double':                    
                    if($value > $constraint['rule']) return false;
                    break;
                case 'array': 
                    if(count($value) > $constraint['rule']) return false;
                    break;
                default:
                    // error : unhandled value type for contraint 'max'
                    break;
                }
                break;
            case 'in':
            case 'not in':            
                if(!is_array($constraint['rule'])) {
                    // warning : 'in' and 'not in' constraint has to be array
                    // try to force conversion to array
                    $constraint['rule'] = [$constraint['rule']];                    
                }
                $type = gettype($value);
                if($type == 'string') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_string($accept)) {
                            // error : while checking a string 'in' constraint has to hold string values
                            unset($constraint['rule'][$index]);
                        }
                    }
                }
                else if ($type == 'integer') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_integer($accept)) {
                            // error : while checking an integer 'in' constraint has to hold integer values
                            unset($constraint['rule'][$index]);
                        }
                    }
                }
                else if ($type == 'double') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_integer($accept) && !is_double($accept)) {
                            // error : while checking a float/double 'in' constraint has to hold float values
                            unset($constraint['rule'][$index]);
                        }
                    }                
                }
                else {                
                    // error : unhandled value type for contraint 'max'
                    continue;
                }
                if(in_array($value, $constraint['rule'])) {
                    if($constraint['kind'] == 'not in') return false;
                }
                else if($constraint['kind'] == 'in') return false;
                break;
            default:
                // warning : unhandled constraint type {$constraint['kind']}
                break;                        
            }
        }
        return true;
    }
}