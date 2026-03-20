<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'description'   => "Create a new empty view as a json file, for a given entity.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
        'entity' => [
            'description'   => 'name of the entity',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' => [
            'description'   => 'id of the view',
            'type'          => 'string',
            'required'      => true
        ],
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context, 'orm' => $orm] = $providers;


$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}
// get the complete schema of the object (including special fields)
$schema = $model->getSchema();
$special_columns = array_keys($model::getSpecialColumns());

$parts = explode("\\", $params['entity']);

$package = array_shift($parts);
$entity = implode("/", $parts);

// Checking if package exists
if(!file_exists("packages/{$package}")) {
    throw new Exception('unknown_package', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
}

$parts = explode(".", $params['view_id']);

$view_type = array_shift($parts);
$view_name = array_shift($parts);

if(count($parts) > 0) {
    throw new Exception("view_id_invalid", QN_ERROR_INVALID_PARAM);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$view_type}.{$view_name}.json";

// prevent overwriting existing file
if(file_exists($file)) {
    throw new Exception('view_already_exists', QN_ERROR_INVALID_PARAM);
}

$path = dirname($entity);

if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")) {
    mkdir(QN_BASEDIR."/packages/{$package}/views/{$path}", 0777, true);
    if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")) {
        throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
    }
}

$views = [
    'list' => [
        "name" => "",
        "description" => "",
        "layout" => [
            "items" => []
        ]
    ],
    'form' => [
        "name" => "",
        "description" => "",
        "layout" => [
            "groups" => [
                [
                    "sections" => [
                        [
                            "rows" => [
                                [
                                    "columns" => [
                                        [
                                            "width" => "100%",
                                            "items" => []
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

$data = $views[$view_type];
$data["name"] = $model->getName();
$data["description"] = $model->getDescription();

$fields = array_filter($schema, function ($descriptor, $field) use ($special_columns) {
            return !in_array($field, $special_columns) && !in_array($descriptor['type'], ['many2many', 'one2many']);
        },
        ARRAY_FILTER_USE_BOTH
    );

foreach ($fields as $field => $descriptor) {

    $item = [
        "type" => "field",
        "value" => $field,
        "width" => "100%"
    ];

    switch($view_type) {
        case 'list':
            $item['width'] = strval(floor(100/count($fields))) . '%';
            $data["layout"]["items"][] = $item;
            break;
        case 'form':
            $data["layout"]["groups"][0]["sections"][0]["rows"][0]["columns"][0]["items"][] = $item;
            break;
    }
}

if(!file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
