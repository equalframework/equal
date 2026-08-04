# Debugging Tools & Console

## Purpose

eQual provides two complementary sources of diagnostic information:

- the public HTTP response, which exposes a stable error category and a client-safe message;
- the technical log, which records the context required to diagnose the issue.

The HTTP response should be used to understand the API-level failure. The log should be used to investigate the cause in the framework, controller, ORM, or configuration layer.

## Log file

System and error logs are written to:

```text
./log/equal.log
```

This file can grow quickly in development or during automated test runs. It should be archived, rotated, or cleared periodically according to the environment policy.

Each log entry is written as JSON and may include:

| Field | Description |
| --- | --- |
| `thread_id` | Identifier used to group log entries from the same execution thread. |
| `time` / `mtime` | Timestamp and microsecond component. |
| `level` | Severity, such as `DEBUG`, `INFO`, `WARNING`, `ERROR`, or `FATAL`. |
| `mode` | Source area, such as `PHP`, `ORM`, `NET`, or `AAA`. |
| `class` / `function` | PHP class and function associated with the log entry when available. |
| `file` / `line` | Source location associated with the entry. |
| `message` | Technical diagnostic message. |
| `stack` | Backtrace for warnings, errors, fatal errors, and uncaught exceptions when available. |

Example log entry:

```json
{
    "thread_id": "44b1924d",
    "time": "2026-07-08T22:01:43+00:00",
    "mtime": "294898",
    "level": "WARNING",
    "mode": "ORM",
    "class": "equal\\orm\\ObjectManager",
    "function": "validate()",
    "file": "C:\\DEV\\wamp64\\www\\equal\\lib\\equal\\orm\\Collection.class.php",
    "line": 686,
    "message": "given value (`foo`) for field `core\\test\\Test`::`datetime` violates constraint : Value is incompatible with type date. [\"foo\"]",
    "stack": []
}
```

## HTTP console

The debug console provides a browser interface for inspecting the log file:

```text
/console.php
```

For a local installation, it is commonly available at:

```text
http://equal.local/console.php
```

The HTTP console is only available when the environment is running in `development` mode. It should not be treated as a production monitoring interface.

## Error responses

When an exception reaches the main processing entry point, eQual converts it into a standardized HTTP response. The response body contains an `errors` object whose key is derived from the eQual error code.

For example, a request missing the required `entity` parameter:

```text
http://equal.local/?get=model_collect
```

can return:

```json
{
    "errors": {
        "MISSING_PARAM": "entity"
    }
}
```

The response identifies the API-level failure. The corresponding log entry should be checked when the response does not provide enough information to diagnose the root cause.

## Controller announcements

Controllers declare their required parameters through `eQual::announce()`. When a controller is called without required parameters, eQual can return a structured error response and, when applicable, include announcement metadata describing the expected request format.

For diagnostics:

1. identify the controller being called;
2. inspect its announcement to confirm required parameters and expected types;
3. compare the request payload or query string with the announced contract;
4. use `equal.log` to locate the technical source of the failure if the controller contract is valid.

From PowerShell, controller announcements should be inspected through `run.php`, for example:

```bash
php run.php --get=model_collect --announce=true
```

## Configuration errors

Configuration errors are checked by the `announce()` function inside `eq.lib.php` and by the services initialized during the request lifecycle. Faulty configuration generally results in an HTTP 500 response.

When an HTTP 500 occurs during configuration or initialization:

1. check `./log/equal.log` for `ERROR` or `FATAL` entries;
2. verify that the relevant constants are defined in the active configuration;
3. confirm that required services can be instantiated;
4. inspect the stack trace to identify the first failing file and line.

---
