### Audit log & changes history

The logging system relies on two entities to keep track of object
modifications:

1. `core\Log` — metadata about an action performed on an object
2. `core\Change` — the detailed payload of the modifications (the "diff")

These logging information are stored directly in database. `core\Log` is indexed for fast queries while `core\Change` holds the full
history and can be purged if required. More on that in the [Logging overview](../../operations/monitoring-maintenance/logging-overview.md) section.

#### Entities summary

**`core\Log` (metadata)**

* stores **who** did **what** on **which object**
* key fields:
  * `creator` – user ID (`0` or `1` means "System")
  * `action` – type of action (`R_UPDATE`, `R_CREATE`, ...)
  * `object_class`, `object_id` – points to the affected object
* may or may not be linked to a `core\Change` entry

**`core\Change` (diff payload)**

* stores only the **changed fields** when an object is modified
* linked to `Log` through `log_id`
* also stores:
  * `object_class`, `object_id` – for direct access
  * `description` – contextual text
  * `diff` – field‑level differences in JSON
* can be archived or purged over time

#### When changes are made

* During `ObjectManager::create()` and `ObjectManager::update()`
* A `Log` is always created if `LOGGING_ENABLED === true`
* A `Change` is only created if there are field‑level differences that can be
  computed

#### Rebuilding history

**Changes history retrieval**

```
1. For each LOG (log_id = $id):
   └── Fetch its linked CHANGE:
       └── extract: description + diff
                ↓
             $map_new_values = fields + new values
                ↓
             $fields = list of changed fields
```

---

```
2. Prepare to look BACK in time:

   Search previous changes (older than current log):
   ┌──────────────────────────────────────────────┐
   │ WHERE:                                       │
   │   object_class = log.object_class            │
   │   object_id    = log.object_id               │
   │   created      < log.created                 │
   │   log_id       != current log_id             │
   └──────────────────────────────────────────────┘
     ↓
   $changes_ids = last 25 matching Change IDs
```

> The 25 changes limit is implementation-specific.

---

```
3. Iterate each previous CHANGE in reverse (newest → oldest):
   └── For each field in $fields:
       └── If field exists in change.diff:
           └── Save as old value
           └── Remove field from list
   → Stop when all fields are resolved
```

**Use case: reconstruct an object’s state at time `T`**

1. Get all `Change` records related to an object
2. Filter only those with `created <= T`
3. Walk through the `Change` entries in reverse chronological order
4. For each field, capture the most recent previous value
5. Merge with the current object to build a snapshot at time `T` if needed

#### Human‑readable rendering (HTML)

Each change can be displayed as an HTML block:

```html
<table>
  <tbody>
    <tr><td>title</td><td>“Draft”</td><td>→</td><td>“Published”</td></tr>
    <tr><td>status</td><td>“pending”</td><td>→</td><td>“validated”</td></tr>
  </tbody>
</table>
```

> Special case: `creator == 0 or 1` is rendered as "System".

### Reporting

The Reporter `lib/equal/error/Reporter.class.php` class focuses on system and error logs, which is different from the `Logger` that keeps track of application-level object changes in the database.

Reporting is used to keep track of the following types of messages:

- debug (can be used in any script, to check variables values);
- warning (the action is done, but incomplete);
- error (the action can't be done);
- and fatal errors (the system stops) messages.

The `Reporter` registers handlers for PHP errors and uncaught exceptions. It writes technical diagnostics to `log/equal.log`, while the public HTTP response only receives the standardized error descriptor generated from the exception message and eQual error code.

For example:

```php
trigger_error(
    "ORM::Capability denied {$operation} {$this->class}",
    EQ_REPORT_ERROR
);

throw new \Exception(
    'capability_denied',
    EQ_ERROR_NOT_ALLOWED
);
```

The log entry contains the technical context needed by developers. The exception message remains stable and generic enough to be returned to the client or translated.

Calling `trigger_error()` with a supported reporting level such as `EQ_REPORT_ERROR` is equivalent to calling the reporter service directly:

```php
$reporter->error(
    "ORM::Capability denied {$operation} {$this->class}"
);
```

System and error logs can be accessed directly on the server or from the command line at:

```text
./log/equal.log
```

They can also be viewed through the HTTP console at:

```text
/console.php
```

The HTTP console is only available when the environment is running in `development` mode.

> In the future, a timestamps journal could be enabled in the global `config.inc.php`, to keep track of the length of use of any eQual resources.

---
