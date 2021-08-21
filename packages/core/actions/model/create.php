<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

/*
Usage examples :  

## PHP
```php 
<?php
use core\Group;
$group = Group::create(['name' => 'test group'])->first();
```

## CLI
```bash
equal.run --do=model_create --entity=core\group --fields[name]="test group"
```

*/

list($params, $providers) = announce([
    'description'   => "Create a new object using given fields values.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Associative array mapping fields to their related values.',
            'type'          => 'array', 
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

// fields and values have been received as a raw array : adapt received values according to schema
$schema = $orm->getModel($params['entity'])->getSchema();
foreach($params['fields'] as $field => $value) {
    // drop empty and unknown fields
    if(is_null($value) || !isset($schema[$field])) {
        unset($params['fields'][$field]);
        continue;
    }
    $params['fields'][$field] = $adapter->adapt($value, $schema[$field]['type']);
}

// fields for which no value has been given are set to default value (set in Model)
$instance = $params['entity']::create($params['fields'], $params['lang'])
            ->adapt('txt')
            ->first();

$result = [
    'entity' => $params['entity'], 
    'id'     => $instance['id']
];

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();