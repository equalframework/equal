# Searches

## Overview

Search views provide users with powerful tools to filter and find records based on specific criteria. They can be configured with various properties to customize their behavior and appearance.

**Purpose:** Search views allow users to quickly locate relevant records by applying filters, sorting, and pagination. They can be associated with specific entities and domains to ensure accurate results.

**Example:** A search view for a "Products" entity might include filters for category, price range, and availability, along with sorting options by name or price.

**Key Features:** 

- Entity association for targeted searching
- Domain conditions to refine search results
- Default sorting and pagination options for improved usability

---

## Search View Properties

| **PROPERTY**  | **TYPE**  | **DESCRIPTION**                                                         |
| ------------- | --------- | ----------------------------------------------------------------------- |
| `id`          | `string`  | Unique identifier for the search view                                   |
| `name`        | `string`  | Display name for the search view                                        |
| `description` | `string`  | (optional) Description of the search view                               |
| `entity`      | `string`  | The entity this search view is associated with (e.g., `tutorial\\Post`) |
| `domain`      | `array`   | [Domain](../../models/domains.md) conditions to apply                                      |
| `order`       | `string`  | Column to use for sorting results                                       |
| `sort`        | `string`  | Sort direction for search results (e.g., `asc`, `desc`)         |
| `start`       | `integer` | Pagination offset for search results                                    |
| `limit`       | `integer` | Maximum number of search results to display                             |
| `layout`      | object    | Layout configuration for the search view (see below)                    |

## Layout Structure

Searches are configured as JSON objects containing an array of search items. The base of their configuration is the `layout` property, in which you can define groups, sections, rows, and columns to organize the search's content. Each item within the layout specifies which view to embed and how to filter it. For more details on the layout structure, refer to the [Forms layout documentation](./forms#layout-structure).

#### Items

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                          |
| ------------ | --------- | ------------------------------------------------------------------------ |
| `id`         | `string`  | Unique identifier for the search view                                    |
| `domain`     | `array`   | [Domain](../../models/domains.md) conditions to apply to search results                     |
| `order`      | `string`  | Default sorting order for search results (e.g., `name asc`, `date desc`) |
| `sort`       | `string`  | Default sort direction for search results (e.g., `asc`, `desc`)          |
| `start`      | `integer` | Pagination offset for search results                                     |
| `limit`      | `integer` | Maximum number of search results to display                              |

---

### Search View Example

```json
{
    "id": "search.default",
    "name": "Post",
    "description": "",
    "entity": "tutorial\\Post",
    "order": "id",
    "sort": "asc",
    "start": 2,
    "limit": 5,
    "domain": ["title", "like", "%Blog%"],
    "layout": {
        "groups": [
            {
                "label": "Posts",
                "id": "posts_group",
                "sections": [
                    {
                        "label": "Posts List",
                        "rows": [
                            {
                                "columns": [
                                    {
                                        "width": "50%",
                                        "align": "center",
                                        "items": [
                                            {
                                                "type": "field",
                                                "value": "published",
                                                "width": "50%",
                                                "widget": {
                                                    "type": "date",
                                                    "heading": true
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        "width": "50%",
                                        "items": [
                                            {
                                                "type": "field",
                                                "value": "title",
                                                "width": "50%",
                                                "widget": {
                                                    "type": "text",
                                                    "heading": true
                                                }
                                            },
                                            {
                                                "type": "field",
                                                "value": "content",
                                                "width": "50%",
                                                "widget": {
                                                    "type": "text",
                                                    "wrap": true
                                                }
                                            },
                                            {
                                                "type": "field",
                                                "value": "author_name",
                                                "width": "50%",
                                                "widget": {
                                                    "type": "text",
                                                    "heading": true
                                                }
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```