# Common Issues

- Missing or invalid constants in `config.json`.
- Overridden constants not listed by the controller.

To debug configuration issues, ensure that all constants are correctly defined and validated.

## Debugging in Development Workflow

When developing or troubleshooting, always enable detailed logging and error reporting in your development environment. Use the built-in logging tools to trace configuration loading, controller execution, and ORM/database queries. If you encounter unexpected behavior, check the logs for warnings or errors, and use the debugger to step through the code. Make sure to clear any cached configuration or compiled files after making changes to ensure your updates are applied. For persistent issues, compare your configuration files with the provided examples and consult the documentation for known issues and solutions.

## Security Considerations for ORM Usage

Direct use of the ORM in controllers (or entity event handlers) is permitted (as is the use of the DbManipulator service), but it should be done with caution as it poses a potential security risk. Additionally, controllers that inject the ORM service generate a warning during package integrity checks.

Furthermore, there are no logs at this level and no user concept (if fields do not contain a creator/modifier, it is assumed to be the super-user).

---