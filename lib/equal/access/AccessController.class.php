<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\access;

use equal\orm\ObjectManager;
use equal\orm\Model;
use equal\organic\Service;
use equal\services\Container;

class AccessController extends Service {

    private $permissionsTable;

    private $groupsTable;

    private $usersTable;

    private $default_rights;

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance).
     */
    protected function __construct(Container $container) {
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

            $orm = $this->container->get('orm');
            $values = $orm->read('core\User', $user_id, ['groups_ids']);
            if($values > 0 && isset($values[$user_id])) {
                $this->groupsTable[$user_id] = array_unique(array_merge($this->groupsTable[$user_id], $values[$user_id]['groups_ids']));
            }
        }
        $groups_ids = $this->groupsTable[$user_id];
        return $groups_ids;
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
     * Retrieve the permissions (ACL) that apply for a given user on a target entity.
     *
     * @param   int     $user_id         Identifier of the user for which the permissions are requested.
     * @param   string  $object_class    The class for which the rights have to be fetched. This param accepts wildcard notation ('*').
     * @param   int[]   $object_ids      This param is optional. If provided, the method will return the minimum set of permissions that are granted for user on all objects of the collection.
     *
     * @return  int     Returns a binary mask made of the resulting rights of the given user on the target entity.
     *
     */
    protected function getUserRights($user_id, $object_class, $object_ids=[]) {
        // no matter the ACLs, users are always granted the default permissions
        $user_rights = $this->default_rights;

        if(count($object_ids)) {
            // get all user's groups
            $groups_ids = $this->getUserGroups($user_id);

            // build domain
            $domain = [];
            $domain[] = [ ['class_name', '=', $object_class], ['user_id', '=', $user_id], ['object_id', 'in', $object_ids] ];
            $domain[] = [ ['class_name', '=', '*'], ['user_id', '=', $user_id], ['object_id', 'in', $object_ids] ];
            $domain[] = [ ['class_name', '=', $object_class], ['user_id', '=', $user_id], ['object_id', 'is', null] ];
            $domain[] = [ ['class_name', '=', '*'], ['user_id', '=', $user_id], ['object_id', 'is', null] ];
            if(count($groups_ids)) {
                $domain[] = [ ['class_name', '=', $object_class], ['group_id', 'in', $groups_ids], ['object_id', 'in', $object_ids] ];
                $domain[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids], ['object_id', 'in', $object_ids] ];
                $domain[] = [ ['class_name', '=', $object_class], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
                $domain[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
            }
            $orm = $this->container->get('orm');
            // fetch all ACLs variants and build a map by object_id
            $acl_ids = $orm->search('core\Permission', $domain);
            if(count($acl_ids)) {
                $map_objects_permissions = [];
                // get the user permissions
                $values = $orm->read('core\Permission', $acl_ids, ['object_id', 'rights']);
                foreach($values as $acl_id => $acl) {
                    if(!isset($map_objects_permissions[$acl['object_id']])) {
                        $map_objects_permissions[$acl['object_id']] = $acl['rights'];
                    }
                    else {
                        $map_objects_permissions[$acl['object_id']] |= $acl['rights'];
                    }
                }
                $user_rights = QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE;
                foreach($map_objects_permissions as $object_id => $rights) {
                    $user_rights &= $rights;
                }
            }
            // user has at least the default rights
            $user_rights = max($user_rights, $this->default_rights);
            // root user always has full rights
            if($user_id == QN_ROOT_USER_ID) {
                $user_rights = QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE;
            }
        }
        else {

            // if we did already compute user rights then provide the previous result
            if(isset($this->permissionsTable[$user_id][$object_class])) {
                $user_rights = $this->permissionsTable[$user_id][$object_class];
            }
            else {
                // root user always has full rights
                if($user_id == QN_ROOT_USER_ID) {
                    $user_rights = QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE;
                }
                else {

                    // grant READ on system entities ('core' package)
                    if(ObjectManager::getObjectPackage(ObjectManager::getObjectRootClass($object_class)) == 'core') {
                        $user_rights |= QN_R_READ;
                    }

                    // fetch all ACLs variants
                    $acls = $this->getUserAcls($user_id, $object_class);

                    if($acls > 0 && count($acls)) {
                        // get the user permissions
                        foreach($acls as $aid => $acl) {
                            $user_rights |= $acl['rights'];
                        }
                    }

                    // store resulting permissions in cache
                    if(!isset($this->permissionsTable[$user_id])) {
                        $this->permissionsTable[$user_id] = [];
                    }
                }
                $this->permissionsTable[$user_id][$object_class] = $user_rights;
            }
        }

        return $user_rights;
    }


    /**
     * Retrieve ACLs for a given user on whole class or parents classes, but not individual objects.
     *
     * @param   $user_id        integer  Identifier of targeted user.
     * @param   $object_class   string   Name of the class for which the ACL are requested.
     */
    protected function getUserAcls($user_id, $object_class) {
        $result = [];

        $orm = $this->container->get('orm');

        // get user groups
        $groups_ids = $this->getUserGroups($user_id);

        // build array of ACL variants
        if($object_class == '*') {
            $domain = [
                [ ['class_name', '=', '*'], ['user_id', '=', $user_id], ['object_id', 'is', null] ]
            ];
            if(count($groups_ids)) {
                $domain[] = [ ['class_name', '=', '*'], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
            }
        }
        else {
            $domain = [];
            // add parent classes to the domain (when a right is granted on a class, it is also granted on children classes)
            $classes = $orm->getObjectParentsClasses($object_class);
            $classes[] = $object_class;

            foreach($classes as $class) {
                // extract package parts from class name (for wildcard notation)
                $package_parts = explode('\\', $class);

                // add disjunctions
                for($i = 0, $n = count($package_parts), $has_groups = count($groups_ids); $i <= $n; ++$i) {
                    $level = array_slice($package_parts, 0, $i);
                    $level_wildcard = implode('\\', $level);
                    if($i < $n) {
                        $level_wildcard .= (strlen($level_wildcard))?'\*':'*';
                    }

                    $domain[] = [ ['class_name', '=', $level_wildcard], ['user_id', '=', $user_id], ['object_id', 'is', null] ];

                    if($has_groups) {
                        $domain[] = [ ['class_name', '=', $level_wildcard], ['group_id', 'in', $groups_ids], ['object_id', 'is', null] ];
                    }
                }
            }
        }

        // fetch all ACLs variants
        $acl_ids = $orm->search('core\Permission', $domain);
        if(count($acl_ids)) {
            // get the user ACL
            $result = $orm->read('core\Permission', $acl_ids, ['rights']);
        }

        return $result;
    }


    /**
     * Arbitrary change the rights granted to a user or a group.
     *
     * @param   $identity       string   'user' or 'group'
     * @param   $operator       string   '+' or '-'
     * @param   $rights         integer  binary mask of the rights to apply
     * @param   $identity_id    integer  identifier of targeted user or group
     * @param   $object_class   string   name of the class on which rights apply to : wildcards are allowed (ex. '*' or 'core\*')
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
     * @param $groups_ids   array   List of groups identifiers the current user must be added to.
     */
    public function addGroups($groups_ids, $user_id=null) {
        $groups_ids = (array) $groups_ids;
        if(is_null($user_id)) {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }
        $orm = $this->container->get('orm');
        return $orm->write('core\Group', $groups_ids, ['users_ids' => ["+{$user_id}"] ]);
    }

    /**
     * Alias of addGroups.
     *
     * @param   $group_id   integer Identifier of the group the user must be added to.
     */
    public function addGroup($group_id, $user_id=null) {
        return $this->addGroups($group_id, $user_id);
    }

    /**
     * Check if a user is member of a given group. I no user is provided, defaults to current user.
     *
     * @param $group    integer|string    The group name or identifier.
     */
    public function hasGroup($group, $user_id=null) {
        $orm = $this->container->get('orm');
        if(is_null($user_id)) {
            $auth = $this->container->get('auth');
            $user_id = $auth->userId();
        }
        if( !is_numeric($group) ) {
            // fetch all ACLs variants
            $groups_ids = $orm->search('core\Group', ['name', '=', $group]);
            if($groups_ids < 0 || count($groups_ids) <= 0) {
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
     * @param integer       $operation        Binary mask of the operations that are checked (can be built using constants : QN_R_CREATE, QN_R_READ, QN_R_DELETE, QN_R_WRITE, QN_R_MANAGE).
     * @param string        $object_class     Class selector indicating on which classes the check must be performed.
     * @param int[]         $objects_ids      (optional) List of objects identifiers (relating to $object_class) against which the check must be performed.
     */
    public function hasRight($user_id, $operation, $object_class='*', $objects_ids=[]) {
        // check operation against default rights
        if( ($this->default_rights & $operation) == $operation) {
            return true;
        }

        // force cast ids to array (passing a single id is accepted)
        $objects_ids = (array) $objects_ids;

        // permission query is for class and/or fields only (no specific objects)
        $user_rights = $this->getUserRights($user_id, $object_class, $objects_ids);

        return (($user_rights & $operation) == $operation);
    }

    /**
     *  Check if current user (retrieved using Auth service) has rights to perform a given operation.
     *
     *  This method is called by the Collection service, when performing CRUD.
     *  #todo - deprecate $object_fields
     *
     * @param integer       $operation        Identifier of the operation(s) that is/are checked (binary mask made of constants : QN_R_CREATE, QN_R_READ, QN_R_DELETE, QN_R_WRITE, QN_R_MANAGE).
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

    /**
     * @deprecated
     */
    /*
    public function rights($user_id, $object_class, $object_fields=[]) {
        // non-root users can only fetch their own rights
        $auth = $this->container->get('auth');
        $uid = $auth->userId();
        if($uid != QN_ROOT_USER_ID) {
            $user_id = $uid;
        }
        return $this->getUserRights($user_id, $object_class);
    }
    */

    /**
     * @deprecated
     *
     */
    /*
    public function groups($user_id) {
        // non-root user can only fetch their own groups
        $auth = $this->container->get('auth');
        $uid = $auth->userId();
        if($uid != QN_ROOT_USER_ID) {
            $user_id = $uid;
        }
        return $this->getUserGroups($user_id);
    }
    */

    /**
     *  Filter a list of objects and return only ids of objects on which current user has permission for given operation.
     *  This method is called by the Collection service, when performing Search
     *  @deprecated
     */
    // public function filter($operation, $object_class, $object_fields, $object_ids) {

    //     // grant all rights when using CLI
    //     if(php_sapi_name() === 'cli') return $object_ids;

    //     /** @var ObjectManager */
    //     $orm = $this->container->get('orm');

    //     // retrieve current user identifier
    //     $auth = $this->container->get('auth');
    //     $user_id = $auth->userId();

    //     // build final user rights
    //     $user_rights = $this->getUserRights($user_id, $object_class, $object_ids);

    //     // retrieve root class of filtered entity
    //     $root_class = ObjectManager::getObjectRootClass($object_class);

    //     if($root_class == 'core\Group') {
    //         // members of a group have READ rights on it
    //         $groups_ids = $this->getUserGroups($user_id);
    //         if(count(array_diff($object_ids, $groups_ids)) <= 0) {
    //             $user_rights |= QN_R_READ;
    //         }
    //     }
    //     elseif($root_class == 'core\User') {
    //         if(count($object_ids) == 1 && $user_id == $object_ids[0]) {
    //             // user always has READ rights on its own object
    //             $user_rights |= QN_R_READ;
    //             // user is granted to update some fields on its own object
    //             $writeable_fields = ['password', 'firstname', 'lastname', 'language', 'locale'];
    //             // if, after removing special fields, there are only fields that user can update, then we grant the WRITE right
    //             if(count(array_diff($object_fields, array_merge(array_keys(Model::getSpecialColumns()), $writeable_fields))) == 0) {
    //                 $user_rights |= QN_R_WRITE;
    //             }
    //         }
    //     }
    //     elseif($operation == QN_R_READ) {
    //         // this is a special case of a generic feature (we should add this in the init data)
    //         // if all fields 'creator' of targeted objects are equal to $user_id, then add R_READ to user_rights
    //         $objects = $orm->read($object_class, $object_ids, ['creator']);
    //         $user_ids = [];
    //         foreach($objects as $oid => $odata) {
    //             $user_ids[$odata['creator']] = true;
    //         }
    //         // user always has READ right on objects he created
    //         if(count($user_ids) == 1 && array_keys($user_ids)[0] == $user_id) {
    //             $user_rights |= QN_R_READ;
    //         }
    //     }


    //     /*
    //     loop on objects_ids, for each object

    //     1) fetch regular ACL
    //     2) fetch ACL with a domain
    //     either user has operation granted with one of its ACL (or one of its groups)
    //     or user is granted operation on specific objects (matching ACL domain)

    //     validate id as soon as an ACL condition is met
    //     */

    //     return ((bool)($user_rights & $operation))?$object_ids:[];
    // }

}