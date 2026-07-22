## Package utilities

### Package Consistency Checks

Before running tests, ensure the targeted package is consistent:

| **PATH**        | `core\actions\test\package-consistency.php`                                                                                  |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_package-consistency&package=myPackage`                                                                             |
| **CLI**         | `$ ./equal.run --do=test_package-consistency --package=myPackage`                                                            |
| **DESCRIPTION** | Consistency checks between DB and class as well as syntax validation for classes (PHP), views, and translation files (JSON). |

This utility ensures that your package is ready for testing and deployment. If it's not the case, see [**Writing the tests**](../../application-developers/core-development/entities-persistence/orm.md#writing-tests-for-entities).

> The level property has 3 options : 
>
> - **`error`** (ex: `missing property 'entity' in file:  "packages\/lodging\/views\/sale\booking\InvoiceLine.form.default.json"`);
> - **`warn`** (ex: `WARN  - I18 - Unknown field 'object_class' referenced in file "packages\/core\/i18n\/en\/alert\MessageModel.json")`;
> - **`*`** (error & warn).

#### `init_package`

| **PATH**        | `core\actions\init\package.php`                                                                                                                                             |
| --------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_package&package=core`                                                                                                                                             |
| **CLI**         | `$ ./equal.run --do=init_package --package=core`                                                                                                                            |
| **DESCRIPTION** | Initialize database for given package. If no package is given, initialize core package. Compile the apps (`apps folder`) of the package and copy them in the public folder. |

After creating a new class or modifying any `.class.php` model behavior in a package, reinitialize that package with `--force=true` so the database schema and generated package metadata are refreshed:

```bash
php run.php --do=init_package --package={package} --force=true
```

#### Package update scripts

Use update scripts for one-off package maintenance tasks that must run after a package has already been initialized, such as data migrations or repair operations that cannot be expressed only through schema initialization.

Create a timestamped empty update script with `config_generate-update`:

| **PATH**        | `core\actions\config\generate-update.php`                                      |
| --------------- | -------------------------------------------------------------------------------- |
| **URL**         | `?do=config_generate-update&package=myPackage&name=my-update`                    |
| **CLI**         | `$ ./equal.run --do=config_generate-update --package=myPackage --name=my-update` |
| **DESCRIPTION** | Create an empty PHP update script in `packages/{package}/init/updates/`.         |

The generated file name starts with a `YmdHis` timestamp, followed by `_` and the provided name, for example:

```text
packages/myPackage/init/updates/20260722121916_my-update.php
```

During package initialization, files from `packages/{package}/init/updates/` are copied to `packages/{package}/updates/`. On an already initialized instance, make sure the generated script is present in `packages/{package}/updates/` before running the updates controller.

To execute pending update scripts for an initialized package, run:

| **PATH**        | `core\actions\init\updates.php`                                      |
| --------------- | ----------------------------------------------------------------------- |
| **URL**         | `?do=init_updates&package=myPackage`                                    |
| **CLI**         | `$ ./equal.run --do=init_updates --package=myPackage`                   |
| **DESCRIPTION** | Execute pending PHP update scripts from `packages/{package}/updates/`. |

`init_updates` only considers PHP files whose first filename segment, split on `_` or `-`, is a `YmdHis` timestamp. The timestamp must be greater than the package initialization timestamp recorded in `log/packages.json`. Executed scripts are recorded in `log/updates.json`, so the same script is not executed again.

Update execution relies on two log files in `./log`:

- `log/packages.json` is created or updated by `init_package` after a successful package initialization. It maps each package name to the latest initialization timestamp, using the `date('c')` format, for example `{ "myPackage": "2026-07-22T14:31:32+00:00" }`. `init_updates` uses this timestamp as the baseline: if the file is missing, or if the package has no usable entry, no update script is eligible for execution. For compatibility with older instances, `init_updates` can also read an object entry that contains a string `first` value.
- `log/updates.json` is read by `init_updates` when it exists and is created on the first successful update script execution. It maps each package to the scripts already executed and the execution timestamp, for example `{ "myPackage": { "20260722121916_my-update.php": "2026-07-22T14:35:00+00:00" } }`. If this file is missing, `init_updates` treats all eligible scripts as pending. The `log` directory, and the file itself when already present, must be writable.

#### `init_seed`
| **PATH**        | `core\actions\init\seed.php`                                                       |
| --------------- | ---------------------------------------------------------------------------------- |
| **URL**         | `?do=init_seed&package=core`                                                       |
| **CLI**         | `$ ./equal.run --do=init_seed --package=core`                                      |
| **DESCRIPTION** | Seed objects for package using json configuration files in "{package}/init/seed/". |

#### `init_anonymize`
| **PATH**        | `core\actions\init\anonymize.php`                                                |
| --------------- | -------------------------------------------------------------------------------- |
| **URL**         | `?do=init_anonymized&package=core`                                               |
| **CLI**         | `$ ./equal.run --do=init_anonymized --package=core`                              |
| **DESCRIPTION** | Anonymize objects using json configuration files in "{package}/init/anonymize/". |

#### `test_package`

| **PATH**        | `core\actions\test\package.php`                                                                                                 |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_package&package=core`                                                                                                 |
| **CLI**         | `$ ./equal.run --do=test_package --package=core`                                                                                |
| **DESCRIPTION** | This controller checks the presence of test units for a given package and runs them, if any. See [Tests & coverage](../../community/contribution-guide/tests-coverage.md). |

## Seeding with Command Line

The action controller `core_init_seed` allows you to automatically generate objects for a specific package according to the schemas (JSON files) present in `{package}/init/seed/`.

### Action Parameters

| **Param**       | **Description**                                              |
| --------------- | ------------------------------------------------------------ |
| **package**     | The name of the package to seed.                             |
| **config_file** | If given, only the specified config file will be considered. |

### Example Usage

```bash
$ ./equal.run --do=init_seed --package=myPackage
```

## Objects Generation with Command Line

The action controller `core_model_generate` allows you to generate objects for a specific entity with both given and random data.

### Example Usage

```bash
$ ./equal.run --do=model_generate --entity='core\User' --qty=5
```

### Action Parameters

| **Param**     | **Description**                                                                  |
| ------------- | -------------------------------------------------------------------------------- |
| **entity**    | Specifies the fully qualified name of the model class to be seeded. *(required)* |
| **qty**       | Number of records to create.                                                     |
| **fields**    | Associative array mapping fields to their related values.                        |
| **relations** | Relational fields descriptor.                                                    |

## Managing Dependencies with Composer

eQual uses Composer to manage dependencies. Composer allows you to easily add, update, and remove libraries in your project. Dependencies are stored in the `/vendor` folder, and the `composer.json` file defines the required libraries and their versions.

To add a new dependency, use the `composer require` command. For example:

```bash
$ composer require swiftmailer/swiftmailer
```

To update all dependencies, run:

```bash
$ composer update
```

To remove a dependency, use:

```bash
$ composer remove <library-name>
```

After modifying dependencies, always run the following command to ensure eQual recognizes the changes:

```bash
$ ./equal.run --do=init_composer
```

---
