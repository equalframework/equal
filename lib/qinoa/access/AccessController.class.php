<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\access;

use qinoa\organic\Service;
use qinoa\services\Container;

class AccessController extends Service {
       
    private $permissionsTable;
    
    private $groupsTable;
   
    
    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        $this->permissionsTable = array();        
        $this->groupsTable = array();        
    }

    public static function constants() {
        return ['R_CREATE', 'R_READ', 'R_WRITE', 'R_DELETE', 'R_MANAGE', 'DEFAULT_RIGHTS', 'DEFAULT_GROUP_ID', 'ROOT_USER_ID', 'GUEST_USER_ID'];
    }
    

    private function getUserGroups($user_id) {        
        if(!isset($this->groupsTable[$user_id])) {
            $orm = $this->container->get('orm');
            $values = $orm->read('core\User', $user_id, ['groups_ids']);
            $this->groupsTable[$user_id] = array_unique(array_merge([(string) DEFAULT_GROUP_ID], $values[$user_id]['groups_ids']));
        }
        return $this->groupsTable[$user_id];
    }
    
    private function getUserRights($user_id, $object_class) {
		// all users are at least granted the default permissions
		$user_rights = DEFAULT_RIGHTS;

        // if we did already compute user rights then we're done!
		if(isset($this->permissionsTable[$user_id][$object_class])) {
            $user_rights = $this->permissionsTable[$user_id][$object_class];
        }
		else {
            // root user always has full rights
            if($user_id == ROOT_USER_ID) $user_rights = R_CREATE | R_READ | R_WRITE | R_DELETE | R_MANAGE;
            else if($user_id != GUEST_USER_ID) {
                $orm = $this->container->get('orm');
                // extract package from class name (for wildcard notation)
                $object_package = $orm->getObjectPackageName($object_class);
                // get user groups  
                $groups_ids = $this->getUserGroups($user_id);                                
                // check if permissions are defined for the current user on given object class
                $acl_ids = $orm->search('core\Permission', [
                                    [ ['class_name', '=', $object_class], ['group_id', 'in', $groups_ids] ],
                                    [ ['class_name', '=', $object_class], ['user_id', '=', $user_id] ],
                                    [ ['class_name', '=', $object_package.'\*'], ['group_id', 'in', $groups_ids] ],
                                    [ ['class_name', '=', $object_package.'\*'], ['user_id', '=', $user_id] ],
                                ]);
                if(count($acl_ids)) {
                    // get the user permissions
                    $values = $orm->read('core\Permission', $acl_ids, array('rights'));
                    foreach($values as $acl_id => $row) $user_rights |= $row['rights'];
                }
                
                
                if(!isset($this->permissionsTable[$user_id])) $this->permissionsTable[$user_id] = array();
                // first element of the class-related array is used to store the user permissions for the whole class
                $this->permissionsTable[$user_id][$object_class] = $user_rights;
            }                
		}
		return $user_rights;
	}

    /**
     *
     * @param   $object_class  string  name of the class on which rights apply to : wildcards are allowed (ex. '*' or 'core\*')
     *
     */
    private function setUserRights($right, $user_id, $object_class) {
        // all users belong to the default group
        $orm = $this->container->get('orm');
        // search for an ACL describing this specific user
        $acl_ids = $orm->search('core\Permission', [
                            [ ['class_name', '=', $object_class], ['user_id', '=', $user_id] ]
                        ]);       
        if(count($acl_ids)) {
            $values = $orm->write('core\Permission', $acl_ids, ['right' => $right]);
        }
        else {
            $orm->create('core\Permission', ['class_name' => $object_class, 'user_id' => $user_id, 'right' => $right]);
        }
    }
    

    public function grant($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();
        $rights = $this->getUserRights($user_id, $object_class);
        $rights = $rights | $operation;
        $this->setUserRights($rights, $user_id, $object_class);
    }
   
    public function revoke($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();            
        $rights = $this->getUserRights($user_id, $object_class);   
        $rights = $rights & ~$operation;   
        $this->setUserRights($rights, $user_id, $object_class);        
    }
    
    public function isAllowed($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
		if(DEFAULT_RIGHTS & $operation) return true;

        $auth = $this->container->get('auth');
        
        $user_id = $auth->userId();

        // build final user rights
        $user_rights = DEFAULT_RIGHTS;		
        $user_rights |= $this->getUserRights($user_id, $object_class);

		return (bool) ($user_rights & $operation);
    }
}