<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use core\setting\Setting;
use equal\orm\Domain;
use equal\orm\DateReference;

use core\User;

list($params, $providers) = announce([
    'description'   => "Returns a view populated with a collection of objects, and outputs it as an XLS spreadsheet.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'list'
        ],
        'domain' =>  [
            'description'   => 'Domain for filtering objects to include in the export.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' =>  [
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ],
        'params' => [
            'description'   => 'Additional params to relay to the data controller.',
            'type'          => 'array',
            'default'       => []
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

// we expect the controller to be core_model_chart

list($context, $orm, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];


// retrieve view schema
$json = run('get', 'model_view', [
    'entity'        => $params['entity'],
    'view_id'       => $params['view_id']
]);

// decode json into an array
$data = json_decode($json, true);

// relay error if any
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) {
        throw new Exception($message, qn_error_code($name));
    }
}

$view_schema = $data;

if(!isset($view_schema['layout'])) {
    throw new Exception('invalid_view', QN_ERROR_INVALID_CONFIG);
}

$controller = isset($view_schema['controller'])?$view_schema['controller']:'core_model_chart';

$body = $view_schema['layout'];
$body['mode'] = 'grid';

if(count($params['domain'])) {
    $domain = new Domain($params['domain']);
    if(isset($body['domain'])) {
        $domain_tmp = new Domain($body['domain']);
        $domain = $domain->merge($domain_tmp);
    }
    $body['domain'] = $domain->toArray();
}

if(isset($body['range_from'])) {
    $ref = new DateReference($body['range_from']);
    $body['range_from'] = date('c', $ref->getDate());
}

if(isset($body['range_to'])) {
    $ref = new DateReference($body['range_to']);
    $body['range_to'] = date('c', $ref->getDate());
}

foreach($params['params'] as $param => $value) {
    $body[$param] = $value;
}

// #memo - we run the controller under root context to use cache, if present
$values = eQual::run('get', $controller, $body, true);



// generate a virtual layout
$first = reset($values);
$fields = array_keys($first);
$view_fields = array_map(function($a) { return ['value' => $a]; }, $fields);

// generate a virtaul schema
// fields descriptors
$descriptors = array_merge( [['type' => 'string']], array_fill(0, count($fields)-1, ['type' => 'float']));
$schema = array_combine($fields, $descriptors);


/*
    Fetch settings
*/

$settings = [
    'date_format'       => Setting::get_value('core', 'locale', 'date_format', 'm/d/Y'),
    'time_format'       => Setting::get_value('core', 'locale', 'time_format', 'H:i'),
    'format_currency'   => function($a) { return Setting::format_number_currency($a); }
];



$doc = new Spreadsheet();

$user = User::id($auth->userId())->read(['id', 'login'])->first(true);

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

    $name = $field;
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

        // handle html content
        if($type == 'string' && strlen($value) && $usage == 'text/html') {
            $align = 'left';
            $$value = strip_tags(str_replace(['</p>', '<br />'], "\r\n", $value));
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