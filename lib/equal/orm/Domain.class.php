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
 *			],
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
    }

    /**
     * Add a condition at the Domain level : the condition is added to each clause of the Domain
     */
    public function addCondition($condition) {
        foreach($this->clauses as $clause) {
            $clause->addCondition($condition);
        }
    }

    /*
    * domain checks and operations
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
            trigger_error("QN_DEBUG_ORM::condition is not an array", QN_REPORT_DEBUG);
            return false;
        }
        // condition must be composed of 3 elements (field, operator, operand)
        if(count($condition) != 3) {
            trigger_error("QN_DEBUG_ORM::missing condition in domain", QN_REPORT_DEBUG);
            return false;
        }
        // we need to have access to class definition to fully check conditions
        if(!empty($schema)) {
            $field = $condition[0];
            $operator = $condition[1];
            // first operand (field) must be a valid field
            if(!in_array($field, array_keys($schema))) {
                trigger_error("QN_DEBUG_ORM::unknown field '{$field}' in domain", QN_REPORT_DEBUG);
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
                trigger_error("QN_DEBUG_ORM::invalid operator '{$operator}' in domain", QN_REPORT_DEBUG);
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
        // add contion to all clauses
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