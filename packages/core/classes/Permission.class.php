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
                'type'              => 'string'
            ],
            'domain' => [
                'type'              => 'string'
            ],
            'group_id' => [
                'type'              => 'many2one', 
                'foreign_object'    => 'core\Group'
            ],
            'user_id' => [
                'type'              => 'many2one', 
                'foreign_object'    => 'core\User'],
            'rights' => [
                'type' 	            => 'integer',
                'onchange'          => 'core\Permission::onchangeRights'
            ],
            // virtual fields, used in list views
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
        $rights = Permission::getRightsTxt($om, $ids, $lang);
        foreach($ids as $oid) {
            $om->write(__CLASS__, $oid, array('rights_txt' => $rights[$oid]), $lang);
        } 
    }

    public static function getRightsTxt($om, $ids, $lang) {
        $res = array();
        $values = $om->read('core\Permission', $ids, array('rights'), $lang);
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