# Debugging Tools & Console

## Console

To help you with human-readable data, eQual comes with its own UI debug console (which simply reads the `EQ_error.log` located  in the `/log` directory).

> Note: `eq_error.log` provides information for each occurring event, so don't forget to delete that file from time to time if you don't want to end up with a huge logs. Especially if you're running a lot of tests.

To access it from the browser: [http://equal.local/console.php](http://equal.local/console.php).


There you can find information about your error, here is an **example** :

```bash
01-05-2022 14:44:43+0.41235100 Warning **@** [`C:\wamp64\www\equal\lib\
equal\orm\Collection.class.php:335`] **in** `equal\orm\Domain::toString()`
: Undefined offset: 1
```



Another **example**, if I did the request :

```
http://equal.local/?get=model_collect
```

The built-in responses, usually already give some information about the error :

```json
"errors": {
        "MISSING_PARAM": "entity"
}
```

Each Controller tells explicitly which parameters are **required**:

```bash
01-06-2022 12:10:17+0.31526700 Warning @ [C:\wamp64\www\equal\run.php:185] in 
{main}(): EQ_DEBUG_ORM::MISSING_PARAM - entity
```

An entity is **missing**, if I do add one :

```
http://equal.local/?get=model_collect&entity=core\User
```

I will get a JSON-object with all the users. 

## Debugging Configuration Errors

Configuration errors are checked by the `announce()` function inside `eq.lib.php`. If there is an issue with the configuration, the system will display an `Error 500`.

---