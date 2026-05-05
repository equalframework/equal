# Authorization Overview

Authorization in eQual is a critical component of the framework, ensuring secure and controlled access to entities and their operations. This document provides an overview of the mechanisms and strategies available for managing access control.

---

## Access Control Strategies

eQual supports a variety of Access Control strategies to meet diverse security requirements:

- **ACL (Access Control Lists):** Assigns permissions to users or groups for specific resources.
- **ABAC (Attribute-Based Access Control):** Grants access based on attributes of the user, resource, or environment.
- **RBAC (Role-Based Access Control):** Manages permissions based on user roles.
- **PBAC (Policy-Based Access Control):** Uses policies to define access rules.

!!! info "Learn More"
    For a comparison of these strategies, see [Open Policy Agent's comparison](https://www.openpolicyagent.org/docs/latest/comparison-to-other-systems/).

---

## Users and Groups

### Users

Users are defined in the `core\User` class ([`packages/core/classes/User.class.php`](../../../../../../packages/core/classes/User.php.class)). Each user belongs to one or more groups, with the default group being "users" (see `DEFAULT_GROUP_ID` in [`eq.lib.php`](../../../../../../eq.lib.php)).

```php
public static function getColumns() {
    return [
        'firstname' => ['type' => 'string'],
        'lastname'  => ['type' => 'string'],
        'login'     => ['type' => 'string', 'label' => 'Username'],
        'password'  => ['type' => 'string', 'label' => 'Password'],
        'language'  => ['type' => 'string'],
        'groups_ids' => [
            'type' => 'many2many',
            'foreign_object' => 'core\Group',
            'foreign_field' => 'users_ids',
            'rel_table' => 'core_rel_group_user',
            'rel_foreign_key' => 'group_id',
            'rel_local_key' => 'user_id'
        ]
    ];
}
```

### Groups

Groups are defined in the `core\Group` class (`packages/core/classes/Group.class.php`). They manage user memberships and associated permissions.

```php
<?php
public static function getColumns() {
    return [
        'name' => ['type' => 'string'],
        'users_ids' => [
            'type' => 'many2many',
            'foreign_object' => 'core\User',
            'foreign_field' => 'groups_ids',
            'rel_table' => 'core_rel_group_user',
            'rel_foreign_key' => 'user_id',
            'rel_local_key' => 'group_id'
        ],
        'permissions_ids' => [
            'type' => 'one2many',
            'foreign_object' => 'core\Permission',
            'foreign_field' => 'group_id'
        ]
    ];
}
```
Groups provide a way to manage permissions collectively, simplifying access control for large numbers of users.

---

### Policies

Policies are methods defined at the entity level, describing the authorization logic that applies to the related entity for specific actions.

Policies are represented as an associative array mapping policy names with policy handlers. Policy handlers return an associative array mapping error IDs with their descriptions. If the returned array is empty, the action is allowed.

```php
public static function getPolicies() {
    return [
        'updatable' => [
            'description' => "",
            'function' => 'policyUpdatable'
        ],
        'subscribable' => [ 
            'description' => '',
            'function' => 'policySubscribable'
        ],
        'publishable' => [
            'description' => "Policy defining the rules for an object to be publishable.",
            'function' => 'policyPublishable'
        ]
    ];
}

public static function policySubscribable($self, $user_id) {
    $result = [];
    $user = User::id($user_id)->read(['points'])->first();
    foreach($self as $id => $object) {
        if (!$access->hasRole($user_id, 'viewer', self::getType(), $id)) {
            $result[$id] = ['missing_role' => "user doesn't have the viewer role"];
            continue;
        }
        if ($user['points'] < $object['points']) {
            $result[$id] = ['missing_points' => "user doesn't have enough points"];
            continue;
        }
        if (!$object['is_subscribable']) {
            $result[$id] = ['not_subscribable' => "object is not subscribable"];
            continue;
        }
    }
    return $result;
}
```

Policies can be defined to check if a given user can perform an operation (action or transition). The check can either be based on:

* The status or the value of some fields (ABAC)
* The roles of a specific user (RBAC)
* Indirect relationships (at user or object level)
* Or a combination of these.

The use of policies can be done directly by using the AccessController `isCompliant()` method, or indirectly by using entity actions or transitions (workflow).

---