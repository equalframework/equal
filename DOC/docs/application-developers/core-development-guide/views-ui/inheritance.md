# Views inheritance

Views are written in JSON, and act as  blueprints for the rendering engine on how to display content in the web interface.

When a view is requested, a first step involves inheritance logic. This process figures out which JSON to fetch, based on the requested view ID.



Entities defined in eQual have the capability to extend other entities (even from different packages). This allows for the addition of new fields or the customization of existing behaviors.

When a view is requested for a specific entity, if a view with that identifier isn't found, the system searches for a matching view in its ancestors, iterating up to the root `Model` class.

For instance, let's consider a scenario where a `User.form.test.json` is defined for the `core\User` in the core package but not for `identity\User` in the identity package. In this case, if `User.form.test.json` is requested for the `identity\User` entity, the system will return the `User.form.test.json` from the core package.

This is handled by the `core_model_view` (`?get=model_view`) controller. 
[view source](https://github.com/equalframework/equal/blob/master/packages/core/data/model/view.php)

## Child views

It is also possible to make a view inherit from the structure and attributes of a parent view. This feature is particularly useful when implementing minor customizations or integrating specific functionalities, allowing to reduce development time.

Extending a view can be achieved by specifying the parent view in the `"extends"` property,  and then by adapting it using specific `remove` and `update` properties.

This pseudo JSON structure below shows how inheritance can be applied on an existing view. It specifies the base view to extend from, lists elements to remove, and describes updates to be applied to elements in the base view. Comments are used to provide detailed descriptions of each property, clarifying its purpose and usage.


```json
{
    // Specifies the base view that this layout extends from.
    "extends": {
        "entity": "core\\User",
        "view": "form.default"
    },
    "layout": {
        // Lists elements that should be removed from the base view.
        "remove": [],
        // Describes the modifications or updates to be applied to elements in the base view.
        "update": {
            // Specifies the ID of the element to be updated.
            "item.user_id": {
                // Specifies attributes to be updated for the element.
                "attributes": {
                    "width": "50%"
                },
                // Indicates elements to be added before the target element.
                "before": [
                    {
                        // The target can be a group, section, column, row, or item.
                        // (Depending on the target, use an appropriate structure.)
                        "type": "field",
                        "value": "name",
                        "width": "100%",
                        "widget": {
                            "heading": true
                        }
                    }
                ],
                // Indicates elements to be added after the target element.
                "after": [],
                // Indicates elements to be prepended to the target element.
                "prepend": [],
                // Indicates elements to be appended to the target element.
                "append": []
            }
        }
    }
}
```



Building the resulting view is achieved by following the logic below : 

### Within the view

* If `extends` is present, we load (with controller/recursion) the targeted view.

### Within the layout

* **If `remove` is present:** We traverse the view and remove all elements whose ID is present in the `remove` array.

* **If `update` is present:** For all keys (IDs), we search for the element in the view; if found:  

* adapt its attributes if `attributes` is present.

* add elements before it if `before` is present.

* add elements after it if `after` is present.

* add to the beginning of its children list if `prepend` is present.

* add to the end of its children list if `append` is present.

---