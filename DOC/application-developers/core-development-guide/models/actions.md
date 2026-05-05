# Actions

Actions are a convenient way to describe the operations available for a given entity, which can be manually applied to it. Each action can be associated with a series of [policies](./authorization/authorization-overview/#policies) to automatically restrict its availability based on the context (e.g., user, object, collection).

Actions can be invoked using the `::do($action)` method on any entity [Collection](./collections/collections-overview.md). If the action is unknown or cannot be performed due to one or more broken policies, an exception is raised.

## Defining Actions

### Example: `Report.class`

The following example demonstrates how actions are defined for the `Report` entity:

```php
<?php
public static function getActions() {
    return [
        'init' => [
            'description'   => "Generates the lines of the Report.",
            'policies'      => [],
            'function'      => 'doInit'
        ]
    ];
}

public static function doInit($self) {
    $self->read(['status', 'account_lines_ids' => ['account_id', 'total']]);
    foreach ($self as $id => $report) {
        foreach ($report['account_lines_ids'] as $line_id => $line) {
            ReportLine::create([
                'report_id'             => $id,
                'service_account_id'    => $line['account_id'],
                'total'                 => $line['total']
            ]);
        }
    }
}
```

### Example: Invoking Actions

To invoke an action, use the following syntax:

```php
<?php
// Create a new report and initialize it (generates all lines)
Report::create()->do('init');
```

---

## Access Control Overview for Actions

Access control is a crucial topic in the eQual framework. It ensures that user interactions with entities are authorized based on a variety of strategies. These strategies include:

- **ACL (Access Control Lists)**: Permissions for CRUD operations (create, read, update, delete) at the entity level.
- **ABAC (Attribute-Based Access Control)**: Conditions based on user attributes and object state.
- **RBAC (Role-Based Access Control)**: Permissions assigned to roles (e.g., admin, editor, viewer).
- **PBAC (Policy-Based Access Control)**: Policies that define complex rules for access.

For more information on access control strategies, see the [AccessController documentation](./authorization/access-controller.md).

## Key Concepts in Access Control

### Lexicon

The following terms are used in the context of access control:

- **ACLs**: Define permissions for CRUD operations at the entity level for groups or users.
- **Policies**: Define entity-specific conditions that depend on both the current user and the object's state (via `isCompliant()`).
- **Roles**: Arbitrary roles (e.g., owner, admin, editor) assigned to users on specific objects. Roles can be hierarchical (e.g., an owner is also an admin, and an admin is also an editor).
- **Actions**: Operations that can be performed on entities. Actions can be restricted by policies and/or roles.

#### Groups vs. Roles

- **Groups**: Collections of users with shared permissions. Groups are based on identity.
- **Roles**: Collections of granted actions, often tied to specific conditions or policies. Roles are based on activity and can be hierarchical.

---

## Access Control Logic Recap

Access control in eQual involves several components:

```
Access Control
├── Model (at field level, using `access`, `readonly`, and `visible` attributes)
├── CRUD operations
│    ├── CRUD rights (ACL) (via `hasRight()`)
│    └── Policies (via `isCompliant()`) and Roles (via `hasRole()`)
└── Actions
     ├── Roles (via `hasRole()`) : Restrict actions to certain roles (RBAC)
     └── Policies (via `isCompliant()`) : Restrict actions to certain policies (ABAC, PBAC)
```

!!! note "Constraints"
    Besides access control, other constraints may apply when transitioning an object from one state to another (e.g., workflows assigned to the entity).

## Best Practices for Defining Actions

1. **Use Descriptive Names**: Ensure action names clearly describe their purpose (e.g., `init`, `approve`, `reject`).
2. **Attach Policies**: Define policies to restrict actions based on context (e.g., user permissions, object state).
3. **Document Actions**: Provide clear descriptions for each action, including its purpose and any associated policies or roles.
4. **Test Thoroughly**: Ensure actions are tested for both valid and invalid scenarios to prevent unauthorized access.

---

