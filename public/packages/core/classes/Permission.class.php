<?php
namespace core;
use qinoa\orm\Model;

class Permission extends Model {

    public static function getColumns() {
        return [
            'class_name'		=> ['type' => 'string'],
            'group_id'			=> ['type' => 'many2one', 'foreign_object' => 'core\Group'],
            'user_id'			=> ['type' => 'many2one', 'foreign_object' => 'core\User'],
            'rights'			=> [
                                    'type' 	        => 'integer',
                                    'onchange'      => 'core\Permission::onchange_rights'
                                   ],
            // virtual fields, used in list views
            'rights_txt'		=> [
                                    'type'          => 'function', 
                                    'store'         => true, 
                                    'result_type'   => 'string', 
                                    'function'      => 'core\Permission::getRightsTxt'
                                   ]
        ];
    }

    public static function onchange_rights($om, $ids, $lang) {
        // note : we are in the core namespace, so we don't need to specify it when referring to this class
        $rights = Permission::getRightsTxt($om, $ids, $lang);
        foreach($ids as $oid) $om->write('core\Permission', $oid, array('rights_txt' => $rights[$oid]), $lang);
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