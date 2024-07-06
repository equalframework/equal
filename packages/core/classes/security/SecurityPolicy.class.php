<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class SecurityPolicy extends Model {

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
                'description'       => "Policy name to serve as reference."
            ],

            'is_active' => [
                'type'              => 'boolean',
                'description'       => "Marks the setting as translatable.",
                'default'           => false
            ],

            'description' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => "Description of the role and tests performed by the policy."
            ],

            'policy_rules_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\security\SecurityPolicyRule',
                'foreign_field'     => 'security_policy_id',
                'sort'              => 'asc',
                'order'             => 'user_id',
                'description'       => 'List of rules related to the policy.',
                'visible'           => ['is_active', '=', true]
            ]

        ];
    }

}
