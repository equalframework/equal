<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Permission extends Model {

    public static function getColumns() {
        return [
            'class_name' => [
                'type'              => 'string',
                'description'       => 'Full name of the entity to which the permission rule applies.'
            ],

            'domain' => [
                'type'              => 'string',
                'description'       => "JSON value of the constraints domain (ex. ['creator', '=', '1'])"
            ],

            'group_id' => [
                'type'              => 'many2one', 
                'foreign_object'    => 'core\Group',
                'description'       => "Targeted group, if permission applies to a group.",
                'default'           => DEFAULT_GROUP_ID
            ],

            'user_id' => [
                'type'              => 'many2one', 
                'foreign_object'    => 'core\User',
                'description'       => "Targeted user, if permission applies to a single user."
            ],

            'rights' => [
                'type' 	            => 'integer',
                'onchange'          => 'core\Permission::onchangeRights',
                'description'       => "Rights binary mask (1: CREATE, 2: READ, 4: WRITE, 8 DELETE, 16: MANAGE)"
            ],

            // virtual field, used in list views
            'rights_txt' => [
                'type'              => 'computed', 
                'store'             => true, 
                'result_type'       => 'string', 
                'function'          => 'core\Permission::getRightsTxt'
            ]
        ];
    }

    public static function onchangeRights($om, $ids, $lang) {
        // note : we are in the core namespace, so we don't need to specify it when referring to this class
        $rights = self::getRightsTxt($om, $ids, $lang);
        foreach($ids as $oid) {
            $om->write(__CLASS__, $oid, ['rights_txt' => $rights[$oid]], $lang);
        } 
    }

    public static function getRightsTxt($om, $ids, $lang) {
        $res = array();
        $values = $om->read(__CLASS__, $ids, ['rights'], $lang);
        foreach($ids as $oid) {
            $rights_txt = array();
            $rights = $values[$oid]['rights'];
            if($rights & QN_R_CREATE)   $rights_txt[] = 'create';
            if($rights & QN_R_READ)     $rights_txt[] = 'read';
            if($rights & QN_R_WRITE)    $rights_txt[] = 'write';
            if($rights & QN_R_DELETE)   $rights_txt[] = 'delete';
            if($rights & QN_R_MANAGE)   $rights_txt[] = 'manage';
            $res[$oid] = implode(', ', $rights_txt);
        }
        return $res;
    }

}