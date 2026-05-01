# Contexts

eQual leverages a **view generator** and **UI components framework** to create dynamic and structured user interfaces. The central part of the screen is managed by a **frame** that holds a **stack of contexts**, allowing for dynamic navigation and interaction with data.

When opening a view (e.g., by clicking on an item in a list), eQual creates a new context and pushes it onto the top of the stack. Switching back to a previous view pops the context off the stack. Apps define the initial bootstrap view, and menus structure a series of view descriptors with links to load corresponding contexts.

A **context** in eQualUI defines the target of a navigation or action within the application. It encapsulates the metadata needed to dynamically load a view for a given business entity, including its filter, rendering mode (`view`), and optionally its interaction type.

Contexts are used in:

* Action routes (`routes`, `actions`, `menus`)
* Error fallbacks (`on_error`)

## Context Object Structure

| **Property**  | **Type** | **Required** | **Description**                                                               |
| ------------- | -------- | ------------ | ----------------------------------------------------------------------------- |
| `id`          | string   | yes          | Unique identifier for the context (used in routing and UI references)         |
| `entity`      | string   | yes          | Fully qualified name of the ORM entity (e.g., `finance\\accounting\\Journal`) |
| `view`        | string   | yes          | Target view ID to load (e.g., `list.default`, `form.extended`)                |
| `domain`      | array    |              | Filtering logic, using `[field, operator, value]` or combined clauses         |
| `type`        | string   |              | Optional type to categorize context (e.g., `entry`, `detail`, `action`)       |
| `label`       | string   |              | Display label shown in UI                                                     |
| `icon`        | string   |              | Material icon or custom icon name                                             |
| `description` | string   |              | Optional short description of the context                                     |


!!!note "Additional Notes"
    * Contexts can use **dynamic variables** (e.g., `object.id`, `user.id`, etc.) inside their `domain` expressions.
    * The `view` must be available for the specified entity; if not, a fallback via `on_error` is recommended.
    * The context’s `id` must be unique globally across view definitions, especially if referenced in UI routes or menus.

## Using Contexts in Views

Contexts are primarily used in **menus** and **actions** to define the target of a navigation or operation. When a user clicks on a menu item or triggers an action, the associated context is loaded, and the framework renders the specified view with the defined parameters.

However, contexts can also be used in other scenarios, such as customizing view behavior based on the current state or user interactions.

**For Lists:**

* `"purpose" = "view"`: View a list of existing objects : only possible action should be available (`create`).
* `"purpose" = "select"`: Select a value for a field : the displayed list purpose is to select an item (other actions should not be available).
* `"purpose" = "add"`: Add one or more objects to a x2many fields.

**For Forms:**

* `"mode" = "view"`
    * `"purpose" = "view"`: View a single object : only available actions should be 'edit'.
* `"mode" = "edit"`
    * `"purpose" = "create"`: Create a new object : only available actions should be 'save' and 'cancel'.
    * `"purpose" = "update"`: Update an existing object : only available actions should be 'save' and 'cancel'.

Other view types (e.g., dashboards, charts) don't have a specific `purpose` but can still leverage contexts to define their data scope and behavior.

##  Stacking Contexts

#### What is a Context
A **context** defines the scope and environment in which a view operates. It includes:

- The **entity** being displayed or modified (e.g., `User`, `Post`).
- The **view type** (e.g., `list.default`, `form.default`).
- Additional parameters like domain (filter), actions, or associated routes.

#### How Context Stacking Works
Context stacking manages **nested or hierarchical contexts** for interacting with multiple data layers or UI components. Each new context is shown on the top of the previous one, creating a logical stack:

1. **Initial Context**: Application-specific context related to the initial view.

2. **Sub-contexts**: Nested contexts for related entities or actions.

    

!!! Note "Contexts linked to routes (url)"
    Alternatively, some contexts can be associated to specific routes .

## Interaction Between Contexts and Views

When users interact with the application:

1. **Context Stack Evolves**: Each interaction (e.g., opening a form) creates a new context atop the stack.

2. **Dynamic Data Scope**: The current context defines which data is fetched or filtered.

3. **Rendering Views**: UI components are rendered based on the context, including inherited and user-applied filters.



**For example:**

* In a **list view**, clicking an item dynamically loads a **form view** with the selected entity’s details, while retaining the original context for navigation.

### Contexts examples

#### Example 1: Filtered list of accounting journals
Displays the `list.default` view for the `Journal` entity, filtered to show only records not linked to any condo.

```json
{
    "id": "item.accounting.journals",
    "type": "entry",
    "label": "Journals",
    "icon": "auto_stories",
    "description": "",
    "context": {
        "entity": "finance\\accounting\\Journal",
        "domain": ["condo_id", "=", null],
        "view": "list.default"
    }
}
```

#### Example 2: Route to profile view for current user
Dynamically loads the profile form for the currently logged-in user.
```json
{
    "id": "context.user.profile",
    "label": "My Profile",
    "icon": "person",
    "context": {
        "entity": "core\\auth\\User",
        "domain": ["id", "=", "current_user_id"],
        "view": "form.profile"
    }
}
```

####  Example 3: Context used in a menu or action
Used in a dashboard or action to list all active subscriptions.
```json
{
    "id": "action.manage.subscriptions",
    "label": "Manage Subscriptions",
    "icon": "subscriptions",
    "context": {
        "entity": "crm\\Subscription",
        "domain": ["active", "=", true],
        "view": "list.admin"
    }
}
```

#### Example: Action route (non-contextual)
This route points directly to a file generation endpoint. It does not use a `context`, but could be enhanced to do so.

```json
{
    "id": "print.pdf",
    "label": "Print tender",
    "icon": "print",
    "route": "/?get=renover_tender-pdf&id=object.id",
    "target": "_blank",
    "absolute": true
}
```

#### Example: Fallback context in `on_error`
Automatically redirects to a list view when the main object is missing or deleted.
```json
"on_error": {
    "missing": {
        "context": {
            "entity": "sale\\pay\\Funding",
            "view": "list.default",
            "domain": ["status", "!=", "archived"]
        },
        "label": "Funding list"
    }
}
```

---