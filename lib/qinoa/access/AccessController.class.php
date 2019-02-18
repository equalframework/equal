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

    private $usersTable;

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        $this->permissionsTable = array();
        $this->groupsTable = array();
        $this->usersTable = array();
    }

    public static function constants() {
        return ['DEFAULT_RIGHTS', 'DEFAULT_GROUP_ID', 'ROOT_USER_ID', 'GUEST_USER_ID'];
    }

    private function getUserGroups($user_id) {
		$groups_ids = [];
		if(isset($this->groupsTable[$user_id])) {
			$groups_ids = $this->groupsTable[$user_id];
		}
        else {
            $orm = $this->container->get('orm');
            $values = $orm->read('core\User', $user_id, ['groups_ids']);
			if($values) {
				$this->groupsTable[$user_id] = array_unique(array_merge([(string) DEFAULT_GROUP_ID], $values[$user_id]['groups_ids']));
				$groups_ids = $this->groupsTable[$user_id];
			}
        }
        return $groups_ids;
    }

    private function getGroupUsers($group_id) {
		$users_ids = [];
		if(isset($this->usersTable[$group_id])) {
			$users_ids = $this->usersTable[$group_id];
		}
        else {
            $orm = $this->container->get('orm');
            $values = $orm->read('core\Group', $group_id, ['users_ids']);
			if($values) {
				$this->usersTable[$group_id] = array_unique(array_merge([(string) DEFAULT_GROUP_ID], $values[$group_id]['users_ids']));
				$users_ids = $this->usersTable[$group_id];
			}
        }
        return $users_ids;
    }

    private function getUserRights($user_id, $object_class, $object_fields=[], $object_ids=[]) {
		// all users are at least granted the default permissions
		$user_rights = DEFAULT_RIGHTS;

        // if we did already compute user rights then we're done!
		if(isset($this->permissionsTable[$user_id][$object_class])) {
            $user_rights = $this->permissionsTable[$user_id][$object_class];
        }
		else {
            // root user always has full rights
            if($user_id == ROOT_USER_ID) return QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE;
            else {

                // get user groups
                $groups_ids = $this->getUserGroups($user_id);

                // build array of domain variants
                if($object_class == '*') {
                    $domains = [
                        [ ['class_name', '=', '*'], ['user_id', '=', $user_id] ]
                    ];
                    if(count($groups_ids)) {
                        $domains[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids] ];
                    }
                }
                else {
					$orm = $this->container->get('orm');
                    // extract package from class name (for wildcard notation)
                    $object_package = $orm->getObjectPackageName($object_class);
                    $domains = [
                        [ ['class_name', '=', $object_class], ['user_id', '=', $user_id] ]
                    ];
                    if(count($groups_ids)) {
                        $domains[] = [ ['class_name', '=', $object_class], ['group_id', 'in', $groups_ids] ];
                    }
                    $domains[] = [ ['class_name', '=', $object_package.'\*'], ['user_id', '=', $user_id] ];
                    if(count($groups_ids)) {
                        $domains[] = [ ['class_name', '=', $object_package.'\*'], ['group_id', 'in', $groups_ids] ];
                    }
                    $domains[] = [ ['class_name', '=', '*'], ['user_id', '=', $user_id] ];
                    if(count($groups_ids)) {
                        $domains[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids] ];
                    }
                }

                // fetch ACLs for all domain variants
                $acl_ids = $orm->search('core\Permission', $domains);
                if(count($acl_ids)) {
                    // get the user permissions
                    $values = $orm->read('core\Permission', $acl_ids, ['rights']);
                    foreach($values as $acl_id => $row) {
                        $user_rights |= $row['rights'];
                    }
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
    private function changeUserRights($operator, $rights, $user_id, $object_class) {
        // all users belong to the default group
        $orm = $this->container->get('orm');
        // search for an ACL describing this specific user
        $acl_ids = $orm->search('core\Permission', [ ['class_name', '=', $object_class], ['user_id', '=', $user_id] ]);
        if(count($acl_ids)) {
            $values = $orm->read('core\Permission', $acl_ids, ['rights']);
            // there should be only one result
            foreach($values as $acl_id => $acl) {
                if($operator == '+') {
                    $acl['rights'] |= $rights;
                }
                else if($operator == '-') {
                    $acl['rights'] &= ~$rights;
                }
                if(!$acl['rights']) {
                    // remove ACL if empty (no right granted)
                    $orm->remove('core\Permission', $acl_id, true);
                }
                else {
                    $orm->write('core\Permission', $acl_id, ['rights' => $acl['rights']]);
                }
            }
            $rights = $acl['rights'];
        }
        else if($operator == '+') {
            $orm->create('core\Permission', ['class_name' => $object_class, 'user_id' => $user_id, 'rights' => $rights]);
        }
        // update internal cache
        if(isset($this->permissionsTable[$user_id])) unset($this->permissionsTable[$user_id]);
    }

    public function grantUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($users_ids)) $users_ids = (array) $users_ids;
        foreach($users_ids as $user_id) {
            $this->changeUserRights('+', $operation, $user_id, $object_class);
        }
    }

    public function revokeUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($users_ids)) $users_ids = (array) $users_ids;

        foreach($users_ids as $user_id) {
            $this->changeUserRights('-', $operation, $user_id, $object_class);
        }
    }

    public function grant($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();
        $this->grantUsers($user_id, $operation, $object_class, $object_fields, $object_ids);
    }

    public function revoke($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();
        $this->revokeUsers($user_id, $operation, $object_class, $object_fields, $object_ids);
    }

    public function isAllowed($operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        // grant all rights when using CLI
        if(php_sapi_name() === 'cli' || defined('STDIN')) return true;

        // check operation against default rights
		if(DEFAULT_RIGHTS & $operation) return true;

        // retrieve current user identifier
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();

		// force cast ids to array (passing a single id is accepted)
		if(!is_array($object_ids)) {
			$object_ids = (array) $object_ids;
		}

        // build final user rights
        $user_rights = $this->getUserRights($user_id, $object_class, $object_fields, $object_ids);

		// do we need to perform additional testing ?
		if($operation == QN_R_READ) {
			// user always has READ right on its own object
			if($object_class == 'core\User' && count($object_ids) == 1 && $user_id == $object_ids[0]) {
				$user_rights |= QN_R_READ;
			}
			else {
				// if all fields 'creator' of targeted objects are equal to $user_id, then add R_READ to user_rights
				$orm = $this->container->get('orm');
				$objects = $orm->read($object_class, $object_ids, ['creator']);
				$user_ids = [];
				foreach($objects as $oid => $odata) {
					$user_ids[$odata['creator']] = true;
				}
				// user always has READ right on objects he created
				if(count($user_ids) == 1 && array_keys($user_ids)[0] == $user_id) {
					$user_rights |= QN_R_READ;
				}
			}
		}

		return (bool) ($user_rights & $operation);
    }
}