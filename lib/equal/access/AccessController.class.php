<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\access;

use equal\organic\Service;
use equal\services\Container;

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
        return ['DEFAULT_RIGHTS', 'ROOT_GROUP_ID', 'DEFAULT_GROUP_ID', 'ROOT_USER_ID', 'GUEST_USER_ID'];
    }

    protected function getUserGroups($user_id) {
		$groups_ids = [];
		if(!isset($this->groupsTable[$user_id])) {
            // all users are members of default group (including unidentified users)
            $this->groupsTable[$user_id] = [(string) DEFAULT_GROUP_ID];

            $orm = $this->container->get('orm');
            $values = $orm->read('core\User', $user_id, ['groups_ids']);
			if($values > 0 && isset($values[$user_id])) {
				$this->groupsTable[$user_id] = array_unique(array_merge($this->groupsTable[$user_id], $values[$user_id]['groups_ids']));
            }
        }
        $groups_ids = $this->groupsTable[$user_id];
        return $groups_ids;
    }

    protected function getGroupUsers($group_id) {
		$users_ids = [];
		if(!isset($this->usersTable[$group_id])) {
            $orm = $this->container->get('orm');
            $values = $orm->read('core\Group', $group_id, ['users_ids']);
			if($values > 0 && isset($values[$group_id])) {
				$this->usersTable[$group_id] = $values[$group_id]['users_ids'];
            }
            else {
                $this->usersTable[$group_id] = [];
            }
        }
        $users_ids = $this->usersTable[$group_id];
        return $users_ids;
    }

    /**
     * Retrieve the permissions that apply for a given user on a target entity.
     *
     * @param   string  $object_class    The class for which the rights have to be fetched accepts wildcard notation (use of '*').
     * @return  integer Returns a binary mask made of the resulting rights of the given user on the target entity.
     *
     *  #todo : Add support for domains
     *  restrict granting to a given class based on a series of conditions, with support for specific ids
     *  example :
     *  // limit objects visibility to the users who created them
     *  ['object.creator', '= ', 'user.id']
     *  for a given object, check access by successive SQL queries
     *  => performed during filter
     */
    protected function getUserRights($user_id, $object_class, $object_fields=[], $object_ids=[]) {
		// all users are at least granted the default permissions
		$user_rights = DEFAULT_RIGHTS;

        // if we did already compute user rights then we're done!
		if(isset($this->permissionsTable[$user_id][$object_class])) {
            $user_rights = $this->permissionsTable[$user_id][$object_class];
        }
		else {
            // root user always has full rights
            if($user_id == ROOT_USER_ID) {
                return QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE;
            }
            else {

                // get user groups
                $groups_ids = $this->getUserGroups($user_id);

                // build array of ACL variants
                if($object_class == '*') {
                    $domains = [
                        [ ['class_name', '=', '*'], ['user_id', '=', $user_id], ['domain', 'is', null] ]
                    ];
                    if(count($groups_ids)) {
                        $domains[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids], ['domain', 'is', null] ];
                    }
                }
                else {
					$orm = $this->container->get('orm');

                    $domains = [];

                    // extract package parts from class name (for wildcard notation)
                    $package_parts = explode('\\', $object_class);

                    // #todo - add parent classes (when a right is granted on a class, it is also granted on classes from which they inherit from)

                    // add disjunctions
                    for($i = 0, $n = count($package_parts), $is_groups = count($groups_ids); $i <= $n; ++$i) {
                        $level = array_slice($package_parts, 0, $i);
                        $level_wildcard = implode('\\', $level);
                        if($i < $n) {
                            $level_wildcard .= (strlen($level_wildcard))?'\*':'*';
                        }

                        $domains[] = [ ['class_name', '=', $level_wildcard], ['user_id', '=', $user_id], ['domain', 'is', null] ];

                        if($is_groups) {
                            $domains[] = [ ['class_name', '=', $level_wildcard], ['group_id', 'in', $groups_ids], ['domain', 'is', null] ];
                        }
                    }

                }

                // fetch all ACLs variants
                $acl_ids = $orm->search('core\Permission', $domains);
                if(count($acl_ids)) {
                    // get the user permissions
                    $values = $orm->read('core\Permission', $acl_ids, ['rights']);
                    foreach($values as $acl_id => $row) {
                        $user_rights |= $row['rights'];
                    }
                }

                if(!isset($this->permissionsTable[$user_id])) $this->permissionsTable[$user_id] = array();
                // #todo - first element ([0]) of the class-related array could be used to store the user permissions for the whole class
                $this->permissionsTable[$user_id][$object_class] = $user_rights;
            }
		}

		return $user_rights;
	}


    protected function getUserAcls($user_id, $object_class) {
		$acls = [];

        // get user groups
        $groups_ids = $this->getUserGroups($user_id);

        // build array of ACL variants
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

            $domains = [];

            // extract package parts from class name (for wildcard notation)
            $package_parts = explode('\\', $object_class);

            // add disjunctions
            for($i = 0, $n = count($package_parts), $is_groups = count($groups_ids); $i <= $n; ++$i) {
                $level = array_slice($package_parts, 0, $i);
                $level_wildcard = implode('\\', $level);
                if($i < $n) {
                    $level_wildcard .= (strlen($level_wildcard))?'\*':'*';
                }

                $domains[] = [ ['class_name', '=', $level_wildcard], ['user_id', '=', $user_id] ];

                if($is_groups) {
                    $domains[] = [ ['class_name', '=', $level_wildcard], ['group_id', 'in', $groups_ids] ];
                }
            }

        }

        // fetch all ACLs variants
        $acl_ids = $orm->search('core\Permission', $domains);
        if(count($acl_ids)) {
            // get the user permissions
            $acls = $orm->read('core\Permission', $acl_ids, ['rights']);
        }

		return $acls;
	}


    /**
     *
     * @param   $identity       string   'user' or 'group'
     * @param   $operator       string   '+' or '-'
     * @param   $rights         integer  binary mask of the rights to apply
     * @param   $identity_id    integer  identifier of targeted user or group
     * @param   $object_class   string   name of the class on which rights apply to : wildcards are allowed (ex. '*' or 'core\*')
     *
     */
    private function changeRights($identity, $operator, $rights, $identity_id, $object_class) {
        // all users belong to the default group
        $orm = $this->container->get('orm');
        // search for an ACL describing this specific user
        $acl_ids = $orm->search('core\Permission', [ ['class_name', '=', $object_class], [$identity.'_id', '=', $identity_id] ]);
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
                    // update ACL with new permissions
                    $orm->write('core\Permission', $acl_id, ['rights' => $acl['rights']]);
                }
            }
            $rights = $acl['rights'];
        }
        else if($operator == '+') {
            $orm->create('core\Permission', ['class_name' => $object_class, $identity.'_id' => $identity_id, 'rights' => $rights]);
        }
        // update internal cache
        if($identity == 'user' && isset($this->permissionsTable[$identity_id])) {
            unset($this->permissionsTable[$identity_id]);
        }
    }

    public function grantGroups($groups_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($groups_ids)) $groups_ids = (array) $groups_ids;
        foreach($groups_ids as $group_id) {
            $this->changeRights('group', '+', $operation, $group_id, $object_class);
        }
    }

    public function grantUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($users_ids)) $users_ids = (array) $users_ids;
        foreach($users_ids as $user_id) {
            $this->changeRights('user', '+', $operation, $user_id, $object_class);
        }
    }

    public function revokeUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($users_ids)) $users_ids = (array) $users_ids;

        foreach($users_ids as $user_id) {
            $this->changeRights('user', '-', $operation, $user_id, $object_class);
        }
    }

    public function revokeGroups($groups_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        if(!is_array($groups_ids)) $groups_ids = (array) $groups_ids;
        foreach($groups_ids as $group_id) {
            $this->changeRights('group', '-', $operation, $group_id, $object_class);
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

    /**
     * Add current user to a list of groups.
     *
     * @param $groups_ids   array   List of groups identifiers the current user must be added to.
     */
    public function addGroups($groups_ids) {
        $groups_ids = (array) $groups_ids;
        $auth = $this->container->get('auth');
        $orm = $this->container->get('orm');
        $user_id = $auth->userId();
        return $orm->write('core\Group', $groups_ids, ['users_ids' => ["+{$user_id}"] ]);
    }

    /**
     * Alias of addGroups.
     *
     * @param   $group_id   integer Identifier of the group the user must be added to.
     */
    public function addGroup($group_id) {
        return $this->addGroups($group_id);
    }

    /**
     * Check if current user is member of a given group.
     *
     * @param $group    integer|string    The group name or identifier.
     */
    public function hasGroup($group) {
        $orm = $this->container->get('orm');
        $auth = $this->container->get('auth');
        if( !is_numeric($group) ) {
            // fetch all ACLs variants
            $groups_ids = $orm->search('core\Group', ['name', '=', $group]);
            if($groups_ids < 0 || count($groups_ids) <= 0) {
                return false;
            }
            $group = $groups_ids[0];
        }
        $user_id = $auth->userId();
        $groups_ids = $this->getUserGroups($user_id);
        return in_array($group, $groups_ids);
    }

    /**
     *  Check if a current user (retrieved using Auth service) has rights to perform a given operation.
     *
     *  This method is called by the Collection service, when performing CRUD
     *  Qinoa's Access Controller is trivial and only check for rights at class level.
     *  For a more complex behaviour, this class can be replaced by a custom Auth service.
     *
     * @param integer       $operation        Identifier of the operation(s) that is/are checked (binary mask made of constants : QN_R_CREATE, QN_R_READ, QN_R_DELETE, QN_R_WRITE, QN_R_MANAGE).
     * @param string        $object_class     Class selector indicating on which classes the check must be performed.
     * @param array<string> $object_fields    (optional) List of fields name on which the operation must be granted.
     * @param array<int>    $object_ids       (optional) List of objects identifiers (relating to $object_class) against which the check must be performed.
     */
    public function isAllowed($operation, $object_class='*', $object_fields=[], $object_ids=[]) {

        // grant all rights when using CLI
        if(php_sapi_name() === 'cli') return true;

        // check operation against default rights
		if(DEFAULT_RIGHTS & $operation) return true;

        $allowed = false;

        // retrieve current user identifier
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();

		// force cast ids to array (passing a single id is accepted)
		if(!is_array($object_ids)) {
			$object_ids = (array) $object_ids;
		}

        //  permission query is for class and/or fields only (no specific objects)
        if(!count($object_ids)) {
            $user_rights = $this->getUserRights($user_id, $object_class, $object_fields);
            $allowed = (bool) ($user_rights & $operation);
        }
        // we have to check several objects
        else {
            // remove duplicates, if any
            $object_ids = array_unique($object_ids);
            // keep only ids of object for which user has required privilege
            $filtered_ids = $this->filter($operation, $object_class, $object_fields, $object_ids);
            // compute difference
            $diff = array_diff($object_ids, $filtered_ids);
            // if equal then operation is granted
            $allowed = !((bool) count($diff));
        }

        return $allowed;
    }

    public function rights($user_id, $object_class, $object_fields=[]) {
        // non root user can only fetch their own rights
        if(php_sapi_name() != 'cli') {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }
        return $this->getUserRights($user_id, $object_class, $object_fields);
    }

    public function groups($user_id) {
        // non root user can only fetch their own groups
        if(php_sapi_name() != 'cli') {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }

        return $this->getUserGroups($user_id);
    }

    /**
     *  Filter a list of objects and return only ids of objects on which current user has permission for given operation.
     *  This method is called by the Collection service, when performing Search
     */
    public function filter($operation, $object_class, $object_fields, $object_ids) {

        // grant all rights when using CLI
        if(php_sapi_name() === 'cli') return $object_ids;

        $orm = $this->container->get('orm');

        // retrieve current user identifier
        $auth = $this->container->get('auth');
        $user_id = $auth->userId();

        // build final user rights
        $user_rights = $this->getUserRights($user_id, $object_class, $object_fields, $object_ids);

        // user always has READ rights on its own object
        if($orm::getObjectRootClass($object_class) == 'core\User' && count($object_ids) == 1 && $user_id == $object_ids[0]) {
            $user_rights |= QN_R_READ;
        }
		else if($operation == QN_R_READ) {
            // this is a special case of a generic feature (we should add this in the init data)

            // if all fields 'creator' of targeted objects are equal to $user_id, then add R_READ to user_rights

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


        /*
        loop on objects_ids, for each object

        1) fetch regular ACL
        2) fetch ACL with a domain
        either user has operation granted with one of its ACL (or one of its groups)
        or user is granted operation on specific objects (matching ACL domain)

        validate id as soon as an ACL condition is met
        */

		return ((bool)($user_rights & $operation))?$object_ids:[];
    }
}