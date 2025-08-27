<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Creates a new i18n translation file, for a given entity.",
    'params'        => [

        'entity' => [
            'type'          => 'string',
            'description'   => "Name of the entity.",
            'required'      => true
        ],

        'lang' => [
            'type'          => 'string',
            'description'   => "Code of the desired lang.",
            'required'      => true
        ],

        'overwrite' => [
            'type'          => 'boolean',
            'description'   => "If true the file will be overwritten if it already exists.",
            'default'       => false
        ]

    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$parts = explode("\\", $params['entity']);
$package = array_shift($parts);
$entity = implode("/", $parts);
if(!file_exists("packages/$package")) {
    throw new Exception("unknown_package", EQ_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/$package/i18n")) {
    throw new Exception("missing_i18n_dir", EQ_ERROR_INVALID_CONFIG);
}

$lang = $params['lang'];
if(strpos($lang, '_') === false) {
    if(strlen($lang) !== 2) {
        throw new Exception("invalid_lang", EQ_ERROR_INVALID_PARAM);
    }
}
else {
    // lang can be a local (e.g., fr_BE)
    $lang_parts = explode('_', $lang);
    if(count($lang_parts) !== 2 || strlen($lang_parts[0]) !== 2) {
        throw new Exception("invalid_lang", EQ_ERROR_INVALID_PARAM);
    }
    if(strlen($lang_parts[1]) !== 2) {
        throw new Exception("invalid_country", EQ_ERROR_INVALID_PARAM);
    }
}

$file = EQ_BASEDIR."/packages/$package/i18n/$lang/$entity.json";
// prevent overwriting existing file
if(file_exists($file) && !$params['overwrite']) {
    throw new Exception("i18n_already_exists", EQ_ERROR_INVALID_PARAM);
}

$path = dirname($entity);
if(!is_dir(EQ_BASEDIR."/packages/$package/i18n/$lang/$path")) {
    mkdir(EQ_BASEDIR."/packages/$package/i18n/$lang/$path", 0777, true);
    if(!is_dir(EQ_BASEDIR."/packages/$package/i18n/$lang/$path")) {
        throw new Exception("file_access_denied", EQ_ERROR_UNKNOWN);
    }
}

$columns = $model::getColumns();

$model_data = [];
foreach($columns as $column => $data) {
    if(in_array($data['type'], ['one2many', 'many2many'])) {
        continue;
    }

    $model_data[$column] = [
        'label'         => '',
        'description'   => $data['description'] ?? '',
        'help'          => $data['help'] ?? ''
    ];

    if($data['selection']) {
        $selection = [];
        foreach($data['selection'] as $key => $sel) {
            if(is_string($key)) {
                $selection[$key] = $sel;
            }
            else {
                $selection[$sel] = '';
            }
        }

        $model_data[$column]['selection'] = $selection;
    }
}

$form_layout = [];
foreach($columns as $column => $data) {
    if (!in_array($data['type'], ['one2many', 'many2many'])) {
        continue;
    }

    $form_layout["section.$column"] = ['label' => ''];
}
$view = [
    'form.default' => [
        'name'          => '',
        'description'   => '',
        'layout'        => $form_layout
    ],
    'list.default' => [
        'name'          => '',
        'description'   => '',
        'layout'        => []
    ]
];

$error = [];

$result = [
    'name'          => $model::getName(),
    'plural'        => $model::getName().'s',
    'description'   => $model::getDescription(),
    'model'         => $model_data,
    'view'          => $view,
    'error'         => $error
];

if(!file_put_contents($file, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    throw new Exception("file_access_denied", EQ_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
