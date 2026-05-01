# Pipelines Utilities

This section provides advanced examples and utilities for working with pipelines in the eQual framework.

## Response Signature

Each controller in a pipeline declares its response using the following signature:

```php
"response" => [
  "content-type" => "application/json",
  "schema" => [
    "type"   => "entity" | "any" | scalar type,
    "usage"  => "{usage notation}",
    "qty"    => "one" | "many",
    "entity" => "core\User",
    "values" => []
  ]
]
```

### Examples

#### `model_collect`

```php
<?php
"schema" => [
  "type" => "any",
  "qty"  => "many"
]
```
This controller returns an array of arbitrary JSON objects.

#### `model_search`

```php
<?php
"schema" => [
  "type" => "integer",
  "qty"  => "many"
]
```
This controller returns an array of integers, often used for IDs.

---