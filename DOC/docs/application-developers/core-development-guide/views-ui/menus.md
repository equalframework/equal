# Menus

Menus are used to define the navigational structure of an application. They organize access to different parts of the application—such as [Forms](./views/forms.md), [Lists](./views/lists.md), or [Dashboards](./views/dashboards.md)—into a hierarchical tree of action buttons.

At the base the structure of a menu is as follows:

```json
{
    "name": "Menu Name",
    "access": {
        "groups": ["group1", "group2"]
    },
    "layout": {
        "items": [
            // Menu items go here
        ]
    }
}
```

## Menu Item Structure

A Menu represents a tree structure where every node is a **Menu Item**. An item can be one of two types:

*   **Parent:** A container that holds other items (categories or groups).
*   **Entry:** A leaf node that triggers an action, usually redirecting the user to a specific [Context](./contexts.md).

### Common Properties

The following properties apply to all menu items, regardless of their type:

| Property      | Type     | Description                                                                                                                                  |
| :------------ | :------- | :------------------------------------------------------------------------------------------------------------------------------------------- |
| `id`          | `string` | A unique identifier for the item. This is essential for referencing the item in [Translations](../i18n/i18n-overview.md) and access control. |
| `type`        | `string` | Defines the behavior of the item. Accepted values: `parent` (contains children) or `entry` (link/action).                                    |
| `label`       | `string` | The display name of the item in the UI (unless overridden by a [Translation](../i18n/i18n-overview.md)).                                     |
| `description` | `string` | (Optional) A brief explanation of the item's purpose, often displayed as a tooltip or helper text.                                           |
| `icon`        | `string` | (Optional) The name of an icon to visualize the item (e.g., from Material Icons).                                                            |
| `children`    | `array`  | (For `parent` type) A list of nested Menu Item objects.                                                                                      |

### Entry Items

An **Entry** item serves as a link. It defines a `context` that tells the framework what data to load and how to display it when clicked.

| Property  | Type     | Description                                                                                                                                      |
| :-------- | :------- | :----------------------------------------------------------------------------------------------------------------------------------------------- |
| `context` | `object` | Defines the target parameters (Entity, View, Filters) for the navigation.                                                                        |
| `route`   | `string` | (Optional) A custom URL path to navigate to when the item is clicked. If not provided, the framework will generate a URL based on the `context`. |

!!! notes "Custom Routes"
    If a `route` is specified, it takes precedence over the `context` for navigation. This allows for custom URL structures or external links.

#### Context Object configuration

The `context` object determines the state of the application upon navigation. It links the menu item to a specific [Entity](../models/entities/entities.md).

| Property | Type     | Description                                                                                                    |
| :------- | :------- | :------------------------------------------------------------------------------------------------------------- |
| `entity` | `string` | The fully qualified name of the [Entity](../models/entities/entities.md) to display (e.g., `core\User`, `lodging\sale\booking\Booking`). |
| `view`   | `string` | The ID of the [View](./views/common-structures/common-structures.md) to render (e.g., `list.default` => it will always be: `[type].[name]`).             |
| `domain` | `array`  | (Optional) A [Domain](../models/domains.md) array defining filters to apply to the data (e.g., `[['status', '=', 'active']]`). |
| `order`  | `string` | (Optional) The name of the field by which to sort the results.                                                 |
| `sort`   | `string` | (Optional) The direction of the sort: `asc` (ascending, default) or `desc` (descending).                       |

## Example Configuration

The following JSON snippet demonstrates a menu structure containing a parent category ("Users") with two entries: one for listing users and another for creating a new user.

```json
{
    "name": "Menu",
    "access": {
        "groups": ["users"]
    },
    "layout": {
        "items":
            [
                {
                    "id": "item.users",
                    "label": "Users",
                    "description": "Manage user accounts",
                    "icon": "people",
                    "type": "parent",
                    "children": [
                        {
                            "id": "item.user.list",
                            "label": "List Users",
                            "description": "View all active users",
                            "icon": "list",
                            "type": "entry",
                            "context": {
                                "entity": "core\\User",
                                "view": "list.default",
                                "order": "name",
                                "sort": "asc",
                                "domain": [
                                    ["is_active", "=", true]
                                ]
                            }
                        },
                        {
                            "id": "routes",
                            "type": "entry",
                            "label": "Routes",
                            "icon": "route",
                            "route": "/routes"
                        }
                    ]
                }
            ]
    }
}