<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

/*
Usage examples :

## PHP
```php
<?php
use core\Group;
$group = Group::create(['name' => 'test group'])->first(true);
```

## CLI
```bash
./equal.run --do=model_create --entity=core\\group --fields[name]="test group"
./equal.run --do=model_create --entity=core\\User --fields="{login:'test@example.com'}"
```

*/

use equal\orm\Field;

[$params, $providers] = eQual::announce([
    'description'   => "Create a new object using given fields values.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'required'      => true
        ],
        'fields' => [
            'type'          => 'array',
            'description'   => 'Associative array mapping fields to their related values.',
            'default'       => []
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Specific language for multilang field.',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\data\DataAdapterProvider $dap
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap] = $providers;

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

// fields and values have been received as a raw array : adapt received values according to schema
$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$schema = $model->getSchema();

try {
    foreach($params['fields'] as $field => $value) {
        if(!isset($schema[$field])) {
            continue;
        }
        // drop empty and unknown fields
        if(is_null($value) || !isset($schema[$field])) {
            unset($params['fields'][$field]);
            continue;
        }
        /** @var equal\orm\Field */
        $f = $model->getField($field);
        $params['fields'][$field] = $adapter->adaptIn($value, $f->getUsage());
    }
}
catch(Exception $e) {
    $msg = $e->getMessage();
    // handle serialized objects as message
    $data = @unserialize($msg);
    if ($data !== false) {
        $msg = $data;
    }
    throw new \Exception(serialize([$field => $msg]), $e->getCode());
}


// fields for which no value has been given are set to default value (set in Model)
$instance = $params['entity']::create($params['fields'], $params['lang'])
    ->adapt('json')
    ->first(true);

$result = [
    'entity' => $params['entity'],
    'id'     => $instance['id']
];

$context
    ->httpResponse()
    ->status(201)
    ->body($result)
    ->send();