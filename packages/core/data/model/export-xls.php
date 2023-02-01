<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use core\setting\Setting;
use core\User;

list($params, $providers) = announce([
    'description'   => "Returns a view populated with a collection of objects, and outputs it as an XLS spreadsheet.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to use (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'list.default'
        ],
        'domain' =>  [
            'description'   => 'Domain for filtering objects to include in the export.',
            'type'          => 'array',
            'default'       => []
        ],
        'controller' => [
            'description'   => 'Data controller to use to retrieve objects to print.',
            'type'          => 'string',
            'default'       => 'core_model_collect'
        ],
        'params' => [
            'description'   => 'Additional params to relay to the data controller.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' =>  [
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


list($context, $orm, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

$is_controller_entity = false;

/*
    Handle controller entities
*/
$parts = explode('\\', $params['entity']);
$file = array_pop($parts);
if(ctype_lower(substr($file, 0, 1))) {
    $is_controller_entity = true;
    $data = eQual::run('get', 'model_schema', [
            'entity'        => $params['entity']
        ]);
    $schema = $data['fields'];
}
/*
    Handle Model entities
*/
else {
    // retrieve target entity
    $entity = $orm->getModel($params['entity']);
    if(!$entity) {
        throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
    }

    // get the complete schema of the object (including special fields)
    $schema = $entity->getSchema();
}


// retrieve view schema
$view_schema = eQual::run('get', 'model_view', [
    'entity'        => $params['entity'],
    'view_id'       => $params['view_id']
]);

if(!isset($view_schema['layout']['items'])) {
    throw new Exception('invalid_view', QN_ERROR_INVALID_CONFIG);
}

// #todo - add support for group_by directive
$view_fields = [];
foreach($view_schema['layout']['items'] as $item) {
    if(isset($item['type']) && isset($item['value']) && $item['type'] == 'field') {
        $view_fields[] = $item;
    }
}

$fields_to_read = [];

// adapt fields to force retrieving name for m2o fields
foreach($view_fields as $item) {
    $field =  $item['value'];
    $descr = $schema[$field];
    if($descr['type'] == 'many2one') {
        $fields_to_read[$field] = ['id', 'name'];
    }
    else {
        $fields_to_read[] = $field;
    }
}

// entity is a controller (distinct from collect)
if($is_controller_entity) {
    // get data from controller
    $values = eQual::run('get', str_replace('\\', '_', $params['entity']), $params['params']);
}
// entity is a Model
else {
    if(in_array($params['controller'], ['model_collect', 'core_model_collect'])) {
        $limit = (isset($params['params']['limit']))?$params['params']['limit']:25;
        $start = (isset($params['params']['start']))?$params['params']['start']:0;
        $order = (isset($params['params']['order']))?$params['params']['order']:'id';
        $sort = (isset($params['params']['sort']))?$params['params']['sort']:'asc';
        if(is_array($order)) {
            $order = $order[0];
        }
        $values = $params['entity']::search($params['domain'], ['sort' => [$order => $sort]])->shift($start)->limit($limit)->read($fields_to_read)->get();
    }
    else {
        $body = [
                'entity'    => $params['entity'],
                'domain'    => $params['domain'],
                'fields'    => [],
                'limit'     => 25,
                'start'     => 0,
                'order'     => 'id',
                'sort'      => 'asc',
                'lang'      => $params['lang']
            ];

        foreach($params['params'] as $param => $value) {
            if($param == 'order' && is_array($value)) {
                $body['order'] = $value[0];
            }
            else {
                $body[$param] = $value;
            }
        }

        // retrieve objects collection using the target controller
        $data = eQual::run('get', $params['controller'], $body);
        // extract objects ids
        $objects_ids = array_map(function ($a) { return $a['id']; }, $data);
        // retrieve objects required fields
        $values = $params['entity']::ids($objects_ids)->read($fields_to_read)->get();
    }
}

/*
    Retrieve translation data, if any
*/

$translations = [];
try {
    $i18n = eQual::run('get', 'config_i18n', [
        'entity'        => $params['entity'],
        'lang'          => $params['lang']
    ]);
    if(!isset($i18n['model'])) {
        throw new Exception('invalid_translation', QN_ERROR_INVALID_CONFIG);
    }
    foreach($i18n['model'] as $field => $descr) {
        $translations[$field] = $descr;
    }
}
catch(Exception $e) {
    // ignore missing or invalid translation
}

// retrieve view title
$view_title = $view_schema['name'];
$view_legend = $view_schema['description'];
if(isset($i18n['view'][$params['view_id']])) {
    $view_title = $i18n['view'][$params['view_id']]['name'];
    $view_legend = $i18n['view'][$params['view_id']]['description'];
}


/*
    Fetch settings
*/

$settings = [
    'date_format'       => Setting::get_value('core', 'locale', 'date_format', 'm/d/Y'),
    'time_format'       => Setting::get_value('core', 'locale', 'time_format', 'H:i'),
    'format_currency'   => function($a) { return Setting::format_number_currency($a); }
];

$user = User::id($auth->userId())->read(['id', 'login'])->first(true);

$doc = new Spreadsheet();
$doc->getProperties()
      ->setCreator($user['login'])
      ->setTitle('Export')
      ->setDescription('Exported with eQual library');

$doc->setActiveSheetIndex(0);

$sheet = $doc->getActiveSheet();
$sheet->setTitle("export");


$column = 'A';
$row = 1;

// generate head row
foreach($view_fields as $item) {
    $field = $item['value'];

    $width = (isset($item['width']))?intval($item['width']):0;
    if($width <= 0) {
        // #todo - since group_by is not supported yet, we set the min with to 10%
        $width = 10;
        // continue;
    }
    $name = isset($translations[$field]['label'])?$translations[$field]['label']:$field;
    $sheet->setCellValue($column.$row, $name);
    $sheet->getColumnDimension($column)->setAutoSize(true);
    $sheet->getStyle($column.$row)->getFont()->setBold(true);
    ++$column;
}


foreach($values as $oid => $odata) {
    ++$row;
    $column = 'A';
    foreach($view_fields as $item) {
        $field = $item['value'];
        $width = (isset($item['width']))?intval($item['width']):0;
        if($width <= 0) {
            // #todo - since group_by is not supported yet, we set the min with to 10%
            $width = 10;
            //continue;
        }

        $value = $odata[$field];

        $type = $schema[$field]['type'];
        // #todo - handle 'alias'
        if($type == 'computed') {
            $type = $schema[$field]['result_type'];
        }

        $usage = (isset($schema[$field]['usage']))?$schema[$field]['usage']:'';
        $align = 'left';

        // for relational fields, we need to check if the Model has been fetched
        if(in_array($type, ['one2many', 'many2one', 'many2many'])) {
            // by convention, `name` subfield is always loaded for relational fields
            if($type == 'many2one' && isset($value['name'])) {
                $value = $value['name'];
            }
            else {
                $value = "...";
            }
            if(is_numeric($value)) {
                $align = 'right';
            }
        }
        else if($type == 'date') {
            // #todo - convert using PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel
            $align = 'center';
            $value = date($settings['date_format'], $value);
        }
        else if($type == 'time') {
            $align = 'center';
            $value = date($settings['time_format'], strtotime('today') + $value);
        }
        else if($type == 'datetime') {
            $align = 'center';
            $value = date($settings['date_format'].' '.$settings['time_format'], $value);
        }
        else {
            if($type == 'string') {
                $align = 'center';
            }
            else {
                if(strpos($usage, 'amount/money') === 0) {
                    $align = 'right';
                    $value = $settings['format_currency']($value);
                }
                if(is_numeric($value)) {
                    $align = 'right';
                }
            }
        }

        $allow_wrap = false;
        // handle html content
        if($type == 'string' && strlen($value) && $usage == 'text/html') {
            $align = 'left';
            $value = strip_tags(str_replace(['</p>', '<br />'], "\r\n", $value));
            $allow_wrap = true;
        }
        else {
            // translate 'select' values
            if($type == 'string' && isset($schema[$field]['selection'])) {
                if(isset($translations[$field]) && isset($translations[$field]['selection'])) {
                    $value = $translations[$field]['selection'][$value];
                }
            }
            $value =  $value;
        }

        $sheet->setCellValue($column.$row, $value);

        if($allow_wrap) {
            $sheet->getStyle($column.$row)->getAlignment()->setWrapText(true);
        }

        ++$column;
    }
}

$writer = IOFactory::createWriter($doc, "Xlsx");

ob_start();
$writer->save('php://output');
$output = ob_get_clean();

$context->httpResponse()
        ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->header('Content-Disposition', 'inline; filename="export.xlsx"')
        ->body($output)
        ->send();