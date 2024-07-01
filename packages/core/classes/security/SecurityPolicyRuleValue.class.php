<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class SecurityPolicyRuleValue extends Model {

    public static function getName() {
        return 'Security Policy';
    }

    public static function getDescription() {
        return "Security policies allow the verification of any incoming request against a set of validation rules.";
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Full path setting code to serve as reference (unique).",
                'function'          => 'calcName',
                'store'             => true,
                'readonly'          => true
            ],

            'security_policy_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\security\SecurityPolicy',
                'description'       => 'Security policy the value relates to.'
            ],

            'policy_rule_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\security\SecurityPolicyRule',
                'description'       => 'Security policy rule the value relates to.'
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'Value or range that the request must comply to.',
                'help'              => 'This fields depends on the type set in the related rule.'
            ]

        ];
    }

}
