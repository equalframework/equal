<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;


class Domain {
    /*
    $domain = [                                         // domain
        [                                               // clause 
            [                                           
                '{operand}', '{operator}', '{value}'    // condition
            ],         
            [                                           
                '{operand}', '{operator}', '{value}'    // another contition (AND)
            ]
        ],
        [		                                        // another clause (OR)
            [	
				'{operand}', '{operator}', '{value}'    // condition
			],
            [   
                '{operand}', '{operator}', '{value}'    // another contition (AND)
            ] 	
        ]
    ];
*/  
    
    
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
            // first operand (field) must be a valid field
            if(!in_array($field, array_keys($schema))) {
                trigger_error("QN_DEBUG_ORM::unknown field '{$field}' in domain", QN_REPORT_DEBUG);
                return false;
            }
            // handle 'alias'
            while($schema[$field]['type'] == 'alias') {
                $field = $schema[$field]['alias'];
            }
            $target_type = $schema[$field]['type'];
            if($target_type == 'computed') {
                $target_type = $schema[$field]['result_type'];
            }
            // operator must be amongst valid operators for specified field
            if(!in_array($condition[1], ObjectManager::$valid_operators[$target_type])) {
                trigger_error("QN_DEBUG_ORM::invalid operator '{$condition[1]}' in domain", QN_REPORT_DEBUG);
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
        if(!is_array($domain) || empty($domain) ) return [[]];
        if(!is_array($domain[0])) {
            // single condition
            $domain = [[$domain]];
        }
        else {
            if(empty($domain[0])) return [[[]]];
            if(!is_array($domain[0][0])) {
                // single clause
                $domain = [$domain];
            }
        }
        return $domain;
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