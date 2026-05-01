# Workflows

Workflows are part of the [Model](../entities/entities.md) definition and describe the lifecycle of an entity through a series of statuses and transitions.

When a workflow must be assigned to an entity, a dedicated method, `getWorkflow()`, must be defined for describing the workflow.

The `getWorkflow()` method must return an associative array in which all possible statuses are identified by a key of the map.

For a given entity, the workflow reflects all possible values of the special field `status` (e.g., `validated`, `suspended`, `confirmed`), along with all possible transitions from one status to another (e.g., `validate`, `suspend`, `confirm`).

## Example

```php
<?php
public static function getWorkflow() {
    return [
        'created' => [
            'transitions' => [
                'validate' => [
                    'watch'       => ['validated'],
                    'domain'      => ['validated', '=', true],
                    'description' => 'Update the user status as validated.',
                    'status'      => 'validated',
                ]
            ]
        ],
        'validated' => [
            'transitions' => [
                'suspend' => [
                    'description' => 'Set the user status as suspended.',
                    'status'      => 'suspended'
                ],
                'confirm' => [
                    'domain'      => ['validated', '=', true],
                    'description' => 'Update the user status as confirmed.',
                    'status'      => 'confirmed'
                ]
            ]
        ]
    ];
}
```

## Key Principles

| Principle                     | Description                                                                                                                                                                                                       |
| ----------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Default Behavior**          | By default, no workflow is defined.                                                                                                                                                                               |
| **Status Field Requirement**  | The workflow assumes the existence of a `status` field. Only values defined in the `selection` property of the status field can be assigned to it. The initial value must be specified in the `default` property. |
| **Single Workflow per Class** | There can be only one workflow per class. A behavioral change implies a change in logic, justifying the creation of a new class, potentially in a new package.                                                    |
| **Inheritance**               | A workflow can be overwritten or overridden by inheritance.                                                                                                                                                       |
| **Field Constraints**         | The workflow allows adding constraints to entity fields (`readonly`, `required`, `visible`) based on the status.                                                                                                  |
| **Dependencies**              | The `status` field can have a `dependencies` property to force recalculation of computed fields when modified.                                                                                                    |
| **UI Integration**            | In the UI, possible transitions from the current state are visible in the transition menu, with actions displayed to the right of the current status.                                                             |
| **Schema Adaptation**         | If a `getWorkflow()` method is defined, the schema returned by `getSchema()` is adapted based on the current status.                                                                                              |
| **Initial Status**            | The initial status is defined in the `default` property of the status field definition.                                                                                                                           |
| **Final Status**              | The final status (if any) is the status for which there is no outgoing transition.                                                                                                                                |

---

## Workflow Graph Structure

The workflow is composed of **nodes** representing statuses (successive logical states through which an entity can pass) connected by **vectors** representing transitions. Multiple outgoing and incoming transitions can exist on the same status.

```json
{
  "status_name_A": {
    "readonly": {},
    "transitions": {
      "transitionX": {
        "watch": ["field1", "field3"],
        "domain": ["field1", ">", "field3"],
        "policies": ["policy1", "policy2"],
        "description": "f1 > f3",
        "status": "status_name_B"
      },
      "transitionY": {
        "watch": ["has_quotation_sent"],
        "description": "has_quotation_sent",
        "status": "status_name_C"
      },
      "transitionZ": {
        "description": "manual",
        "status": "status_name_D",
        "onbefore": "onbeforeTransitionC",
        "onafter": "onafterTransitionC"
      }
    }
  }
}
```

### Transition Properties

| Property      | Description                                                                                                              |
| ------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `watch`       | Array of field names that trigger automatic transition evaluation when modified.                                         |
| `domain`      | Condition that must be satisfied for the transition to be allowed. Uses [Domain](../domains.md) syntax.                  |
| `policies`    | Array of [policy](../authorization/authorization-overview/#policies) names that must pass for the transition to proceed. |
| `description` | Human-readable description of the transition.                                                                            |
| `status`      | Target status after the transition completes.                                                                            |
| `onbefore`    | Method name to call before the transition executes.                                                                      |
| `onafter`     | Method name to call after the transition completes.                                                                      |

---

## Transition Types

### Manual Transitions

Manual transitions are triggered explicitly by controllers or user actions.

**Direct status modification**: Controllers can directly modify the status of an object, permitted only if consistent with the workflow:

1. Retrieve the workflow and find the descriptor for the current status.
2. Filter transitions whose `status` property matches the requested new status.
3. If no match exists or no transition has all conditions fulfilled, an error is thrown.
4. If a match exists with all conditions fulfilled, the status is updated. If a `function` property exists, it is called with the current object.

**Signal-based transition**: A controller can invoke a transition by emitting a signal using `ORM::transition(transition_id)`:

1. Retrieve the workflow and find the descriptor for the current status.
2. Search for the transition among those in the descriptor.
3. If a match exists and optional conditions are fulfilled, the status is updated.
4. Otherwise, an error is thrown.

### Automated Transitions

Automated transitions occur when modifying an object triggers a status change based on field values:

1. Retrieve the workflow and find the descriptor for the current status.
2. Filter transitions whose `watch` property contains any of the updated fields.
3. If a match exists with all conditions fulfilled, the status is updated based on the `status` property.
4. Otherwise, no action is taken.

!!! note "Transition Trigger"
    Transitions can be activated either by complex actions (external) or by modifications of the object's fields.

---

## Object Lifecycle vs Business Workflow

In eQual, two distinct concepts govern the evolution of an object: `state` and `status`.

### `state`: Technical Lifecycle

The `state` field describes the **technical lifecycle** of an object, independent of its business logic. It determines whether an object is currently active in the system and what level of interaction is allowed.

| State      | Description                                                                                                                              |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| `draft`    | The object is being created or edited and is not yet considered final. It is excluded from most system operations and may be incomplete. |
| `instance` | The object is active and usable in the application. This is the default state for normal operations.                                     |
| `archive`  | The object has been deactivated or logically deleted. It is kept for record-keeping but excluded from active workflows.                  |

This field is managed internally by the framework and is essential for filtering, safety checks, and data lifecycle management.

### `status`: Business Workflow

The `status` field expresses the **functional or business logic progression** of an object. It models the steps of a business workflow specific to the object type (e.g., orders, publications, approvals).

**Recommended generic pattern:**

```text
[created] → pending → validated
```

- `created` (optional): Initial state upon instantiation, before submission.
- `pending`: Waiting for review, approval, or further action.
- `validated`: Object is confirmed, accepted, or finalized.

Variants like `published`, `approved`, `rejected`, or `cancelled` can be added depending on the object type and use case.

!!! warning "Important Distinction"
    Although both `state` and `status` may contain a value like `draft`, their meanings are independent:
    
    - `state: draft` → Object is not yet usable or finalized at the system level.
    - `status: draft` → Object is in the early stage of its business process (e.g., not yet submitted).

### Best Practices

- Keep use of `state` values minimal.
- Use `status` to implement domain-specific workflows with clear transition rules.
- Avoid using the same labels in `status` that might cause confusion with `state` values.

---

## Using Policies in Workflows

Transitions can reference [policies](../authorization/authorization-overview/#policies) to enforce authorization rules before allowing a status change. Policies are specified in the `policies` array of a transition definition.

When a transition includes policies, the system verifies that all specified policies pass before executing the transition. If any policy fails, the transition is blocked.

---

## Error Handling

### Invalid Transition

When a transition is not allowed from the current status:

```json
{
    "errors": {
        "NOT_ALLOWED": {
            "invalid_transition": "No transition 'validate' from status 'created' defined in core\\User workflow."
        }
    }
}
```

### Field-Level Errors

`NOT_ALLOWED` responses can include nested descriptors for each involved field:

```json
{
    "errors": {
        "NOT_ALLOWED": {
            "legal_name": {
                "missing": "Legal name cannot be empty for legal person."
            }
        }
    }
}
```

### Broken Policy

When a policy check fails:

```json
{
    "errors": {
        "NOT_ALLOWED": {
            "broken_policy": "Collection does not comply with Policy releasable"
        }
    }
}
```

---

