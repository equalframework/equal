<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\access;

use core\Assignment;
use core\Group;
use core\Permission;
use core\security\SecurityPolicy;
use core\security\SecurityPolicyRule;
use core\security\SecurityPolicyRuleValue;
use core\setting\Setting;
use equal\orm\ObjectManager;
use equal\organic\Service;
use equal\services\Container;

class AccessController extends Service {

    private $is_request_compliant;

    private $complying_policy_id;

    private $permissionsTable;

    private $groupsTable;

    private $usersTable;

    private $default_rights;

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance).
     */
    protected function __construct(Container $container) {
        $this->is_request_compliant = false;
        $this->complying_policy_id = 0;
        $this->permissionsTable = array();
        $this->groupsTable = array();
        $this->usersTable = array();
        $this->default_rights = (defined('DEFAULT_RIGHTS'))?constant('DEFAULT_RIGHTS'):0;
    }

    public static function constants() {
        return ['QN_ROOT_GROUP_ID', 'QN_DEFAULT_GROUP_ID', 'QN_ROOT_USER_ID', 'QN_GUEST_USER_ID'];
    }

    public function getUserGroups($user_id) {
        $groups_ids = [];
        if(!isset($this->groupsTable[$user_id])) {
            // all users are members of default group (including unidentified users)
            $this->groupsTable[$user_id] = [(string) QN_DEFAULT_GROUP_ID];
            /** @var \equal\orm\ObjectManager */
            $orm = $this->container->get('orm');
            $values = $orm->read('core\User', $user_id, ['groups_ids']);
            if($values > 0 && isset($values[$user_id])) {
                $this->groupsTable[$user_id] = array_unique(array_merge($this->groupsTable[$user_id], $values[$user_id]['groups_ids']));
            }
        }
        $groups_ids = $this->groupsTable[$user_id];
        return $groups_ids;
    }

    private function getRolesImplications($object_class) {
        $map_roles = [];

        $roles = $object_class::getRoles();

        $getImpliedRoles = function($role) use (&$getImpliedRoles, &$roles, &$map_roles) {
            if(isset($map_roles[$role])) {
                return $map_roles[$role];
            }

            $implied_roles = [];
            foreach($roles as $key => $value) {
                if(isset($value['implied_by']) && in_array($role, (array) $value['implied_by'])) {
                    $implied_roles[] = $key;
                    $implied_roles = array_merge($implied_roles, $getImpliedRoles($key));
                }
            }

            $map_roles[$role] = array_unique($implied_roles);
            return $map_roles[$role];
        };

        foreach(array_keys($roles) as $role) {
            $getImpliedRoles($role, $roles, $map_roles);
        }

        return $map_roles;
    }

    public function getUserRoles($user_id, $object_class, $objects_ids) {
        $roles = [];

        $def_roles = $object_class::getRoles();

        if(count($def_roles)) {
            $map_user_roles = [];
            $orm = $this->container->get('orm');
            $roles_implications = $this->getRolesImplications($object_class);
            $first = true;

            $getResultingRoles = function($role) use($roles_implications) {
                $map_roles = [$role => true];
                if(isset($roles_implications[$role])) {
                    foreach((array) $roles_implications[$role] as $implied_role) {
                        $map_roles[$implied_role] = true;
                    }
                }
                return array_keys($map_roles);
            };

            foreach($objects_ids as $object_id) {
                $assignments_ids = $orm->search('core\Assignment', [ ['user_id', '=', $user_id], ['object_class', '=', $object_class], ['object_id', '=', $object_id] ]);
                $assignments = $orm->read('core\Assignment', (array) $assignments_ids, ['role']);
                if($assignments > 0 && count($assignments)) {
                    $map_assignment_roles = [];
                    foreach($assignments as $aid => $assignment) {
                        foreach($getResultingRoles($assignment['role']) as $resulting_role) {
                            $map_assignment_roles[$resulting_role] = true;
                        }
                    }
                    if($first) {
                        $map_user_roles = $map_assignment_roles;
                    }
                    else {
                        foreach($map_user_roles as $r => $v) {
                            if(!isset($map_assignment_roles[$r])) {
                                unset($map_user_roles[$r]);
                            }
                        }
                        if(!count($map_user_roles)) {
                            break;
                        }
                    }
                    $first = false;
                }
            }
            $roles = array_keys($map_user_roles);
        }

        return $roles;
    }

    public function getGroupUsers($group_id) {
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
     * Retrieve the permissions (ACL) that apply for a given user on a target entity and/or on a set of specific objects.
     * This method use the AccessController cache to provide previously requested Rights.
     *
     * @param   int     $user_id         Identifier of the user for which the permissions are requested.
     * @param   string  $object_class    The class for which the rights have to be fetched. This param accepts wildcard notation ('*').
     * @param   int[]   $object_ids      Optional list of objects identifiers. If provided, the method will return the minimum set of permissions that are granted for user on all objects of the collection.
     * @param   int     $operation       Optional operation (bit mask of rights) we're looking for. If provided, the method will stop searching for permissions as soon as this value is met. This argument is used only when objects_ids are provided.
     *
     * @return  int     Returns a bit mask made of the resulting rights of the given user on the target collection.
     *
     */
    public function getUserRights($user_id, $object_class, $object_ids=[], $operation=EQ_R_ALL) {
        // users are always granted the default permissions and root user always has full rights
        $user_rights = ($user_id == QN_ROOT_USER_ID)?EQ_R_ALL:$this->default_rights;

        // request for rights based on object_class and specific object_ids
        if(count($object_ids)) {
            if($user_rights < $operation) {
                $user_rights |= $this->getUserRightsOnClass($user_id, $object_class);
            }
            if($user_rights < $operation) {
                $user_rights |= $this->getUserRightsOnObjects($user_id, $object_class, $object_ids);
            }
            // grant user RW rights on its own object
            if(ObjectManager::getObjectRootClass($object_class) == 'core\User' && count($object_ids) == 1 && $object_ids[0] == $user_id) {
                $user_rights |= EQ_R_READ | EQ_R_WRITE;
            }
        }
        // request for rights based on object_class only
        else {
            // if we did already compute user rights then provide the previous result
            if(isset($this->permissionsTable[$user_id][$object_class])) {
                $user_rights = $this->permissionsTable[$user_id][$object_class];
            }
            else {
                // grant READ on system entities ('core' package)
                /*
                // #memo - this is done through Permissions object to allow fine grained handling
                if(ObjectManager::getObjectPackage(ObjectManager::getObjectRootClass($object_class)) == 'core') {
                    $user_rights |= EQ_R_READ;
                }
                */
                if(strpos($object_class, '*') === false) {
                    $user_rights |= $this->getUserRightsOnClass($user_id, $object_class);
                }
                else {
                    $user_rights |= $this->getUserRightsOnWildcard($user_id, $object_class);
                }
                if(!isset($this->permissionsTable[$user_id])) {
                    $this->permissionsTable[$user_id] = [];
                }
                $this->permissionsTable[$user_id][$object_class] = $user_rights;
            }
        }

        return $user_rights;
    }

    /**
     * Retrieve ACLs for a given user on a specific class, or parents classes, or subsequent wildcards (but not individual objects).
     * In case none of the considered ACL exist, 0 is returned (default_rights is not considered).
     *
     * @param   int     $user_id        Identifier of targeted user.
     * @param   string  $object_class   Name of the class for which the ACL are requested.
     * @param   int     $operation      Optional operation (bit mask of rights) we're looking for. If provided, the method will stop searching for permissions as soon as this value is met.
     *
     */
    private function getUserRightsOnClass($user_id, $object_class, $operation=EQ_R_ALL) {
        $result = 0;

        // `object_class` should be an explicit class, if not provide default rights
        if(!class_exists($object_class)) {
            return 0;
        }
        /** @var \equal\orm\ObjectManager */
        $orm = $this->container->get('orm');

        // get all user's groups
        $groups_ids = $this->getUserGroups($user_id);

        // 1) lookup for exact match ACL

        // build domain
        $domain = [];
        $domain[] = [ ['class_name', '=', $object_class], ['user_id', '=', $user_id], ['object_id', 'is', null] ];
        if(count($groups_ids)) {
            $domain[] = [ ['class_name', '=', $object_class], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
        }
        $acl_ids = $orm->search('core\Permission', $domain);
        if($acl_ids > 0 && count($acl_ids)) {
            $acls = $orm->read('core\Permission', $acl_ids, ['rights']);
            foreach($acls as $acl_id => $acl) {
                $result |= $acl['rights'];
            }
        }

        // 2) lookup on wildcard with class namespace

        $parts = explode('\\', $object_class);
        array_pop($parts);
        array_push($parts, '*');
        $wildcard = implode('\\', $parts);
        if($result < $operation) {
            $result |= $this->getUserRightsOnWildcard($user_id, $wildcard);
        }

        // if no ACL found, lookup for ACLs set on parent class
        if($result == 0) {
            $parent_classes = ObjectManager::getObjectParentsClasses($object_class);
            if(count($parent_classes)) {
                $classes = [];
                $table_name = $orm->getObjectTableName($object_class);
                foreach($parent_classes as $class) {
                    if($orm->getObjectTableName($class) == $table_name) {
                        $classes[] = $class;
                    }
                }
                foreach($classes as $class) {
                    $result |= $this->getUserRightsOnClass($user_id, $class);
                    if($result >= $operation) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Retrieves exclusively the rights explicitly set on the given wildcard and subsequent wildcards (core\settings\* -> core\* -> *)
     * In case neither given wildcard nor subsequent wildcard match any ACL, 0 is returned (default_rights is not considered).
     *
     * @param   int     $user_id        Identifier of targeted user.
     * @param   string  $object_class   Name of the class for which the ACL are requested.
     *
     */
    private function getUserRightsOnWildcard($user_id, $wildcard) {
        $result = 0;

        // `object_class` should be an explicit class, if not provide default rights
        if(strpos($wildcard, '*') === false) {
            return $result;
        }

        $orm = $this->container->get('orm');

        // get all user's groups
        $groups_ids = $this->getUserGroups($user_id);

        // retrieve all derived wildcard with class namespace
        $wildcards = [];
        $parts = explode('\\', $wildcard);
        for($i = 0, $n = count($parts); $i < $n; ++$i) {
            $level = array_slice($parts, 0, $i);
            $level_wildcard = implode('\\', $level);
            if($i < $n) {
                $level_wildcard .= (strlen($level_wildcard))?'\*':'*';
            }
            $wildcards[] = $level_wildcard;
        }

        // build domain
        $domain = [];
        foreach($wildcards as $card) {
            $domain[] = [ ['class_name', '=', $card], ['user_id', '=', $user_id], ['object_id', 'is', null] ];
            if(count($groups_ids)) {
                $domain[] = [ ['class_name', '=', $card], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
            }
        }

        $acl_ids = $orm->search('core\Permission', $domain);
        if($acl_ids > 0 && count($acl_ids)) {
            $acls = $orm->read('core\Permission', $acl_ids, ['rights']);
            foreach($acls as $acl_id => $acl) {
                $result |= $acl['rights'];
            }
        }

        return $result;
    }

    /**
     * Retrieve the minimal rights set on the given collection of objects.
     * In case no specific right is defined for the collection 0 is returned (default_rights is not considered).
     *
     * @param   int     $user_id        Identifier of targeted user.
     * @param   string  $object_class   Name of the class for which the ACL are requested.
     *
     */
    private function getUserRightsOnObjects($user_id, $object_class, $objects_ids) {
        $result = 0;

        // `object_class` should be an explicit class, if not provide default rights
        if(!class_exists($object_class)) {
            return $result;
        }

        $orm = $this->container->get('orm');

        // get all user's groups
        $groups_ids = $this->getUserGroups($user_id);

        // retrieve applicable parent classes (object from classes sharing same objects ids)
        $classes = [$object_class];
        $parent_classes = ObjectManager::getObjectParentsClasses($object_class);

        $table_name = $orm->getObjectTableName($object_class);
        foreach($parent_classes as $class) {
            if($orm->getObjectTableName($class) == $table_name) {
                $classes[] = $class;
            }
        }

        // build domain
        $domain = [];

        foreach($classes as $class) {
            $domain[] = [ ['class_name', '=', $class], ['user_id', '=', $user_id], ['object_id', 'in', $objects_ids] ];
            if(count($groups_ids)) {
                $domain[] = [ ['class_name', '=', $class], ['group_id', 'in', $groups_ids], ['object_id', 'in', $objects_ids] ];
            }
        }

        $acl_ids = $orm->search('core\Permission', $domain);
        if($acl_ids > 0 && count($acl_ids)) {
            $acls = $orm->read('core\Permission', $acl_ids, ['object_id', 'rights']);
            $map_objects_rights = [];
            // step-1 - map acl by object id (on a same object, rights are added)
            foreach($acls as $acl_id => $acl) {
                if(!isset($map_objects_rights[$acl['object_id']])) {
                    $map_objects_rights[$acl['object_id']] = 0;
                }
                $map_objects_rights[$acl['object_id']] |= $acl['rights'];
            }
            // step-2 - between objects, rights are subtracted (result must be the minimal right granted)
            $resulting_rights = 0;
            foreach($map_objects_rights as $object_id => $rights) {
                if($rights == 0) {
                    $resulting_rights = 0;
                    break;
                }
                elseif($resulting_rights == 0) {
                    $resulting_rights = $rights;
                }
                else {
                    $resulting_rights &= $rights;
                }
            }
            $result |= $resulting_rights;
        }

        return $result;
    }

    /**
     * Arbitrary change the rights granted to a user or a group.
     *
     * @param   string  $identity       Type of identity on which the right must be changed: 'user' or 'group'.
     * @param   string  $operator       Operator to apply: '+' or '-'.
     * @param   integer $rights         Bit mask of the rights to apply.
     * @param   integer $identity_id    Identifier of targeted user or group.
     * @param   string  $object_class   Name of the class on which rights apply to : wildcards are allowed (ex. '*' or 'core\*').
     *
     */
    private function changeRights($identity, $operator, $rights, $identity_id, $object_class) {
        if(in_array($identity, ['user', 'group'])) {
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
                        $orm->remove(Permission::getType(), $acl_id, true);
                    }
                    else {
                        // update ACL with new permissions
                        $orm->update(Permission::getType(), $acl_id, ['rights' => $acl['rights']]);
                    }
                }
                $rights = $acl['rights'];
            }
            else if($operator == '+') {
                $orm->create(Permission::getType(), ['class_name' => $object_class, $identity.'_id' => $identity_id, 'rights' => $rights]);
            }
            // update internal cache
            if($identity == 'user' && isset($this->permissionsTable[$identity_id])) {
                unset($this->permissionsTable[$identity_id]);
            }
        }
    }

    public function grantGroups($groups_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        foreach((array) $groups_ids as $group_id) {
            $this->changeRights('group', '+', $operation, $group_id, $object_class);
        }
    }

    /**
     * Provide additional rights for a user on a given class of objects (wildcard supported).
     */
    public function grantUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        foreach((array) $users_ids as $user_id) {
            $this->changeRights('user', '+', $operation, $user_id, $object_class);
        }
    }

    /**
     * Withdraw some rights for a user on a given class of objects (wildcard supported).
     */
    public function revokeUsers($users_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        foreach((array) $users_ids as $user_id) {
            $this->changeRights('user', '-', $operation, $user_id, $object_class);
        }
    }

    public function revokeGroups($groups_ids, $operation, $object_class='*', $object_fields=[], $object_ids=[]) {
        foreach((array) $groups_ids as $group_id) {
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
     * @param int[] $groups_ids  List of groups identifiers the current user must be added to.
     */
    public function addGroups($groups_ids, $user_id=null) {
        $groups_ids = (array) $groups_ids;
        if(is_null($user_id)) {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }
        if(isset($this->permissionsTable[$user_id])) {
            unset($this->permissionsTable[$user_id]);
        }
        $orm = $this->container->get('orm');
        return $orm->update(Group::getType(), $groups_ids, ['users_ids' => ["+{$user_id}"] ]);
    }

    /**
     * Alias of addGroups.
     *
     * @param   int   $group_id   Identifier of the group the user must be added to.
     */
    public function addGroup($group_id, $user_id=null) {
        return $this->addGroups($group_id, $user_id);
    }

    /**
     * Check if a user is member of a given group. I no user is provided, defaults to current user.
     *
     * @param integer|string    $group    The group name or identifier.
     */
    public function hasGroup($group, $user_id=null) {
        $orm = $this->container->get('orm');
        if(is_null($user_id)) {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }
        if( !is_numeric($group) ) {
            // fetch all ACLs variants
            $groups_ids = $orm->search(Group::getType(), ['name', '=', $group]);
            if($groups_ids < 0 || empty($groups_ids)) {
                return false;
            }
            $group = $groups_ids[0];
        }
        $groups_ids = $this->getUserGroups($user_id);
        return in_array($group, $groups_ids);
    }

    /**
     *  Check if a given user (retrieved using Auth service) is explicitly granted the requested rights to perform a given operation.
     *
     * @param integer       $operation        Bit mask of the operations that are checked (can be built using constants : EQ_R_CREATE, EQ_R_READ, EQ_R_DELETE, EQ_R_WRITE, EQ_R_MANAGE).
     * @param string        $object_class     Class selector indicating on which classes the check must be performed.
     * @param int[]         $objects_ids      (optional) List of objects identifiers (relating to $object_class) against which the check must be performed.
     */
    public function hasRight($user_id, $operation, $object_class='*', $objects_ids=[]) {
        // force cast ids to array (passing a single id is accepted)
        $objects_ids = (array) $objects_ids;
        // retrieve most permissive right that use has on targeted entities/objects.
        $user_rights = $this->getUserRights($user_id, $object_class, $objects_ids, $operation);
        if(strpos($object_class, '*') === false) {
            // retrieve permissions from roles, if set for given class
            $user_roles = $this->getUserRoles($user_id, $object_class, $objects_ids);
            $user_rights |= $this->getRightsFromRoles($user_roles, $object_class);
        }
        // if all bits of operation are granted, then user has requested rights
        return (($user_rights & $operation) == $operation);
    }

    /**
     *  Check if current user (retrieved using Auth service) has rights to perform a given operation.
     *
     *  This method is called by the Collection service, when performing CRUD.
     *  #todo #confirm - deprecate $object_fields (instead rely on individual can...() checks)
     *
     * @param integer       $operation        Identifier of the operation(s) that is/are checked (bit mask made of constants : EQ_R_CREATE, EQ_R_READ, EQ_R_DELETE, EQ_R_WRITE, EQ_R_MANAGE).
     * @param string        $object_class     Class selector indicating on which classes the check must be performed.
     * @param string[]      $object_fields    (optional) List of fields name on which the operation must be granted.
     * @param int[]         $object_ids       (optional) List of objects identifiers (relating to $object_class) against which the check must be performed.
     */
    public function isAllowed($operation, $object_class='*', $object_fields=[], $object_ids=[]) {

        /** @var \equal\auth\AuthenticationManager */
        $auth = $this->container->get('auth');
        // retrieve current user identifier
        $user_id = $auth->userId();

        $has_right = $this->hasRight($user_id, $operation, $object_class, $object_ids);

        return $has_right;
    }

    /**
     * Retrieve granted rights based on an exhaustive list of roles.
     * Rights are retrieved based on the 'rights' property ('implied_by' directive is not handled here).
     */
    public function getRightsFromRoles(array $roles, string $object_class): int {
        $rights = 0;
        if(count($roles)) {
            $def_roles = $object_class::getRoles();
            foreach($roles as $role) {
                if(isset($def_roles[$role])) {
                    $rights |= $def_roles[$role]['rights'] ?? 0;
                }
            }
        }
        return $rights;
    }

    /**
     * Check if a given user is granted a role on a collection of objects.
     *
     * @param integer       $user_id          The identifier of the user for which the test is requested.
     * @param string        $role             The role for which assignment is being tested.
     * @param string        $object_class     Class on which the check must be performed.
     * @param int[]         $object_ids       List of objects identifiers (relating to $object_class) against which the check must be performed.
     */
    public function hasRole($user_id, $role, $object_class, $objects_ids=[]): bool {
        $result = true;

        if(!count($object_class::getRoles())) {
            $result = false;
        }
        else {
            /** @var \equal\orm\ObjectManager */
            $orm = $this->container->get('orm');

            $map_role_matches = [];
            $map_role_matches[$role] = true;

            $roles_implications = $this->getRolesImplications($object_class);
            foreach((array) $roles_implications as $r => $implied_roles) {
                if(in_array($role, $implied_roles)) {
                    $map_role_matches[$r] = true;
                }
            }

            foreach($objects_ids as $object_id) {
                // retrieve all assignments objects implying one or more roles of the list, given to user on given object_class, object_id
                $assignments_ids = $orm->search(Assignment::getType(), [ ['object_id', '=', $object_id], ['object_class', '=', $object_class], ['user_id', '=', $user_id], ['role', 'in', array_keys($map_role_matches)] ]);
                if($assignments_ids <= 0 || !count($assignments_ids)) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Check if a Collection is compliant with a given policy for a specific User.
     * This relates to the optional `getPolicies()` method that can be defined on Model classes.
     */
    public function isCompliant($user_id, $policy, $object_class, $object_ids) {
        $result = [];
        $policies = $object_class::getPolicies();

        if(!isset($policies[$policy]) || !isset($policies[$policy]['function'])) {
            $result['unknown_policy'] = "Policy {$policy} is not defined in class {$object_class}";
        }
        else {
            $called_class = $object_class;
            $called_method = $policies[$policy]['function'];

            $parts = explode('::', $called_method);
            $count = count($parts);

            if( $count < 1 || $count > 2 ) {
                $result['invalid_policy_method'] = "Method provided for Policy {$policy} has an non-supported format.";
            }
            else {
                if( $count == 2 ) {
                    $called_class = $parts[0];
                    $called_method = $parts[1];
                }
                if(!method_exists($called_class, $called_method)) {
                    $result['unknown_policy_method'] = "Method {$called_method} provided for Policy {$policy} is not defined in class {$called_class}.";
                }
                else {
                    trigger_error("ORM::calling {$called_class}::{$called_method}", QN_REPORT_DEBUG);
                    $c = $called_class::ids($object_ids);
                    $res = $called_class::$called_method($c, $user_id);
                    if(count($res)) {
                        $result['broken_policy'] = "Collection does not comply with Policy `{$policy}`";
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Check if a given action can be performed on a Collection, according to the policies defined at the entity level for that action, if any.
     * This relates to the optional `getActions()` method that can be defined on Model classes.
     *
     */
    public function canPerform($user_id, $action, $object_class, $object_ids) {
        $result = [];
        $actions = $object_class::getActions();
        if(isset($actions[$action]) && isset($actions[$action]['policies'])) {
            foreach($actions[$action]['policies'] as $policy) {
                $res = $this->isCompliant($user_id, $policy, $object_class, $object_ids);
                if(count($res)) {
                    $result[$policy] = $res;
                }
            }
        }
        return $result;
    }

    public function getComplyingPolicyId() {
        return $this->complying_policy_id;
    }

    public function isRequestCompliant($user_id, $ip_address) {
        // if compliance has already been evaluated to true, do not re-run the process
        if($this->is_request_compliant) {
            return true;
        }
        $result = true;
        $time = time();

        // fetch security policies: request must be compliant with at least one policy.
        /** @var \equal\orm\ObjectManager */
        $orm = $this->container->get('orm');
        $security_policies_ids = $orm->search(SecurityPolicy::getType(), [['is_active', '=', true]]);
        if($security_policies_ids > 0 && count($security_policies_ids)) {
            $result = false;
            $policies = $orm->read(SecurityPolicy::getType(), $security_policies_ids, ['id', 'policy_rules_ids']);

            foreach($policies as $policy) {
                $is_compliant = true;
                // request must comply with all rules of the policy to comply with the latter
                $rules = $orm->read(SecurityPolicyRule::getType(), $policy['policy_rules_ids'], ['user_id', 'policy_rule_type', 'rule_values_ids']);
                foreach($rules as $rule) {
                    $is_match = false;
                    $values = $orm->read(SecurityPolicyRuleValue::getType(), $rule['rule_values_ids'], ['value']);
                    if($values < 0 || empty($values)) {
                        // ignore empty rules
                        continue;
                    }
                    // request must comply with at least one value of a rule to comply with the latter
                    foreach($values as $value) {
                        switch($rule['policy_rule_type']) {
                            case 'ip_address':
                                $is_match = $this->validateIpAddress($ip_address, $value['value']);
                                break;
                            case 'time_range':
                                $is_match = $this->validateTimeRange($time, $value['value']);
                                break;
                            case 'user_group':
                                $is_match = $this->validateUserGroup($user_id, $value['value']);
                                break;
                        }
                        // request match with one of the value of the rule
                        if($is_match) {
                            break;
                        }
                    }
                    // none of rule values matches with the request
                    if(!$is_match) {
                        // one rule is not met : the request does not comply with the policy
                        $is_compliant = false;
                        break;
                    }
                }
                // request is compliant, stop testing other policies
                if($is_compliant) {
                    $result = true;
                    $this->complying_policy_id = $policy['id'];
                    break;
                }
            }
        }
        $this->is_request_compliant = $result;
        return $result;
    }

    private function validateUserGroup($user_id, $group) {
        return $this->hasGroup($group, $user_id);
    }

    /**
     * tests: 192.168.1.123, 192.168.1.0/24, 192.168.*.*
     */
    private function validateIpAddress($ip, $pattern) {
        if(strpos($pattern, '*') !== false) {
            $pattern = str_replace(['.', '*'], ['\.', '[0-9]+'], $pattern);
            if(preg_match('/^' . $pattern . '$/', $ip)) {
                return true;
            }
        }
        elseif(strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            if(ip2long($ip) >> (32 - $mask) == ip2long($subnet) >> (32 - $mask)) {
                return true;
            }
        }
        elseif($ip === $pattern) {
            return true;
        }
        return false;
    }

    /**
     * var_dump(validate_time_range(1719925622, 'mon@09:00-wed@17:00')); // true
     * var_dump(validate_time_range(1719925622, 'tue@09:00-tue@17:00')); // true
     * var_dump(validate_time_range(1719925622, 'mon@09:00-fri@17:00')); // true
     * var_dump(validate_time_range(1719925622, 'thi@09:00-fri@17:00')); // false
     * var_dump(validate_time_range(1719925622, 'tue@09:00-tue@11:00')); // false
     * var_dump(validate_time_range(1719925622, 'mon@09:00-mon@11:00')); // false
     * var_dump(validate_time_range(1719925622, 'tue@13:00-tue@14:00')); // true
     */
    private function validateTimeRange($time, $pattern) {
        list($hours, $minutes) = explode(':', date('H:i', $time));
        $time_of_day = ($hours * 3600) + ($minutes * 60);

        // retrieve timezone offset between local timezone and UTC (times in settings use local time)
        $tz = new \DateTimeZone(Setting::get_value('core', 'locale', 'time_zone', 'Europe/Brussels'));
        // apply timezone offset, in seconds to apply, depending on the date of the given date (based on received timestamp)
        $time_of_day += $tz->getOffset(new \DateTime('@'.$time));

        $map_days = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

        $day_of_week = strtolower(date('D', $time));
        if(!isset($map_days[$day_of_week])) {
            return false;
        }
        $day_of_week = $map_days[$day_of_week];

        list($start, $end) = explode('-', $pattern);
        list($day_start, $time_start) = explode('@', $start);
        list($day_end, $time_end) = explode('@', $end);

        $day_start = strtolower(substr($day_start, 0, 3));
        $day_end = strtolower(substr($day_end, 0, 3));

        if (!isset($map_days[$day_start]) || !isset($map_days[$day_end])) {
            return false;
        }

        $day_start = $map_days[$day_start];
        $day_end = $map_days[$day_end];

        list($hours, $minutes) = explode(':', $time_start);
        $time_start_seconds = ($hours * 3600) + ($minutes * 60);

        list($hours, $minutes) = explode(':', $time_end);
        $time_end_seconds = ($hours * 3600) + ($minutes * 60);

        if ($day_start < $day_end || ($day_start == $day_end && $time_start_seconds <= $time_end_seconds)) {
            return ($day_of_week > $day_start || ($day_of_week == $day_start && $time_of_day >= $time_start_seconds)) &&
                ($day_of_week < $day_end || ($day_of_week == $day_end && $time_of_day <= $time_end_seconds));
        }
        else {
            return ($day_of_week > $day_start || ($day_of_week == $day_start && $time_of_day >= $time_start_seconds)) ||
                ($day_of_week < $day_end || ($day_of_week == $day_end && $time_of_day <= $time_end_seconds));
        }
        return false;
    }

}
