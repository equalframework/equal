# Routing

## Introduction

The eQual Router (`equal/route/Router.class.php`) is responsible for analyzing incoming requests and directing them to the appropriate [Controller](controllers.md).

Fundamentally, a route maps a **URI** to an **Operation**.

## Structure of a Route

Routes are defined in JSON files located in the `config/routing` directory.

### Route definition
| **PROPERTY**    | **TYPE** | **DESCRIPTION**                                                                                  |
| :-------------- | :------- | :----------------------------------------------------------------------------------------------- |
| **Method**      | `string` | The HTTP method (GET, POST, PUT, DELETE, PATCH).                                                 |
| **Path**        | `string` | The full path of the URI (e.g., `/user/:id`).                                                    |
| **Operation**   | `string` | The [Controller](controllers.md) query string the URI resolves to (e.g., `?get=core_user_read`). |
| **Params**      | `array`  | An optional object defining expected query parameters and their types.                           |
| **Description** | `string` | A human-readable description of the route.                                                       |

### Example
This configuration defines RESTful endpoints for a User entity:

```json
"/user/:ids": {
    "GET": {
        "description": "Retrieve fields values related to a given user",
        "operation": "?get=core_model_read&entity=core\\User"
    },
    "PUT": {
        "description": "Update a user",
        "operation": "?do=core_user_update"
    },
    "DELETE": {
        "description": "Delete a user",
        "operation": "?do=core_model_delete&entity=core\\User"
    }
}
```

## Creating Routes

### Hierarchy and Overrides
Routes are loaded from routing. The files are processed in **alphabetical order**. If a route is defined in multiple files, the definition in the **first** loaded file takes precedence.

It is common practice to use numeric prefixes to manage priority:
```text
/routing
  10-lodging.json      # High priority (custom overrides)
  80-identity.json     # Standard package routes
  99-default.json      # Fallback defaults
```

### Internationalization (i18n)
The routing folder can contain sub-folders for different languages (e.g., `fr`, `en`), allowing for localized route definitions.

## HTTP Request Handling

eQual complies with HTTP standards. It analyzes the HTTP message to route the request and negotiates the response format (Content-Type) based on the Controller's announcement.

**Processing Flow:**

1.  **Request**: Server receives an HTTP request (or CLI command).
2.  **Routing**: The URI is matched against the loaded route configuration to find the corresponding **Operation** (Controller).
3.  **Controller Execution**: The target script is run. Dependency Injection provides params and services.
4.  **Response**: The controller output is captured and formatted (usually as JSON) sent to the standard output stream.

## Default Controller

The application entry point can be configured in config.json. This determines where the root URL (`/`) redirects.

```json
{
    "DEFAULT_PACKAGE": "core",
    "DEFAULT_APP": "workbench"
}
```

In this example, visiting `http://equal.local/` automatically redirects to `http://equal.local/index.php?show=core_workbench`.

## Error Handling

The Router manages basic HTTP error responses when requests cannot be fulfilled.

### 404 Not Found
Returned when the requested URI does not match any known route, or if the resolved controller file does not exist.

**Unknown Route:**
```json
{
    "errors": {
        "UNKNOWN_OBJECT": "unknown_route 'GET':'/undefined-path'"
    }
}
```

**Unknown Controller:**
```json
{
    "errors": {
        "UNKNOWN_OBJECT": "unknown_data_provider: (get) package_missing_script"
    }
}
```

### 400 Bad Request

Returned when input parameters do not satisfy the `params` contract defined in the controller's Announcement.

**Example:**
```json
{
    "errors": {
        "UNKNOWN_ENTITY": "unknown_entity: unknown model ''"
    }
}
```

---