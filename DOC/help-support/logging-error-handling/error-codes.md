# Exception Handling and Error Codes

## Exception and Throwable

eQual uses standard PHP exceptions and does not define custom exception classes. It natively handles exceptions (or `Throwable` objects) as errors and generates the appropriate HTTP response accordingly.

An exception is generally thrown as follows:

```php
throw new \Exception('capability_denied', EQ_ERROR_NOT_ALLOWED);
```

By convention:

1. the first argument is always a string;
2. the second argument is an eQual constant identifying the error category.

The error constants are defined inside the `eq.lib.php` file.

### Exception message

The message provided as the first argument should be short, stable, and suitable for application-level processing.

It may be:

- a generic identifier, such as `capability_denied`;
- a message intended for translation;
- a serialized array when several parameters must be passed to the error-handling mechanism.

Technical, internal, or sensitive information should not be included in this message.

### Exception propagation

While it remains possible to use `try/catch` blocks, any `Throwable` that is not explicitly handled interrupts the current eQual execution cycle.

It then propagates to the main processing entry point, generally `run.php`, which catches it and automatically generates an HTTP response corresponding to the encountered error.

The `Reporter` class defines specific handlers for:

- PHP errors;
- uncaught exceptions.

This mechanism serves several purposes:

- exposing as little technical information as possible to the client;
- automatically returning the appropriate HTTP status code;
- standardizing error responses;
- facilitating message translation;
- preserving the technical information required for diagnosis in the logs.

In order to generate an error response, any controller may throw an `Exception`.

```php
throw new \Exception('error_msg_id', EQ_ERROR_{CODE});
```

The second argument allows eQual to map the exception to an HTTP status code.

### Error constants

Error constants use the `EQ_ERROR_{CODE}` naming convention.

| **CONSTANT** &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; | **VALUE** | **HTTP** | **DESCRIPTION**                                                                                                                                        |
| :------------------------------------------------------------------------------------------------------------------------------------------- | --------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `EQ_ERROR_UNKNOWN`                                                                                                                           | -1        | 500      | Something went wrong (that requires to check the logs). Equivalent to  HTTP 'Internal Server Error'.                                                   |
| `EQ_ERROR_MISSING_PARAM`                                                                                                                     | -2        | 400      | One or more mandatory parameters are missing. Equivalent to  HTTP 'Bad Request'.                                                                       |
| `EQ_ERROR_INVALID_PARAM`                                                                                                                     | -4        | 400      | One or more parameters have invalid or incompatible value. Equivalent to  HTTP 'Bad Request'.                                                          |
| `EQ_ERROR_SQL`                                                                                                                               | -8        | 456      | There was an error while building SQL query or processing it (check that object class matches DB schema). Equivalent to  HTTP 'Unrecoverable Error'.   |
| `EQ_ERROR_UNKNOWN_OBJECT`                                                                                                                    | -16       | 404      | The request Unknown resource (class, object, view, ...). Equivalent to  HTTP 'Not Found'.                                                              |
| `EQ_ERROR_NOT_ALLOWED`                                                                                                                       | -32       | 403      | Action violates some rule (including UPLOAD_MAX_FILE_SIZE for binary fields) or user don't have required permissions. Equivalent to  HTTP 'Forbidden'. |
| `EQ_ERROR_LOCKED_OBJECT`                                                                                                                     | -64       | 423      | Object cannot be updated because it is locked by another user. Equivalent to  HTTP 'Locked'.                                                           |
| `EQ_ERROR_CONFLICT_OBJECT`                                                                                                                   | -128      | 409      | Version conflict (object has been changed in between). Equivalent to  HTTP 'Conflict'.                                                                 |
| `EQ_ERROR_INVALID_USER`                                                                                                                      | -256      | 401      | Authentication failure (invalid user or token). Equivalent to  HTTP 'Unauthorized'.                                                                    |
| `EQ_ERROR_UNKNOWN_SERVICE`                                                                                                                   | -512      | 503      | Server error : missing service. Equivalent to  HTTP 'Service Unavailable'.                                                                             |
| `EQ_ERROR_INVALID_CONFIG`                                                                                                                    | -1024     | 500      | Server error : faulty configuration. Equivalent to  HTTP 'Internal Server Error'.                                                                      |


Some checks are automatically performed based on context and configuration.
If a check fails, an HTTP response is returned with an error status and a body holding an error descriptor.

!!! note "Announcement property"
    When an error is raised inside a controller, if the `eQual::announce()` method is called in the invoked controller, in addition with the `error` property an additional `announcement` property is appended to the response to describe the expected format of the requests made to the controller.

### Logging technical information

The exception message returned to the client should remain generic. Information useful for diagnosis should be recorded separately in the logs.

A recommended practice is therefore to associate an exception with a more detailed log entry:

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

Calling `trigger_error()` with a supported reporting level such as `EQ_REPORT_ERROR` is equivalent to calling the reporter service directly:

```php
$reporter->error(
    "ORM::Capability denied {$operation} {$this->class}"
);
```

The first message contains the technical information required by the developer. The second remains sufficiently generic to be returned to the client.

Log entries are never exposed in the public HTTP response.

### Complete example

```php
if(!$this->hasCapability($operation)) {
    trigger_error(
        "ORM::Capability denied {$operation} {$this->class}",
        EQ_REPORT_ERROR
    );

    throw new \Exception(
        'capability_denied',
        EQ_ERROR_NOT_ALLOWED
    );
}
```

In this example:

- the log records the affected operation and class;
- the client only receives the generic `capability_denied` message;
- the `EQ_ERROR_NOT_ALLOWED` constant allows eQual to automatically determine the HTTP status code to return.


### Authorization Errors

`NOT_ALLOWED` error is raised when access-control rules or permissions deny the requested operation.

For the protected and private access checks shown below, eQual returns an HTTP 403 status.

For a controller announced with protected access:
```php
    'access' => [
        'visibility'        => 'protected',
    ]
```

a call made by a non-authenticated user will result in:
```json
{
    "errors": {
        "NOT_ALLOWED": "protected_operation"
    }
}
```


For a controller announced with `private` access:
```php
    'access' => [
        'visibility'        => 'private',
    ]
```

a call from a non CLI context will result in:
```json
{
    "errors": {
        "NOT_ALLOWED": "private_operation"
    }
}
```

---
