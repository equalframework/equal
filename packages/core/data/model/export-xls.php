<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'description'   => 'List of unique identifiers of the objects to read.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' =>  [
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]        
    ],
    'response'      => [
        'accept-origin' => '*'        
    ],
    'providers'     => ['context', 'orm', 'auth'] 
]);


list($context, $orm, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

$entity = $params['entity'];

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

if(!isset($data['layout']['items'])) {
    throw new Exception('invalid_view', QN_ERROR_INVALID_CONFIG);
}

$fields = [];

foreach($data['layout']['items'] as $item) {
    if(isset($item['type']) && isset($item['value']) && $item['type'] == 'field') {
        $fields[] = $item['value'];
    }
}

$values = $params['entity']::search($params['domain'])->read($fields)->adapt('txt')->get();

// retrieve translation data, if any
$json = run('get', 'config_i18n', [
    'entity'        => $params['entity'], 
    'lang'          => $params['lang']
]);

// decode json into an array
$data = json_decode($json, true);
$translations = [];
if(!isset($data['errors']) && isset($data['model'])) {
    foreach($data['model'] as $field => $descr) {
        $translations[$field] = $descr['label'];
    }
}


$doc = new Spreadsheet();

$user = User::id($auth->userId())->read(['id', 'login'])->first();

$doc->getProperties()
      ->setCreator($user['login'])
      ->setTitle('Export')
      ->setDescription('Exported with equal lib');

$doc->setActiveSheetIndex(0);

$sheet = $doc->getActiveSheet(); 
$sheet->setTitle("export");


$column = 'A';
$row = 1;

foreach($fields as $field) {
    $name = isset($translations[$field])?$translations[$field]:$field;
    $sheet->setCellValue($column.$row, $name);
    $sheet->getColumnDimension($column)->setAutoSize(true);
    $sheet->getStyle($column.$row)->getFont()->setBold(true);
    ++$column;
}

foreach($values as $oid => $odata) {
    ++$row;
    $column = 'A';
    foreach($fields as $field) {
        $sheet->setCellValue($column.$row, $odata[$field]);
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