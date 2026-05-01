## Data manipulation utilities

### Building a Test Data Set

To ensure consistent test results, use a predefined data set. Follow these steps:

1. Write the data you need in your database.
2. Export the data as a `.sql` file.
3. Place the file in your package's `init` folder.
4. Modify the SQL file to include only `INSERT INTO` statements.

Run the following command to initialize the data set:

```bash
php run.php --do=init_package --package=mypackage
```

This ensures your database is in the desired state before running tests.

### Models meta data

#### `model_schema`

| **PATH**        | `core\data\model\schema.php`                           |
| --------------- | ------------------------------------------------------ |
| **URL**         | `?get=model_schema&entity=core\User`                   |
| **CLI**         | `$ ./equal.run --get=model_schema --entity=core\\User` |
| **DESCRIPTION** | Returns the schema of given class (model) in JSON.     |

#### `model_view`

| **PATH**        | `core\data\model\view.php`                                                  |
| --------------- | --------------------------------------------------------------------------- |
| **URL**         | `?get=model_view&entity=core\User&view_id=list.default`                     |
| **CLI**         | `$ ./equal.run --get=model_view --entity=core\\User --view_id=list.default` |
| **DESCRIPTION** | Returns the JSON view related to an entity (class model), given a view ID.  |

### Manipulate data

#### `model_create`

| **PATH**        | `core\actions\model\create.php`                                             |
| --------------- | --------------------------------------------------------------------------- |
| **URL**         | `?do=model_create&entity=core\Group&fields[name]=Admin`                     |
| **CLI**         | `$ ./equal.run --do=model_create --entity=core\\Group --fields[name]=Admin` |
| **DESCRIPTION** | Create a new object using given fields values.                              |

#### `model_generate`

| **PATH**        | `core\actions\model\generate.php`                                             |
| --------------- | ----------------------------------------------------------------------------- |
| **URL**         | `?do=model_generate&entity=core\Group&fields[name]=Admin`                     |
| **CLI**         | `$ ./equal.run --do=model_generate --entity=core\\Group --fields[name]=Admin` |
| **DESCRIPTION** | Generate a new object with random data and given values.                      |

#### `model_anonymize`

| **PATH**        | `core\actions\model\anonymize.php`                                                                 |
| --------------- | -------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=model_anonymize&entity=core\Group&fields[name]=Group1&domain=[id,=,15]`                       |
| **CLI**         | `$ ./equal.run --do=model_anonymize --entity=core\\Group --fields[name]=Group1 --domain=[id,=,15]` |
| **DESCRIPTION** | Anonymize an existing object with random data and given values.                                    |

#### `model_update`

| **PATH**        | `core\actions\model\update.php`                                                    |
| --------------- | ---------------------------------------------------------------------------------- |
| **URL**         | `?do=model_update&entity=core\Group&id=45&fields[name]=Worker`                     |
| **CLI**         | `$ ./equal.run --get=model_view --entity=core\\User --id=45 --fields[name]=Worker` |
| **DESCRIPTION** | Update (fully or partially) the given object.                                      |

#### `model_delete`

| **PATH**        | `core\actions\model\delete.php`                                 |
| --------------- | --------------------------------------------------------------- |
| **URL**         | `?do=model_delete&entity=core\Group&id=48`                      |
| **CLI**         | `$ ./equal.run --get=model_delete --entity=core\\Group --id=48` |
| **DESCRIPTION** | Deletes the given object(s).                                    |

#### `model_search`

| **PATH**        | `core\data\model\search.php`                                                                                        |
| --------------- | ------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?get=model_search&entity=core\Group&domain=[name,=,Admin]`                                                         |
| **CLI**         | `$ ./equal.run --get=model_search --entity=core\\Group --domain=[name,=,Admin]`                                     |
| **DESCRIPTION** | Returns a list of identifiers of a given entity, according to given domain (filter), start offset, limit and order. |

#### `model_read`

| **PATH**        | `core\data\model\read.php`                                                                       |
| --------------- | ------------------------------------------------------------------------------------------------ |
| **URL**         | `?get=model_read&entity=core\Group&fields=[created,description]&ids=[1,2]`                       |
| **CLI**         | `$ ./equal.run --get=model_read --entity=core\\Group --fields=[created,description] --ids=[1,2]` |
| **DESCRIPTION** | Returns values map of the specified fields for object matching given class and identifier.       |

#### `model_collect`

| **PATH**        | `core\data\model\collect.php`                                                                             |
| --------------- | --------------------------------------------------------------------------------------------------------- |
| **URL**         | `?get=model_collect&entity=core\Group&fields=[created,description]&domain=[id,=,1]`                       |
| **CLI**         | `$ ./equal.run --get=model_collect --entity=core\\Group --fields=[created,description] --domain=[id,=,1]` |
| **DESCRIPTION** | Returns a list of entities according to given domain (filter), start offset, limit and order.             |

---

### Anonymization utilities

##### `init_anonymize` 

| **PATH**        | `core\actions\init\anonymize.php`                  |
| --------------- | -------------------------------------------------- |
| **URL**         | `?do=init_anonymize&package=core`                  |
| **CLI**         | `$ ./equal.run --do=init_anonymize --package=core` |
| **DESCRIPTION** | Anonymizes all objects of a given package.         |

| **Param**       | **Description**                                |
| --------------- | ---------------------------------------------- |
| **package**     | Name of the package to anonymize.              |
| **config_file** | (Optional) Specific configuration file to use. |

##### `model_anonymize `

| **PATH**        | `core\actions\model\anonymize.php`                                                      |
| --------------- | --------------------------------------------------------------------------------------- |
| **URL**         | `?do=model_anonymize&entity=core\User&fields=[firstname,lastname]`                                                                                  |
| **CLI**         | `$ ./equal.run --do=model_anonymize --entity='core\User' --fields=[firstname,lastname]` |
| **DESCRIPTION** | Anonymizes objects of a given package for a given domain.                               |

| **Param**     | **Description**                                             |
| ------------- | ----------------------------------------------------------- |
| **entity**    | Name of the entity to anonymize.                            |
| **fields**    | Fields to anonymize.                                        |
| **relations** | Descriptor for handling relationships (many2one, one2many). |
| **lang**      | Language for multilingual fields.                           |
| **domain**    | Criteria for filtering objects to anonymize.                |

