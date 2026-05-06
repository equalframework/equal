
# Access Control Lists (ACL)

Access Control Lists (ACLs) are a fundamental mechanism for managing permissions in eQual. They associate specific rights with users or groups for particular resources.

---

## Permission Levels

eQual defines the following permission levels, which can be combined using a binary mask:

- **CREATE**: Permission to create new objects.
- **READ**: Permission to view objects.
- **UPDATE**: Permission to modify objects.
- **DELETE**: Permission to remove objects.
- **MANAGE**: Permission to manage rights and permissions.
- **ALL**: Grants all permissions.

### Rights Values

The rights values are defined in [`eq.lib.php`](../../../../../../eq.lib.php) as follows:

```php
define('EQ_R_CREATE',  1);
define('EQ_R_READ',    2);
define('EQ_R_UPDATE',   4);
define('EQ_R_DELETE',  8);
define('EQ_R_MANAGE',  16);
define('EQ_R_ALL',      31);
```

!!! note "Default Rights"
Default permissions are defined in the configuration file (config.json) under the DEFAULT_RIGHTS setting.

### Permission Class

The `Permission` class (`packages/core/classes/Permission.class.php`) models ACLs. It includes the following structure:
```php
<?php
public static function getColumns() {
    return [
        'class_name' => ['type' => 'string'],
        'group_id' => [
            'type' => 'many2one',
            'foreign_object' => 'core\Group',
            'foreign_field' => 'permissions_ids'
        ],
        'rights' => ['type' => 'integer']
    ];
}
```
The `rights` field is a binary mask representing the permissions granted to the associated group. If a user belongs to multiple groups, the most permissive combination of rights is applied.

---

### ACL Logic

The `AccessController` service determines whether a user has the required rights for a specific operation. It evaluates permissions based on the following logic:

1. **Class-Level Rights:** Checks ACLs for the class and its namespace.
   
2. **Wildcard Rights:** Evaluates wildcard entries in the ACL.
   
3. **Object-Level Rights:** Determines rights for specific objects.

The most permissive rights always take precedence.

#### Overriding the AccessController

You can override the default `AccessController` service to implement custom logic:
```php
<?php
namespace mylib\access;

class AccessController extends \equal\access\AccessController {
    public function hasRight($user_id, $operation, $object_class='*', $objects_ids=[]) {
        // Custom logic
    }
}
```
To use the custom service, inject it into a controller:
```php
<?php
list($params, ##$providers) = eQual::announce([
    'description' => 'Controller with custom ACL handling.',
    'providers' => ['context', 'orm', 'adapt' => 'mylib\access\AccessController']
]);
```

---

