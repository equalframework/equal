## Built-in Services

The eQual framework provides a set of built-in services to handle common tasks such as authentication, routing, object management, and error reporting. Each service is associated with a specific class and is accessible throughout the application.

| **ID**   | **DESCRIPTION**                                                                                                     |
| -------- | ------------------------------------------------------------------------------------------------------------------- |
| report   | **`equal\error\Reporter`**<br />Intercepts, handles, and filters error and exception messages; stores them in logs. |
| auth     | **`equal\auth\AuthenticationManager`**<br />Manages credentials and authentication tokens.                          |
| access   | **`equal\access\AccessController`**<br />Manages user and group permissions for entities and controllers.           |
| context  | **`equal\php\Context`**<br />Handles HTTP requests and responses.                                                   |
| validate | **`equal\data\DataValidator`**<br />Checks field consistency for entities and controllers.                          |
| adapt    | **`equal\data\DataAdapterProvider`**<br />Provides DataAdapters for transforming data types (e.g., string to date). |
| orm      | **`equal\orm\ObjectManager`**<br />Manages objects (classes).                                                       |
| route    | **`equal\route\Router`**<br />Returns existing routes.                                                              |
| spool    | **`equal\email\EmailSpooler`**<br />Manages email delivery and queuing.                                             |

The services are listed at `eq.lib.php` in a container responsible to instantiate them. Every service is given an arbitrary name that can be overwritten (*limited to the use of the controller*). If a controller requires the use of a service, it needs to invoke it explicitly. For example, to use the `report` service:

```php
<?php
list($params, $providers) = announce([
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "user name",
            'type'          => 'string',
            'required'      => true
        ],
        'password' =>  [
            'description'   => "user password",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth', 'orm'],     // Services are invoked

    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_REFRESH_TOKEN_VALIDITY',
                        'AUTH_TOKEN_HTTPS']
]);
```

If the service is present in the global `config.inc.php`, it can also be overwritten inside the `config.inc.php` files of the packages. If a controller calls an other controller, the called controller won't access the services the calling controller has access to. There is no inheritance for services between controllers.

---

## Configuration Parameters

The eQual framework allows administrators to define and modify runtime parameters through the **Settings** application. These parameters are modeled with `Setting`, `SettingValue`, and `SettingSequence` records, so package code can resolve contextual values without hard-coding environment-specific behavior.

For setting structure, naming conventions, sections, and examples, see [Settings](settings.md).

---

## Alerts Lifecycle and Message Models

Alerts in eQual are managed through dedicated message-model entities, ensuring consistency and traceability.

### Message Model (`core\alert\MessageModel`)

The `MessageModel` entity defines the semantics of an alert type:

* `name`: Internal identifier.
* `label` and `description`: Multilingual text describing the alert.
* `type`: Optional tag for logical grouping.
* `messages_ids`: References all alert instances for this model.

This registry of alert types ensures consistency across the system. For more on multilingual support, see [Internationalization](../i18n/i18n-overview.md).

### Message (`core\alert\Message`)

The `Message` entity represents an emitted alert linked to a specific object. It contains:

* `object_class` and `object_id`: Target of the alert.
* `message_model_id`: Reference to the alert model.
* `severity`: One of `notice`, `warning`, `important`, `error`.
* `controller`: Optional fallback controller to re-evaluate the condition.
* `params` / `links`: JSON payload with controller input or related resources.
* `user_id` / `group_id`: Optional recipient scoping.

Alerts are persisted through the ORM, and a uniqueness constraint ensures only one alert per `(object_class, object_id, message_model_id)` tuple.

For more on ORM and persistence, see [Object Management](../entities-persistence/orm.md#object-definition).

---
