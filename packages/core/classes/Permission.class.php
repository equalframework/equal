<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;
use equal\services\Container;

/**
 * @property string     $name
 * @property string     $class_name     Full name of the entity to which the permission rule applies.
 * @property string     $domain         JSON value of the constraints domain (ex. ['creator', '=', '1'])
 * @property integer    $object_id      Identifier of the specific object on which the permission applies.
 * @property integer    $group_id       Targeted group, if permission applies to a group.
 * @property integer    $user_id        Targeted user, if permission applies to a single user.
 * @property integer    $rights         Rights binary mask (1: CREATE, 2: READ, 4: WRITE, 8 DELETE, 16: MANAGE)
 * @property string     $rights_txt
 */
class Permission extends Model {

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'class_name'
            ],

            'description' => [
                'type'              => 'string',
                'description'       => 'Short optional description explaining the role of the Permission.',
                'default'           => ''
            ],

            'class_name' => [
                'type'              => 'string',
                'description'       => 'Full name of the entity to which the permission rule applies.',
                'required'          => true
            ],

            'domain' => [
                'deprecated'        => "use `getRole()` for each specific Model",
                'type'              => 'string',
                'description'       => "JSON value of the constraints domain (ex. ['creator', '=', '1'])",
                'default'           => NULL
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the specific object on which the permission applies."
            ],

            'group_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\Group',
                'description'       => "Targeted group, if permission applies to a group.",
                'ondelete'          => 'cascade',
                'default'           => EQ_DEFAULT_GROUP_ID
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'ondelete'          => 'cascade',
                'description'       => "Targeted user, if permission applies to a single user."
            ],

            'rights' => [
                'type' 	            => 'integer',
                'description'       => "Rights binary mask (1: CREATE, 2: READ, 4: WRITE, 8: DELETE, 16: MANAGE)",
                'dependents'        => ['rights_txt']
            ],

            // virtual field, used in list views
            'rights_txt' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcRightsTxt',
                'store'             => true
            ]
        ];
    }


    private static function computeRightsTxt($rights) {
        $result = [];

        if($rights & EQ_R_CREATE) {
            $result[] = 'create';
        }
        if($rights & EQ_R_READ) {
            $result[] = 'read';
        }
        if($rights & EQ_R_WRITE) {
            $result[] = 'write';
        }
        if($rights & EQ_R_DELETE) {
            $result[] = 'delete';
        }
        if($rights & EQ_R_MANAGE) {
            $result[] = 'manage';
        }

        return implode(', ', $result);
    }

    public static function calcRightsTxt($self) {
        $result = [];
        $self->read(['rights']);
        foreach($self as $id => $permission) {
            $result[$id] = self::computeRightsTxt($permission['rights']);
        }
        return $result;
    }


    public function onchange($event) {
        $result = [];
        if(isset($event['rights'])) {
            $result['rights_txt'] = self::computeRightsTxt($event['rights']);
        }
        return $result;
    }

    /**
     * Check if current user can manage Permission objects.
     */
    private static function canManagePermissions(): bool {
        [$auth, $access] = Container::getInstance()->get(['auth', 'access']);

        $user_id = $auth->userId();

        // No user
        if($user_id <= 0) {
            return false;
        }

        // Root bypass
        if($user_id === EQ_ROOT_USER_ID) {
            return true;
        }

        // Check MANAGE right on Permission class
        return $access->hasRight(self::getType(), EQ_R_MANAGE);
    }

    protected static function cancreate($self) {
        if(self::canManagePermissions()) {
            return [];
        }

        $result = [];
        foreach ($self as $id => $values) {
            $result[$id] = [
                'not_allowed' => "You are not allowed to create permission rules."
            ];
        }

        return $result;
    }

    protected static function canupdate($self) {
        if (self::canManagePermissions()) {
            return [];
        }

        $result = [];
        foreach ($self as $id => $permission) {
            $result[$id] = [
                'not_allowed' => "You are not allowed to modify permission rules."
            ];
        }

        return $result;
    }

    protected static function candelete($self) {
        if (self::canManagePermissions()) {
            return [];
        }

        $result = [];
        foreach ($self as $id => $permission) {
            $result[$id] = [
                'not_allowed' => "You are not allowed to delete permission rules."
            ];
        }

        return $result;
    }
}
