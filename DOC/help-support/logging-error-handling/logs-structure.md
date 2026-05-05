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

The Reporter `lib/equal/error/Reporter.class.php` class focuses on system/error logs, which is different from the `Logger` that will keep track of application-level logs.

Reporting is used to keep track of the following types of messages:

- debug (can be used in any script, to check variables values);
-  warning (the action is done, but incomplete);
- error (the action can't be done);
- and fatal errors (the system stops) messages.

The logs are kept inside the `log` folder (CSV files) folder (and appear in [http://equal.local/console](http://equal.local/console)), they are written in a human readable way, to keep track easily.

> The logs are brief, and could, in the future, be written in JSON, to add infos.

The logs content is written following the `core/Log.class.php` structure. They are just like any other object and may use any of their functions.

For example, an other class could point at the log object ("log_id"), every time that object is subject to debug, warnings, errors and fatal errors.

> In the future, a timestamps journal could be enabled in the global `config.inc.php`, to keep track of the length of use of any eQual resources.

---