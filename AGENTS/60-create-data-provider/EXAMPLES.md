- Minimalist data provider example:
```php
<?php

use mypackage\MyEntity;

[$params, $providers] = eQual::announce([
    'description' => 'Return a minimal payload for one entity.',
    'params' => [
        'id' => [
            'type' => 'many2one',
            'foreign_object' => 'mypackage\MyEntity',
            'required' => true
        ]
    ],
    'response' => [
        'content-type' => 'application/json',
        'charset' => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers' => ['context']
]);

['context' => $context] = $providers;

$object = MyEntity::id($params['id'])
    ->read(['id', 'name'])
    ->adapt('json')
    ->first(true);

if(!$object) {
    throw new Exception('unknown_object', EQ_ERROR_INVALID_PARAM);
}

$context->httpResponse()
    ->body($object)
    ->send();
```