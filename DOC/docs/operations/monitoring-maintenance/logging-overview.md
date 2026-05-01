### Logging

The Logger (`equal/log/Logger.class.php`) class adds logs to the database using 4 different parameters :

| **PARAMETER** | **DESCRIPTION**                                                                |
| ------------- | ------------------------------------------------------------------------------ |
| user_id       | Identifier of the user responsible for the action.                             |
| action        | Action of the user.                                                            |
| value         | Complementary value of the action (**example**: previous value of the object). |
| object_class  | Name of the entity on which the action is done.                                |
| object_id     | Identifier of the entity on which the action is done.                          |

Those logs are system object, no permissions must be applied.

The logs allow users to keep an overview of object changes (action log).

The actions are CRUDS by default, but custom actions could also be created, **example** : SENT, when a message is sent.

As of now, the logs don't keep track of the content of the changes or reason behind it.



The logs can be enabled or disabled in the global config file :

```php
define('LOGGING_ENABLED', true);
```


### Versioning

An other way to keep track of the object changes is the use of the version (`core/version.class.php`) class.

In which you could have an evolving tracking of an object, going through changes over times, with the value changes (`serialized_value`).



The versioning can be enabled or disabled in the global config file :

```php
define('VERSIONING_ENABLED', true);
```

---