# ORM

eQual comes with an Object-Relational Mapper (ORM) that greatly eases interactions with the database by handling data conversions, class inheritance, and object relations.

The ORM service is dedicated to low-level operations on entities. It handles all tasks related to object search and manipulation, offering an abstract layer for DBMS queries.

It primarily allows for searching, retrieving, and modifying lists of objects (Model). Operations performed by the ORM are not subject to constraints related to application logic consistency, user permissions, event handling, field calculation, or entity relationship consistency.

All checks (permissions, workflow, data validation, etc.) are exclusively handled via Collections.

Controllers mostly require high-level manipulations (including data conversion, validation, and permission checks) and therefore use Collections.

However, direct use of the ORM in controllers (or entity event handlers) is permitted (as is the use of the DBManipulator service), but it should be done with caution as it poses a potential security risk. Additionally, controllers that inject the ORM service generate a warning during package integrity checks.

Furthermore, there are no logs at this level and no user concept (if fields do not contain a creator/modifier, it is assumed to be the super-user).

The ORM implements methods allowing to:

- **create**, **update**, or **delete** one or more objects (based on the ID field)
- **retrieve** a single entity or a list of entities (both based on the ID field)
- **retrieve** a list of IDs of entities that match some criteria

---

## Object Definition

### Models

In the ORM system, object definitions are structured using classes that inherit from `equal\orm\Model`. This base class provides methods and properties that are common to all objects within the system.

#### Fields Definition

Each model class must implement at least one mandatory method: `getColumns()`. This method is essential as it defines the model fields.

The `getColumns()` method returns an associative array mapping field names to their definitions. This method allows for the specification of each field's characteristics, such as type, default values, constraints, and any other relevant attributes.

Below is a basic implementation of the `getColumns()` method:

```php
<?php
[...]
    
public static function getColumns() {
    // Returns an empty array by default
    return [];
}
```

---

## Default Values

The `default` property of a field allows automatically assigning a default value when creating a new object. The framework supports scalar values, closures, and references to class methods for defining default values.

### Scalar Values

Scalar values include numbers, strings, and results from PHP function calls that are evaluated when the class is parsed. In this case, there is no special handling, and all created instances will have the value (or the result of the function) as assigned during the class declaration. This assignment of default values occurs when the ORM first retrieves the class definition.

For example:

```php
<?php 
[...]
    
'day' => [
    'type'    => 'date',
    'default' => time()
]
```

This will generate instances with the same timestamp for all subsequent calls within the same thread.

### Closures

Using a closure allows for dynamic generation of default values, where a new value can be determined each time a new instance is created. However, this method is limited to basic functionalities and does not allow for the retrieval of contextual information (like services, current user, etc.).

Example of using a closure:

```php
<?php 
[...]
    
'day' => [
    'type'    => 'date',
    'default' => function () { return time(); }
]
```

This configuration will generate instances with a different timestamp for each creation of a new instance.

### Class Methods

Default values can also be defined by referencing a class method. This method can dynamically compute the default value based on additional context such as the current user.

For instance:

```php
<?php 
[...]
    
'user_id' => [
    'type'           => 'many2one',
    'foreign_object' => 'core\User',
    'default'        => 'defaultUserId'
]

public static function defaultUserId($self, $auth) {
    return $auth->userId();
}
```

In this example, the default value for the `user_id` field is dynamically obtained from the `defaultUserId` method, which utilizes the current authentication service to fetch the user ID.

### Settings

Default values for fields can be dynamically defined using a Setting value. This mechanism allows eQual to retrieve default values directly from the settings stored in the database. To enable this functionality, configure the field's `default` value as `defaultFromSetting`.

If the corresponding Setting is not found in the database, the `setting_default` value will act as a fallback.

Example of using a setting:

```php
<?php
[...]

'printer_type' => [
    'type'            => 'string',
    'selection'       => [
        'pos-80',
        'iso-a4'
    ],
    'description'     => 'Printer format to be used for Point Of Sale tickets.',
    'default'         => 'defaultFromSetting',
    'setting_default' => 'iso-a4'
]
```

For eQual to find the Setting, it must be part of the *default* `SettingSection` and follow a naming convention for the `Setting` code field.

| Field   | Value                      |
| ------- | -------------------------- |
| package | identity                   |
| section | default                    |
| code    | center_office.printer_type |

The code is composed of:

- The namespace (without the package) in snake case and dotted instead of slashed (e.g., `core\alert\MessageModel` becomes `alert.message_model`)
- The field name prefixed by a dot

In that case, if the Setting `identity.default.center_office.printer_type` does not exist, `iso-a4` will be used as default.

This approach provides greater flexibility in assigning default values to fields, allowing adjustments to be made dynamically via database settings without requiring code changes. This eliminates the need for redeployment or manual code modifications when default values need to be updated, streamlining maintenance and enhancing adaptability.

---

## Classes Inheritance

Classes can extend other classes, potentially from different packages, to add new fields or customize behavior.

When a class inherits from another, objects instantiated from this class will contain not only the fields defined in the class itself but also all the fields defined in its ancestor classes. There can be multiple levels of inheritance.

All model classes inherit from the class `equal\orm\Model`.

### Object Storage

To store objects, the ORM utilizes a dedicated table in the database following the active record pattern. By convention, the name of the table for a given class is derived from the name of the first ancestor class that extends `equal\orm\Model`, with the full namespace converted to snake case. For example, objects of the class `realestate\RentalUnit` are stored in the `realestate_rentalunit` table.

However, it is also possible to have inheritance that is distinct from the table assignment in the database.

For example, the classes `sale\customer\Customer` and `identity\Contact` both inherit from the class `identity\Partner`. However, they are distinct objects which are preferable not to mix. In such cases, it is possible to manually define the table to be used for a class via the `getTable()` method.

### Management of Relationships

The ORM automatically manages relationships between entities, with dynamic support for overrides, avoiding the need to manually redefine relational fields.

#### Dynamic Entity Creation

When eQual encounters a request for an entity that does not yet exist, but whose namespace corresponds to a parent entity, the framework dynamically generates an empty entity that extends this parent entity. This ensures that relationships between entities remain consistent, even without explicitly overriding the fields. The framework follows a standard naming convention by automatically subclassifying entities under the schema `my\package\name\space\Class`.

##### Direct Relationships

In a direct relationship between two entities:

```php
A {
  b: rel_B
}

B {
  a: rel_A
}

my\A extends A {
  
}

my\B extends B {
  
}
```

When an entity `my\A` requests the property `b`, the framework automatically returns an object of class `my\B`.

##### Indirect Relationships

In the case of indirect relationships, where an intermediate entity links two other entities:

```php
A {
  c: rel_C
}

B {
}

C {
  b: rel_B
}

my\B extends B {
  other: any
}

my\C extends C {
}

my\A extends A {
}
```

When the relationship `c.b` is requested from the entity `my\A`, the ORM will return an object of class `my\B`, allowing access to the additional field `other` defined in class `my\B`.

#### Namespace Handling in Relationships

When processing relational fields, the ORM automatically checks the namespace of the related entities. If part of the namespace does not directly match the package of the processed entity but refers to an existing package, the framework prefixes the current processing package to the namespace. This ensures that custom entities are used when necessary.

#### Resolution of Parent Entities

If a custom entity does not exist in a given namespace, the ORM falls back to the parent entity. This ensures the consistency of the relational schema and prevents inconsistencies when extended entities are involved.

#### Advantages

- **Transparent entity overriding**: It is not necessary to manually override entities to maintain relationship consistency
- **Relational consistency**: Dynamically generated entities retain correct relationships with their parent entities, ensuring access to all properties and methods, even in complex relationships
- **Simplicity in view overriding**: Views can be overridden without requiring entity rewriting, facilitating changes in user interfaces and data relationships

#### Constraints

- **Naming conventions**: Entity overrides must follow the framework's naming convention, such as `package\name\space\Class` and `my\package\name\space\Class`
- **Namespace collisions**: There can be no namespace collisions; if the second part of a class's namespace matches a package name, then this class **must** be inherited from a parent class within that package

### Impact on Translations

Inheritance affects translation files as well. All fields are eligible for providing a translation. If some fields are common between a class and its ancestor, they can be overridden. If not, the translations from the first ancestor declaring a translation will be utilized.

If a JSON file is absent for a given class, the system returns the first available JSON file from the class ancestors. For example, if no translation file is defined for `lodging\realestate\RentalUnit`, then the translation file for `realestate\RentalUnit` will be returned.

### Impact on Views

The inheritance system influences how views are managed. When a view is related to a class that inherits from another, the view can leverage all the properties and methods available from the ancestor classes. This allows for a more flexible and powerful user interface design, accommodating extended functionality as defined by the inheritance chain.

---

## Value Generation for Seeding

When seeding is executed, each entity field can receive a value based on different strategies. For more details on model generation, see the model generation controller in generate.php.

### General Logic

1. **Generation Function**: If the entity has a specific method to generate the field's value, it is used:
   ```php
   if ($field_has_generate_function && method_exists($params['entity'], $field_descriptor['generation'])) {
       $new_entity[$field] = $params['entity']::{$field_descriptor['generation']}();
   }
   ```

2. **Random Value**:

      * If the field is **not required**, there is a **5% chance** it will be left empty (`null`)

      * Otherwise, the value is dynamically generated based on the **field type**

### Generation Based on Field Type

#### Based on Usage

If the field has a specific usage (e.g., `email`, `phone`, `name`), the `UsageFactory` is used to generate an appropriate value:

```php
if (isset($field_descriptor['usage'])) {
    try {
        $usage = UsageFactory::create($field_descriptor['usage']);
        return $usage->generateRandomValue();
    } catch (\Exception $e) {
        // Usage issue
    }
}
```

#### Based on Field Name

Some well-known fields are associated with specific methods:

| Field Name        | Associated Method        |
| ----------------- | ------------------------ |
| `username`        | `self::username()`       |
| `firstname`       | `self::firstname($lang)` |
| `lastname`        | `self::lastname($lang)`  |
| `fullname`        | `self::fullname($lang)`  |
| `legal_name`      | `self::legalName($lang)` |
| `address_street`  | `self::addressStreet()`  |
| `address_zip`     | `self::addressZip()`     |
| `address_city`    | `self::addressCity()`    |
| `address_country` | `self::addressCountry()` |

#### Based on Field Type

If the field does not match a specific case, a random value is generated based on its type:

| Field Type | Associated Method                | Description                                       |
| ---------- | -------------------------------- | ------------------------------------------------- |
| `string`   | `self::plainText()`              | Generates random text                             |
| `boolean`  | `self::boolean()`                | Randomly returns `true` or `false`                |
| `integer`  | `self::integerByLength(9)`       | Generates a random integer (9 digits)             |
| `float`    | `self::realNumberByLength(9, 2)` | Generates a floating-point number with 2 decimals |

---