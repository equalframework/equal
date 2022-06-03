<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use core\setting\Setting;

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
    'access' => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 * @var \equal\data\DataAdapter     $adapter
 */
list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];


// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

// get the complete schema of the object (including special fields)
$schema = $entity->getSchema();

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

$view_schema = $data;
$group_by = $view_schema['group_by'];

$fields = [];
$widths = [];
// colspan for rows with single column (group)
$colspan = 1;
$default_width = round(100.0 / count($view_schema['layout']['items']), 2);

// compute columns widths, from layout
foreach($view_schema['layout']['items'] as $item) {
    if(isset($item['type']) && isset($item['value']) && $item['type'] == 'field') {
        $field = $item['value'];
        $widths[$field] = isset($item['width'])?intval($item['width']):$default_width;
        $fields[] = $field;
    }
}


/*
    Read targeted objects
*/

// copy original array holding fields names
$fields_to_read = $fields;

// adapt fields to force retrieving name for m2o fields
foreach($fields_to_read as $index => $field) {
    $descr = $schema[$field];
    if($descr['type'] == 'many2one') {
        unset($fields_to_read[$index]);
        $fields_to_read[$field] = ['id', 'name'];
    }
}

$values = $params['entity']::search($params['domain'])->read($fields_to_read)->get();


/*
    Retrieve Model translations
*/

// retrieve translation data (for fields names), if any
$json = run('get', 'config_i18n', [
    'entity'        => $params['entity'],
    'lang'          => $params['lang']
]);

// decode json into an array
$i18n = json_decode($json, true);
$translations = [];
if(!isset($i18n['errors']) && isset($i18n['model'])) {
    foreach($i18n['model'] as $field => $descr) {
        $translations[$field] = $descr['label'];
    }
}

// retrieve view title
$view_title = $view_schema['name'];
if(isset($i18n['view'][$params['view_id']])) {
    $view_title = $i18n['view'][$params['view_id']]['name'];
}
/*
    Generate HTML
*/

$css = "
    @page {
        margin: 1cm;
        margin-top: 2cm;
        margin-bottom: 1.5cm;
    }
    body {
        font-family:sans-serif;
        font-size: 0.7em;
        color: black;
    }
    table {
        table-layout: fixed;
        border-left: 1px solid #aaa;
        border-right: 0;
        border-top: 1px solid #aaa;
        border-bottom: 0;
    }
    table, tr {
        width: 100%;
    }
    table, tr, td {
        border-spacing:0;
    }
    th, td { 
        vertical-align: top;
        border-left: 0;
        border-right: 1px solid #aaa;
        border-top: 0;
        border-bottom: 1px solid #aaa;    
    }
    td {
        padding: 0px 5px;
    }
    td.center {
        text-align: center;
    }
    tr.sb-group-row td {
        font-weight: bold;
    }
    p {
        margin: 0;
        padding: 0;
    }";

$doc = new DOMDocument();

$html = $doc->appendChild($doc->createElement('html'));
$head = $html->appendChild($doc->createElement('head'));
$head->appendChild($doc->createElement('style', $css));
$body = $html->appendChild($doc->createElement('body'));
$table = $body->appendChild($doc->createElement('table'));
$row_h = $table->appendChild($doc->createElement('tr'));

foreach($fields as $field) {
    $width = $widths[$field];
    if($width > 0) {
        $name = isset($translations[$field])?$translations[$field]:$field;
        $cell = $row_h->appendChild($doc->createElement('th', $name));
        $cell->setAttribute('width', $widths[$field].'%');
        ++$colspan;
    }
}


$groups = [];

$stack = [$values];
if(isset($group_by) && count($group_by)) {
    $groups = groupObjects($schema, $values, $group_by);
    $stack = [$groups];
}


while(true) {
    if(count($stack) == 0) break;

    $elem = array_pop($stack);
    $first = reset($elem);

    if(is_array($elem) && isset($first['id'])) { 
        // group is an array of objects: render a row for each object
        foreach($elem as $object) {
            $row = createObjectRow($doc, $object, $view_schema['layout']['items'], $schema, $adapter);
            $table->appendChild($row);
        }

        // #todo - if operations are defined, add a line for each group
    }
    else if(isset($elem['_is_group'])) {
        $row = createGroupRow($doc, $elem, $colspan);
        $table->appendChild($row);
    }
    else {
        // #memo - keys must be strings        
        $keys = array_keys($elem);
        sort($keys);
        $keys = array_reverse( $keys );
        foreach($keys as $key) {
            if(in_array($key, ['_id', '_parent_id', '_key', '_label'])) continue;
            // add object or array
            if(isset($elem[$key]['_data'])) {
                $stack[] = $elem[$key]['_data'];
            }
            else {
                $stack[] = $elem[$key];
            }
            $stack[] = array_merge(['_is_group' => true], $elem[$key]);
        }
    }
}

$html = $doc->saveHTML();



/*
    Generate PDF content
*/

// instantiate and use the dompdf class
$options = new DompdfOptions();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml((string) $html);
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont("helvetica", "regular");
$canvas->page_text(560, $canvas->get_height() - 35, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0,0,0));
$canvas->page_text(40, $canvas->get_height() - 35, "Export", $font, 9, array(0,0,0));

$canvas->page_text(40, 30, $view_title, $font, 14, array(0,0,0));

/*
    Output PDF envelope
*/

// get generated PDF raw binary
$output = $dompdf->output();

$context->httpResponse()
        ->header('Content-Type', 'application/pdf')
        // ->header('Content-Disposition', 'attachment; filename="document.pdf"')
        ->header('Content-Disposition', 'inline; filename="document.pdf"')
        ->body($output)
        ->send();


/**
 * @param array $objects    List of (partial) array representation of an object collection.
 * @param array $group_by   Descriptor of the fields grouping
 */
function groupObjects($schema, $objects, $group_by) {

    $groups = [];

    // group objects
    foreach($objects as $object) {
        $n = count($group_by);
        $parent = &$groups;
        $parent_id = '';

        for($i = 0; $i < $n; ++$i) {            
            $group = $group_by[$i];
            $field = $group;

            if(is_array($group)) {
                if(!isset($group['field'])) {
                    continue;
                }
                $field = $group['field'];
            }

            $model_def = $schema[$field];
            $key = $object[$field];
            $label = $key;

            // handle m2o
            if(is_array($key)) {
                if(isset($key['name'])) {
                    $label = $key['name'];
                    $key = $key['name'];
                }
                else {
                    $label = '';
                    $key = '';
                }
            }

            if(in_array($model_def['type'], ['date', 'datetime'])) {
                // #todo - convert according to settings (date format)
                $label = date('d/m/Y', $key);
                $key = date('Y-m-d', $key);
            }
            else if(isset($model_def['usage'])) {
                if($model_def['usage'] == 'date/month') {
                    // convert ISO8601 month (1-12) to js month  (0-11)
                    $key = intval($key);
                    // #todo - convert according to settings (month name)
                    $label = date('F', mktime(0, 0, 0, $key, 10));
                    $key = sprintf("%02d", $key);
                }
            }

            if(!isset($parent[$key])) {
                if($i < $n-1) {
                    $parent[$key] = ['_id'=> $parent_id.$key, '_parent_id'=> $parent_id, '_key'=> $key, '_label'=> $label];
                }
                else {
                    $parent[$key] = ['_id'=> $parent_id.$key, '_parent_id'=> $parent_id, '_key'=> $key, '_label'=> $label, '_data'=> []];
                    if(is_array($group) && isset($group['operation'])) {
                        $parent[$key]['_operation'] = $group['operation'];
                    }
                }
            }
            $parent_id = $parent_id.$key;
            $parent = &$parent[$key];
        }
        if( isset($parent['_data']) && is_array($parent['_data']) ) {
            $object['_is_object'] = true;
            $parent['_data'][] = $object;
        }
    }

    return $groups;
}

function getElementByAttributeValue($doc, $tagname, $attrname, $value) {
    foreach ($doc->getElementsByTagName($tagname) as $element) {
        if ($element->getAttribute($attrname) == $value) {
            return $element;
        }
    }
    return null;
}

function createObjectRow($doc, $object, $items, $schema, $adapter) {

    $row = $doc->createElement('tr');

    // for each field, create a widget, append to a cell, and append cell to row
    foreach($items as $item) {
        $field = $item['value'];
        $width = intval($item['width']);
        if($width <= 0) continue;

        $value = $object[$field];

        $date_format = Setting::get_value('core', 'locale', 'date_format', 'm/d/Y');
        $time_format = Setting::get_value('core', 'locale', 'time_format', 'H:i');        

        // for relational fields, we need to check if the Model has been fetched
        if(in_array($schema[$field]['type'], ['one2many', 'many2one', 'many2many'])) {
            // by convention, `name` subfield is always loaded for relational fields
            if($schema[$field]['type'] == 'many2one' && isset($value['name'])) {
                $value = $value['name'];
            }
            else {
                $value = "...";
            }
        }
        else if($schema[$field]['type'] == 'date') {
            $value = date($date_format, $value);
        }
        else if($schema[$field]['type'] == 'time') {
            $value = date($time_format, strtotime('today') + $value);
        }
        else if($schema[$field]['type'] == 'datetime') {
            $value = date($date_format.' '.$time_format, $value);
        }
        else {
            // adapt from php to txt
            $value = $adapter->adapt($value, $schema[$field]['type'], 'txt', 'php');
        }

        // handle html content
        if($schema[$field]['type'] == 'string') {
            $elem = $doc->createDocumentFragment();
            $elem->appendXML($value);
            
            $cell = $doc->createElement('td');
            $cell->appendChild($elem);
        }
        else {
            $cell = $doc->createElement('td', $value);
        }

        $row->appendChild($cell);
    }

    return $row;
}

function createGroupRow($doc, $group, $colspan) {

    $label = $group['_label'];
    $prefix = '';
    $suffix = '';

    $children_count = 0;
    $parent_group_id = $group['_parent_id'];

    if(strlen($parent_group_id) > 0) {
        $parent_tr = getElementByAttributeValue($doc, 'tr', 'data-id', $parent_group_id);
        $prev_td = $parent_tr->firstChild;
        if($prev_td) {
            $prefix = $prev_td->getAttribute('title').' › ';
        }
    }

    if(isset($group['_data'])) {
        $children_count = count($group['_data']);

        if(count($group['_operation'])) {
            $op_result = 0;
            $op_type = $group['_operation'][0];
            // target should be 'object.{field_name}'
            $op_field = (explode('.', $group['_operation'][1]))[1];
            foreach($group['_data'] as $obj) {
                if(isset($obj[$op_field])) {
                    switch($op_type) {
                        case 'SUM':
                            $op_result += $obj[$op_field];                        
                            break;
                        case 'COUNT':
                            $op_result += 1;
                            break;
                        case 'MIN':
                            break;
                        case 'MAX':
                            break;
                        case 'AVG':
                            break;
                    }
                }
            }
            $suffix = ' ('.$op_result.')';
        }
        else {
            $suffix = ' ('.$children_count.')';
        }            
    }
    else {
        // sum children groups
    }

    $row = $doc->createElement('tr');
    $row->setAttribute('class', 'sb-group-row');
    $row->setAttribute('data-id', $group['_id']);

    $cell = $doc->createElement('td', $prefix.$label.$suffix);
    $cell->setAttribute('title', $prefix.$label);
    
    $cell->setAttribute('colspan', $colspan);
    $row->appendChild($cell);
    
    return $row;
}