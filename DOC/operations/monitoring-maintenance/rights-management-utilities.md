## Rights Management Utilities

To determine Groups and Users permissions, we use a list of rights : `create`, `read`, `update`, `delete`, `manage`.

They are also defined inside the `eq.lib.php`file with a value attached :

```php
<?php
/**
 * Users & Groups permissions masks (value attached to the mask) 
 * #memo - we use powers of 2 for permission mask so that the addition of 
 * numbers never leads to a colliding values
 */
    define('EQ_R_CREATE',    1);
    define('EQ_R_READ',      2);
    define('EQ_R_WRITE',     4);
    define('EQ_R_DELETE',    8);
    define('EQ_R_MANAGE',   16);
```

**Use** :

Those values are used, for example, inside `Permission.class.php` on the `rights` field, to determine which rights from the list are selected. 

```php
<?php
'rights' => [
    'type' 	            => 'integer',
    'onupdate'          => 'onupdateRights',
    'description'       => "Rights binary mask (1: CREATE, 2: READ, 4: WRITE,
    8 DELETE, 16: MANAGE)"
],
```

Those permissions are used as properties by `User.class.php` & `Group.class.php` to determine the rights available.

### Users

#### `user_grant`

| **PATH**        | `core\actions\user\grant.php`                                                              |
| --------------- | ------------------------------------------------------------------------------------------ |
| **URL**         | `?do=user_grant&right=create&user=cedric@equal.run`                                        |
| **CLI**         | `$ ./equal.run --do=user_grant --right=create --user=cedric@equal.run --entity=core\\Task` |
| **DESCRIPTION** | Grant additional privilege to given user.                                                  |

> Only one right can be granted at a time to one user over one entity.

#### `user_revoke`

| **PATH**        | `core\actions\user\revoke.php`                                                              |
| --------------- | ------------------------------------------------------------------------------------------- |
| **URL**         | `?do=user_revoke&right=create&user=cedric@equal.run&entity=core\Task`                       |
| **CLI**         | `$ ./equal.run --do=user_revoke --right=create --user=cedric@equal.run --entity=core\\Task` |
| **DESCRIPTION** | Revoke privilege from a given user.                                                         |

> Only one right can be revoked at a time to one user over one entity.

### Groups

#### `group_grant`

| **PATH**        | `core\actions\group\grant.php`                                                    |
| --------------- | --------------------------------------------------------------------------------- |
| **URL**         | `?do=group_grant&right=create&group=users&entity=core\Task`                       |
| **CLI**         | `$ ./equal.run --do=group_grant --right=create --group=users --entity=core\\Task` |
| **DESCRIPTION** | Grant additional privilege to given group.                                        |

> Only one right can be granted at a time to one group over one entity.

#### `group_revoke`

| **PATH**        | `core\actions\group\revoke.php`                                                    |
| --------------- | ---------------------------------------------------------------------------------- |
| **URL**         | `?do=group_revoke&right=create&group=users&entity=core\Task`                       |
| **CLI**         | `$ ./equal.run --do=group_revoke --right=create --group=users --entity=core\\Task` |
| **DESCRIPTION** | Revoke privilege from a given group.                                               |

> Only one right can be revoked at a time to one group over one entity.

#### `group_add-user`

| **PATH**        | `core\actions\group\add-user.php`                                         |
| --------------- | ------------------------------------------------------------------------- |
| **URL**         | `?do=group_add-user&group=users&user=cedric@equal.run`                    |
| **CLI**         | `$ ./equal.run --do=group_add-user --group=users --user=cedric@equal.run` |
| **DESCRIPTION** | Add a user as member of a given group.                                    |

### Debugging Access Control Issues

ACLs are defined using a dedicated class `Permission`.

Roles are assigned using a dedicated class `Assignment`.

**Permission Fields:**  

* object_class
* object_id
* rights
* group_id
* user_id

Permissions can be inherited from one of the groups a user belongs to. Additionally, if a right is granted to a user on a given entity, it is also granted on all entities that inherit from that entity.

Role assignment is done using `Assignment` objects.

```sql
Assignment
    object_class
    object_id
    role
    user_id
```

SQL queries for assignments are direct (no table inheritance). Beforehand, all the roles that involve the requested assignment must be listed.

Example:
```php
$map_roles = [];
$map_roles[$role] = true;
$descriptor = $roles[$role];
while(isset($descriptor['implied_by'])) {
    foreach((array) $descriptor['implied_by'] as $r) {
        $map_roles[$r] = true;
    }
    $descriptor = $descriptor['implied_by'];
}

$roles = array_keys($map_roles);
```

> Note: Inheritance can be set up for roles by using specific policies (possibly associated with 'view', 'edit', 'delete' actions). Example: 'folders' objects containing 'documents'.

---