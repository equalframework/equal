# Exception Handling and Error Codes

## Exception and Throwable

eQual natively handles exceptions (or `Throwable` objects) as errors and generates the appropriate HTTP response accordingly.

While it remains possible to use try/catch blocks, any "throwable" that is not explicitly handled by a try/catch block, will by handled by eQual. 

Internally, every raised error/exception is either handled in controllers, or caught by the `run()` method and is eventually turned into a HTTP error code.

In order to generate an error Response, any controller may throw an Exception.

```
    throw new Exception('error_msg_id', EQ_ERROR_{CODE});
```

The error codes are defined inside the `eq.lib.php` file.

EQ_ERROR_{CODE}

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

!!!Note Announcement property
    When an error is raised inside a controller, if the `eQual::announce()` method is called in the invoked controller, in addition with the `error` property an additional `announcement` property is appended to the response to describe the expected format of the requests made to the controller.


### Authorization Errors

`NOT_ALLOWED` error is raised when a user is not authenticated or has not enough rights to perform the requested operation.

All errors relating to authentication error, are returned with a HTTP 403 status.

For a controller announced with protected access;
```
    'access' => [
        'visibility'        => 'protected',
    ]
```

a call made by a non-authenticated user will result in:
{
    "errors": {
        "NOT_ALLOWED": "protected_operation"
    }
}


For a controller announced with `private` access;
```
    'access' => [
        'visibility'        => 'private',
    ]
```

a call from a non CLI context will result in:
{
    "errors": {
        "NOT_ALLOWED": "private_operation"
    }
}

---