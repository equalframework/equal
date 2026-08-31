# Test package

This package contains reusable definitions that exist only to support the
framework suites in `packages/core/tests` during development and CI/CD.

- Production packages must not depend on `test`.
- Test-only ORM models belong in `classes/`.
- Test-only action handlers and data providers belong in `actions/` and `data/`.
- Test suites do not belong in this package; they remain in `packages/core/tests`.
- Generic test runners and consistency controllers remain in `core` so their
  public controller names stay stable.

Initialize and run the package with:

```powershell
php run.php --do=init_package --package=test --force=true
php run.php --do=core_test_package --package=core
```
