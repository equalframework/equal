# Using Collections

This section explains how to create, manipulate, and perform operations on collections.

---

## Creating a Collection

To create a collection, use the `::create()` method:

```php
$collection = MyEntity::create();
```

---

## Common Operations on Collections

### Exporting Data

Collections can be exported as arrays or maps for further processing:

```php
$array = $collection->get($to_array = true);
```

```php
$map = $collection->get();
```

---

### Accessing Elements

Retrieve the first element in a collection:

```php
$first = $collection->first();
```

---

### Bulk Updates

Apply updates to all objects in the collection:

```php
$collection->update(['status' => 'archived']);
```

---

### Deleting Objects

Delete all objects in a collection:

```php
$collection->delete();
```

---

## Advanced Operations

### Filtering

Filter collections using domains:

```php
$filtered = MyEntity::search(['status' => 'active']);
```

---

### Aggregating Data

Perform aggregate operations like counting or summing fields:

```php
$count = $collection->count();
```

```php
// Sum the 'amount' field for all objects in the collection
$sum = 0;
$collection->each(function($id, $object) use (&$sum) {
    $sum += $object['amount'];
});
```

---

### Chaining Methods

Combine multiple operations for concise and readable code:

```php
$results = MyEntity::search(['status' => 'active'])
    ->orderBy('name', 'asc')
    ->read(['id', 'name']);
```

For more on domains and filtering, see [Domain Filtering](../domains.md).

---

