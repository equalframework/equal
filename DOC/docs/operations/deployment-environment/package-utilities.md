## Package utilities

### Package Consistency Checks

Before running tests, ensure the targeted package is consistent:

| **PATH**        | `core\actions\test\package-consistency.php`                                                                                  |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_package-consistency&package=myPackage`                                                                             |
| **CLI**         | `$ ./equal.run --do=test_package-consistency --package=myPackage`                                                            |
| **DESCRIPTION** | Consistency checks between DB and class as well as syntax validation for classes (PHP), views, and translation files (JSON). |

This utility ensures that your package is ready for testing and deployment. If it's not the case, see [**Writing the tests**](../../application-developpers/core-development-guide/models/orm.md/#writing-tests-for-entities).

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
| **DESCRIPTION** | This controller checks the presence of test units for a given package and runs them, if any. (page :['Testing'](./testing.md)). |

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