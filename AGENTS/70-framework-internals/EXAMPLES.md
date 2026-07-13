# Framework internals examples

## ORM behavior change

Relevant files:

- `lib/equal/orm/ObjectManager.class.php`
- `lib/equal/orm/Collection.class.php`
- `packages/core/tests/lifecycle.php`

Typical validation:

```powershell
php -l lib/equal/orm/ObjectManager.class.php
php -l lib/equal/orm/Collection.class.php
php run.php --do=test_db-access
php packages/core/tests/lifecycle.php
```

If the lifecycle test is not executable directly in the current environment, report the exact command attempted and the failure reason.

## Auth behavior change

Relevant files:

- `lib/equal/auth/AuthenticationManager.class.php`
- `public/*.php`
- auth-related controllers or tests discovered with `rg`

Typical validation:

```powershell
php -l lib/equal/auth/AuthenticationManager.class.php
php -l public/console_old.php
php run.php --get=core_config_controllers --package=core
```

Use a safer targeted smoke test when the changed auth path requires credentials, sessions, or external services.

## Public entry point compatibility

Relevant files:

- `public/*.php`
- root bootstrap files
- routing/controller documentation

Typical validation:

```powershell
php -l public/console_old.php
php run.php --help
```

Do not remove legacy entry points unless the user explicitly asks for that cleanup.
