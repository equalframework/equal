<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

/**
 *
 *     $domain = [                                        // domain
 *        [                                               // clause
 *            [
 *                '{operand}', '{operator}', '{value}'    // condition
 *            ],
 *            [
 *                '{operand}', '{operator}', '{value}'    // another contition (AND)
 *            ]
 *        ],
 *        [		                                          // another clause (OR)
 *            [
 *				'{operand}', '{operator}', '{value}'      // condition
 *			  ],
 *            [
 *                '{operand}', '{operator}', '{value}'    // another contition (AND)
 *            ]
 *        ]
 *    ];
 *
 */


class Domain {

    private $clauses;

    public function __construct($domain=[]) {
        $this->fromArray($domain);
    }

    public function fromArray($domain=[]) {
        // reset clauses
        $this->clauses = [];
        /*
            supported formats :
            1) empty  domain : []
            2) 1 condition only : [ '{operand}', '{operator}', '{value}' ]
            3) 1 clause only (one or more conditions) : [ [ '{operand}', '{operator}', '{value}' ], [ '{operand}', '{operator}', '{value}' ] ]
            4) multiple clauses : [ [ [ '{operand}', '{operator}', '{value}' ], [ '{operand}', '{operator}', '{value}' ] ], [ [ '{operand}', '{operator}', '{value}' ] ] ]
        */
        $normalized = self::normalize($domain);

        foreach($normalized as $d_clause) {
            $clause = new DomainClause();
            foreach($d_clause as $d_condition) {
                $clause->addCondition(new DomainCondition($d_condition[0], $d_condition[1], $d_condition[2]));
            }
            $this->addClause($clause);
        }
        return $this;
    }

    public function toArray() {
        $domain = [];
        foreach($this->clauses as $clause) {
            $domain[] = $clause->toArray();
        }
        return $domain;
    }

    public function getClauses() {
        return $this->clauses;
    }

    /**
     * Add a clause at the Domain level : the clause is appened to the Domain
     */
    public function addClause($clause) {
        $this->clauses[] = $clause;
        return $this;
    }

    /**
     * Add a condition at the Domain level : the condition is added to each clause of the Domain
     */
    public function addCondition($condition) {
        if(!count($this->clauses)) {
            $clause = new DomainClause();
            $this->addClause($clause);
        }
        foreach($this->clauses as $clause) {
            $clause->addCondition($condition);
        }
        return $this;
    }


    /**
     * Update domain by parsing conditions and replace any occurence of `object.` and `user.` notations with related attributes of given objects.
     *
     * @param values
     * @returns Domain  Returns current instance with updated values.
     */
    public function parse($object = [], $user = []) {
        foreach($this->clauses as $clause) {
            foreach($clause->conditions as $condition) {
                // adapt value according to its syntax ('user.' or 'object.')
                $value = $condition->value;

                // handle object references as `value` part
                if(is_string($value) && strpos($value, 'object.') == 0 ) {
                    $target = substr($value, 0, strlen('object.'));
                    if(!$object || !isset($object[$target])) {
                        continue;
                    }
                    $tmp = $object[$target];
                    // target points to an object with subfields
                    if(is_array($tmp)) {
                        if($tmp['id']) {
                            $value = $tmp['id'];
                        }
                        else if(isset($tmp['name'])) {
                            $value = $tmp['name'];
                        }
                        else {
                            continue;
                        }
                    }
                    else {
                        $value = $object[$target];
                    }
                }
                // handle user references as `value` part
                else if(is_string($value) && strpos($value, 'user.') == 0) {
                    $target = substr($value, 0, strlen('user.'));
                    if(!$user || !isset($user[$target])) {
                        continue;
                    }
                    $value = $user[$target];
                }
                else if(is_string($value) && strpos($value, 'date.') == 0) {
                    // #todo
                    // $value = (new DateReference($value)).getDate().toISOString();
                }

                $condition->value = $value;
            }
        }
        return $this;
    }

    /**
     * Evaluate domain for a given object.
     * Object structure has to comply with the operands mentionned in the conditions of the domain. If no, related conditions are ignored (skipped).
     *
     * @param object
     * @return boolean Return true if the object matches the domain, false otherwise.
     */
    public function evaluate($object) {
        $res = false;
        if(count($this->clauses) == 0) {
            return true;
        }
        // parse any reference to object in conditions
        $this->parse($object);
        // evaluate clauses (OR) and conditions (AND)
        foreach($this->clauses as $clause) {
            $c_res = true;
            foreach($clause->conditions as $condition) {

                if(!isset($object[$condition->operand])) {
                    continue;
                }

                $operand = $object[$condition->operand];
                $operator = $condition->operator;
                $value = $condition->value;

                $cc_res = false;

                // handle special cases
                if($operator == '=') {
                    $operator = '==';
                }
                else if($operator == '<>') {
                    $operator = '!=';
                }

                if($operator == 'is' && is_numeric($value)) {
                    $operator = '==';
                }

                if($operator == 'is') {
                    if( $value === true ) {
                        $cc_res = $operand;
                    }
                    else if( in_array($value, [false, null, 'null', 'empty']) ) {
                        $cc_res = ( in_array($operand, ['', false, null]) || (is_array($operand) && !count($operand)) );
                    }
                    else {
                        continue;
                    }
                }
                else if($operator == 'in') {
                    if(!is_array($value)) {
                        continue;
                    }
                    $cc_res = in_array($operand, $value);
                }
                else if($operator == 'not in') {
                    if(!is_array($value)) {
                        continue;
                    }
                    $cc_res = !in_array($operand, $value);
                }
                else if($operator == 'like') {
                    $cc_res = (strpos($operand, str_replace('%', '', $value)) !== false);
                }
                else if($operator == 'ilike') {
                    $cc_res = (stripos($operand, str_replace('%', '', $value)) !== false);
                }
                else {
                    $c_condition = "return ( '".$operand."' ".$operator." '".$value."');";
                    $cc_res = eval($c_condition);
                }
                $c_res = $c_res && $cc_res;
            }
            $res = $res || $c_res;
        }
        return $res;
    }


    /*
    * Domain checks and operations.
    * a domain should always be composed of a serie of clauses against which a OR test is made
    * a clause should always be composed of a serie of conditions agaisnt which a AND test is made
    * a condition should always be composed of a property operand, an operator, and a value
    */

    /**
     * Checks condition validity (format and consistency against schema)
     * operand is checked based on value/type compatibility
     *
     */
    private static function conditionCheck($condition, $schema=[]) {
        // condition must be an array
        if(!is_array($condition)) {
            trigger_error("ORM::condition is not an array", QN_REPORT_DEBUG);
            return false;
        }
        // condition must be composed of 3 elements (field, operator, operand)
        if(count($condition) != 3) {
            trigger_error("ORM::missing condition in domain", QN_REPORT_DEBUG);
            return false;
        }
        // we need to have access to class definition to fully check conditions
        if(!empty($schema)) {
            $field = $condition[0];
            $operator = $condition[1];
            // first operand (field) must be a valid field
            if(!in_array($field, array_keys($schema))) {
                trigger_error("ORM::unknown field '{$field}' in domain", QN_REPORT_DEBUG);
                return false;
            }
            // handle 'alias'
            $is_alias = false;
            while($schema[$field]['type'] == 'alias') {
                $is_alias = true;
                $field = $schema[$field]['alias'];
            }
            $target_type = $schema[$field]['type'];
            if($target_type == 'computed') {
                $target_type = $schema[$field]['result_type'];
            }
            if($is_alias) {
            // #todo - adapt operator based on target type
            }
            // operator must be amongst valid operators for specified field
            if(!in_array($operator, ObjectManager::$valid_operators[$target_type])) {
                trigger_error("ORM::invalid operator '{$operator}' in domain", QN_REPORT_DEBUG);
                return false;
            }
        }
        return true;
    }

    private static function clauseCheck($clause, $schema=[]) {
        if(!is_array($clause)) return false;
        foreach($clause as $condition) {
            if(!self::conditionCheck($condition, $schema)) {
                return false;
            }
        }
        return true;
    }

    private static function domainCheck($domain, $schema=[]) {
        if(!is_array($domain)) return false;
        foreach($domain as $clause) {
            if(!self::clauseCheck($clause, $schema)) {
                return false;
            }
        }
        return true;
    }

    public static function normalize($domain) {
        if(!is_array($domain) || empty($domain) ) {
            return [];
        }

        if(!is_array($domain[0])) {
            // single condition
            $domain = [[$domain]];
        }
        else {
            if(empty($domain[0])) {
                return [];
            }
            if(!is_array($domain[0][0])) {
                // single clause
                $domain = [$domain];
            }
        }
        return $domain;
    }

    /**
     * @param Domain    $domain     Domain to be merged with current domain.
     */
    public function merge($domain) {
        $res_domain = [];
        $domain_a = $domain->toArray();
        $domain_b = $this->toArray();

        if(count($domain_a) <= 0) {
            $res_domain = $domain_b;
        }
        else if(count($domain_b) <= 0) {
            $res_domain = $domain_a;
        }
        else {
            foreach($domain_a as $clause_a) {
                foreach($domain_b as $clause_b) {
                    $res_domain[] = array_merge($clause_a, $clause_b);
                }
            }
        }
        return $this->fromArray($res_domain);
    }

    public static function validate($domain, $schema=[]) {
        $domain = self::normalize($domain);
        return self::domainCheck($domain, $schema);
    }

    public static function toString($domain) {
        $domain = self::normalize($domain);
        foreach($domain as $i => $clause) {
            foreach($clause as $j => $condition) {
                $operand = "{$condition[0]}";
                $operator = "{$condition[1]}";
                $value = $condition[2];
                if(is_array($value)) {
                    $value = "['".implode("','", $value)."']";
                }
                else {
                    $value = "'$value'";
                }
                $clause[$j] = "[{$operand},{$operator},{$value}]";
            }
            $domain[$i] = '['.implode(',', $clause).']';
        }
        return '['.implode(',', $domain).']';
    }

	/**
	 * Adds a condition to a clause
	 */
    public static function clauseConditionAdd($clause, $condition) {
        if(!self::conditionCheck($condition)) return $clause;
        $clause[] = $condition;
        return $clause;
    }

	/**
	 * Adds a condition to the domain
	 *
	 * @return	array	resulting domain
	 */
    public static function conditionAdd($domain, $condition) {
        if(!self::conditionCheck($condition)) return $domain;

        $domain = self::normalize($domain);

        // create an empty clause if none yet
        if(count($domain) == 0) {
            $domain[] = [];
        }

        // add condition to all clauses
        for($i = 0, $j = count($domain); $i < $j; ++$i) {
            $domain[$i] = self::clauseConditionAdd($domain[$i], $condition);
        }

        return $domain;
    }

	/**
	 * Adds a clause to the domain
	 */
    public static function clauseAdd($domain, $clause) {
        if(!self::clauseCheck($clause)) return $domain;

        $domain = self::normalize($domain);

        $domain[] = $clause;
        return $domain;
    }

}


class DomainClause {
    public $conditions;

    public function __construct($conditions = []) {
        if(!is_array($conditions) || count($conditions) == 0) {
            $this->conditions = [];
        }
        else {
            $this->conditions = $conditions;
        }
    }

    public function addCondition($condition) {
        $this->conditions[] = $condition;
        return $this;
    }

    public function getConditions() {
        return $this->conditions;
    }

    public function toArray() {
        $clause = [];
        foreach($this->conditions as $condition) {
            // we do not support object related notation back-end
            $value = $condition->getValue();
            if(is_array($value) || strpos($value, 'object.') === false) {
                $clause[] = $condition->toArray();
            }
        }
        return $clause;
    }
}

class DomainCondition {
    public $operand;
    public $operator;
    public $value;

    public function __construct($operand, $operator, $value) {
        $this->operand = $operand;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function toArray() {
        $condition = [];
        $condition[] = $this->operand;
        $condition[] = $this->operator;
        $condition[] = $this->value;
        return $condition;
    }

    public function getOperand() {
        return $this->operand;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function getValue() {
        return $this->value;
    }

}