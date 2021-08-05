<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;

list($params, $providers) = announce([
    'description'   => "Returns a view populated with a collection of objects and outputs it as a PDF document.",
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
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [$providers['context'], $providers['orm']];

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
$widths = [];

$default_width = round(100.0 / count($data['layout']['items']), 2);

foreach($data['layout']['items'] as $item) {
    if(isset($item['type']) && isset($item['value']) && $item['type'] == 'field') {
        $field = $item['value'];
        
        $width = isset($item['width'])?intval($item['width']):$default_width;
        $widths[$field] = $width;
        // add only fields with non null width
        if($width > 0) {
            $fields[] = $field;
        }

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


$css = "
@page {
    margin: 1cm;
}
body {
    font-family:sans-serif;
    font-size: 0.7em;
    color: #000000;
}
table {
    table-layout: fixed;
    border: 0;
}
table, tr {
    width: 100%;
}
table, tr, td {
    border-spacing:0;
    border-collapse:collapse;
}
th, td {
    border: solid 1px #000000;
    vertical-align: top;
}";

$doc = new DOMDocument();

$html = $doc->appendChild($doc->createElement('html'));
$head = $html->appendChild($doc->createElement('head'));
$head->appendChild($doc->createElement('style', $css));
$body = $html->appendChild($doc->createElement('body'));

$table = $body->appendChild($doc->createElement('table'));
$row_h = $table->appendChild($doc->createElement('tr'));

foreach($fields as $field) {
    $name = isset($translations[$field])?$translations[$field]:$field;
    $cell = $row_h->appendChild($doc->createElement('th', $name));
    $cell->setAttribute('width', $widths[$field].'%');
}

foreach($values as $oid => $odata) {
    $row = $table->appendChild($doc->createElement('tr'));
    foreach($fields as $field) {
        $cell = $row->appendChild($doc->createElement('td', $odata[$field]));
    }    
}



$html = $doc->saveHTML();

// instantiate and use the dompdf class
$options = new DompdfOptions();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml((string) $html);
$dompdf->render();

$canvas = $dompdf->get_canvas();
$font = $dompdf->getFontMetrics()->get_font("helvetica", "regular");
$canvas->page_text(560, $canvas->get_height() - 35, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0,0,0));
$canvas->page_text(40, $canvas->get_height() - 35, "Export", $font, 9, array(0,0,0));


// get generated PDF raw binary
$output = $dompdf->output();

$context->httpResponse()
        ->header('Content-Type', 'application/pdf')
        // ->header('Content-Disposition', 'attachment; filename="document.pdf"')
        ->header('Content-Disposition', 'inline; filename="document.pdf"')
        ->body($output)
        ->send();