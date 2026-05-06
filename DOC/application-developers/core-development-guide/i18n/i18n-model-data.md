# Model Data Translation

Any non-relational field can be translated. The [ORM](../models/orm.md) uses a dedicated Model `core\Translation` to store the translations of terms.

When a field is marked as `multilang` in its definition, the values of its translations can be managed directly via CRUD operations.

---

## The `core\Translation` Object

Beneath the surface, eQual stores translations in the `core_Translation` table.

```php
<?php
/*...*/
class Translation extends Model {
  public static function getColumns() {
    return [
        'language'     => ['type' => 'string', 'usage' => 'language/iso-639:2'],
        'object_class' => ['type' => 'string'],
        'object_field' => ['type' => 'string'],
        'object_id'    => ['type' => 'integer'],
        'value'        => ['type' => 'binary']
    ];
  }
}
```

For the `value` field, the SQL `MEDIUMBLOB` type is used (max size of ~16MB). This allows translating simple text fields (`string`, `text`) as well as binary data (e.g., a PDF document specific to a language).

---

## Enabling `multilang` in Entities

i18n is applied on a field-by-field basis. To allow storage of multiple translations for a specific field, set the `multilang` property to `true` in the class definition.

**Example**:
```php
<?php
'description' => [
    'type' => 'string', 
    'multilang' => true
]
```

---

## Manipulating Translated Data

The language context is passed as a parameter to the ORM methods. If no language is specified, the system defaults to the `DEFAULT_LANG` configuration.

### Reading Data (GET)

To retrieve a specific translation, pass the language code as the second argument to the `read` method.

```php
<?php
$lang = 'en';
MyClass::search()
        ->read(['name', 'description'], $lang)
        ->get(true);
```

### Writing Data (UPDATE/CREATE)

Just like reading, writing involves passing the target language to the `update` or `create` methods.

**Updating a specific language:**

```php
<?php
// Update the French translation
MyClass::ids($id)
        ->update($fields, 'fr');
```

**Creating an object with multiple translations:**

To create an object with translations in multiple languages immediately, you must chain the operations. The initial `create` establishes the object, and subsequent `update` calls add the additional languages.

```php
<?php
$fields_fr = ['description' => 'Bonjour'];
$fields_en = ['description' => 'Hello'];

// 1. Create the object with the primary language (e.g. French)
$object = MyClass::create($fields_fr, 'fr')
        ->read(['id'])
        ->first();

// 2. Add the English translation to the newly created ID
MyClass::ids($object['id'])->update($fields_en, 'en');
```

!!! note
    You do NOT need to specify a language for a `delete` request. Deleting the entity removes all associated translations automatically.

---