<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

class Operation {

    /** @var array */
    private static $unary_operators = ['ABS', 'AVG', 'COUNT', 'DIFF', 'MAX', 'MIN', 'SUM'];

    /** @var array */
    private static $binary_operators = ['+', '-', '*', '/', '%', 'round', 'exp', 'root'];

    /**
     * Operator to apply on received operands.
     * @var string
     */
    private $operator;

    /**
     * An array holding exactly one or two operands.
     * Each operand can be either a value, a target field, or an Operation object.
     * @var array
     */
    private $operands;

    /**
     * Values resulting from the computation of current operation.
     * @var array
     */
    private $values;

    public function __construct() {
        $this->value = [];
        $this->operator = '';
        $this->operands = [];

        // constructor with a single array as arg (array notation)
        $arg = func_get_arg(0);

        // constructor with 2 args
        if(func_num_args() > 1) {
            $arg = [func_get_arg(0), func_get_arg(1)];
        }

        $this->fromArray($arg);
    }

    /**
     * Set current instance from an array notation.
     * @param array $operation  Array representing the operation.
     */
    public function fromArray($operation=[]) {
        if(count($operation) >= 2) {
            $operator = $operation[0];
            $this->operands = [];

            $operand = $operation[1];
            if(is_array($operation[1])) {
                $operand = new Operation($operation[1]);
            }
            $this->operands[] = $operand;

            if(in_array($operator, self::$binary_operators)) {
                $operand = $operation[2];
                if(is_array($operation[2])) {
                    $operand = new Operation($operation[2]);
                }
                $this->operands[] = $operand;
            }

            $this->operator = $operator;
        }

        return $this;
    }

    /**
     * Apply operator on given values (1 or 2 operands).
     * @param mixed operand_a   Value of the first operand.
     * @param mixed operand_b   Value of the second operand, if any.
     * @return mixed    Returns the resulting value of the operation. If operand_a is an array, the result will be an array.
     */
    private function apply() {
        if(in_array($this->operator, self::$unary_operators)) {
            $is_array = is_array(func_get_arg(0));
            $operand = (array) func_get_arg(0);
            $count = count($operand);
            switch($this->operator) {
                case 'ABS':
                    $res = array_map(function ($a) { return abs($a); }, $operand);
                    if(!$is_array) {
                        return $res[0];
                    }
                    return $res;
                case 'AVG':
                    if(!$count) return 0;
                    return array_sum($operand) / $count;
                case 'COUNT': return $count;
                case 'MIN': return min($operand);
                case 'MAX': return max($operand);
                case 'DIFF': return array_reduce($operand, function ($c, $a) {return $c - $a;}, 0);
                case 'SUM': return array_sum($operand);
            }
        }
        else if(in_array($this->operator, self::$binary_operators)) {
            $is_array = is_array(func_get_arg(0));
            $operand_a = (array) func_get_arg(0);
            $operand_b = (array) func_get_arg(1);
            $count = count($operand_a);
            $result = [];
            for($i = 0, $n = $count; $i < $n; ++$i) {
                switch($this->operator) {
                    case '+':
                        $result[] = $operand_a[$i] + $operand_b[$i];
                        break;
                    case '-':
                        $result[] = $operand_a[$i] - $operand_b[$i];
                        break;
                    case '*':
                        $result[] = $operand_a[$i] * $operand_b[$i];
                        break;
                    case '/':
                        $result[] = $operand_a[$i] / $operand_b[$i];
                        break;
                    case '%':
                        $result[] = $operand_a[$i] % $operand_b[$i];
                        break;
                    case 'round':
                        $result[] = round($operand_a[$i], $operand_b[$i]);
                        break;
                    case 'exp':
                        $result[] = pow($operand_a[$i], $operand_b);
                        break;
                    case 'root':
                        $result[] = pow($operand_a[$i], 1 / $operand_b[$i]);
                        break;
                }
            }
            if(!$is_array) {
                $result = $result[0];
            }
            return $result;
        }
        return 0;
    }

    public function compute(Collection $collection) {
        $result = false;

        if(in_array($this->operator, self::$unary_operators)) {
            $operand = $this->operands[0];
            if($operand instanceof Operation) {
                $value = $operand->compute($collection);
            }
            elseif( strpos($operand, 'object.') !== false) {
                // we have to return an array of values
                $value = [];
                $field = substr($operand, strlen('object.'));
                foreach($collection->get() as $item) {
                    $value[] = $item[$field];
                }
            }
            else {
                // nothing to do
                $value = $operand;
            }
            $result = $this->apply($value);
        }
        else if(in_array($this->operator, self::$binary_operators)) {
            $operand_a = $this->operands[0];
            $operand_b = $this->operands[1];
            if($operand_a instanceof Operation) {
                $value_a = $operand_a->compute($collection);
            }
            elseif(strpos($operand_a, 'object.') !== false) {
                // we have to return an array of values
                $value_a = [];
                $field = substr($operand_a, strlen('object.'));
                foreach($collection->get() as $item) {
                    $value_a[] = $item[$field];
                }
            }
            else {
                // nothing to do
                $value_a = $operand_a;
            }
            if($operand_b instanceof Operation) {
                $value_b = $operand_b->compute($collection);
            }
            elseif(strpos($operand_b, 'object.') !== false) {
                // we have to return an array of values
                $value_b = [];
                $field = substr($operand_b, strlen('object.'));
                foreach($collection->get() as $item) {
                    $value_b[] = $item[$field];
                }
            }
            else {
                // nothing to do
                $value_b = $operand_b;
            }
            $result = $this->apply($value_a, $value_b);
        }
        return $result;
    }

}