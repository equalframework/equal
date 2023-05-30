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
    'access'        => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'accept-origin'     => '*',
        'content-type'      => 'application/pdf'
    ],
    'providers'     => ['context', 'orm', 'auth', 'adapt']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\orm\ObjectManager            $orm
 * @var \equal\auth\AuthenticationManager   $auth
 * @var \equal\data\DataAdapter             $adapt
 */
list($context, $orm, $auth, $adapter) = [$providers['context'], $providers['orm'], $providers['auth'], $providers['adapt']];


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

/**
 * retrieve view schema
 */

$view_schema = eQual::run('get', 'model_view', [
    'entity'        => $params['entity'],
    'view_id'       => $params['view_id']
]);

if(!isset($view_schema['layout']['items'])) {
    throw new Exception('invalid_view', QN_ERROR_INVALID_CONFIG);
}

$group_by = (isset($view_schema['group_by']))?$view_schema['group_by']:[];

$view_fields = [];
foreach($view_schema['layout']['items'] as $item) {
    if(isset($item['type']) && isset($item['value']) && $item['type'] == 'field') {
        $view_fields[] = $item;
    }
}


/**
 * Read targeted objects
 */

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
    // convert JSON values to PHP
    foreach($values as $index => $object) {
        foreach($object as $field => $value) {
            if(!isset($schema[$field]['type'])) {
                continue;
            }
            $values[$index][$field] = $adapter->adapt($value, $schema[$field]['type']);
        }
    }
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
        $translations[$field] = $descr;
    }
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
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    td.allow-wrap {
        white-space: normal;
    }
    td.center {
        text-align: center;
    }
    td.right {
        text-align: right;
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


// 1) create head row

$row = createHeaderRow($doc, $view_fields, $translations);
$table->appendChild($row);


// 2) generate table lines

if($is_controller_entity) {
    // no group_by support

    foreach($values as $oid => $odata) {
        // adapt value
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
                $align = 'center';
            }
            else if($type == 'time') {
                $align = 'center';
                $value = date($settings['time_format'], strtotime('today') + $value);
            }
            else if($type == 'datetime') {
                $align = 'center';
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
                $value = strip_tags(str_replace(['</p>', '<br />'], "\r\n", $value));
            }
            else {
                // translate 'select' values
                if($type == 'string' && isset($schema[$field]['selection'])) {
                    if(isset($translations[$field]) && isset($translations[$field]['selection'])) {
                        $value = $translations[$field]['selection'][$value];
                    }
                }
            }
            $odata[$field] =  $value;
        }

        $row = createObjectRow($doc, $odata, $view_fields, $translations, $schema, $settings);
        $table->appendChild($row);
    }
}
else {
    // with group_by support
    // create initial stack of goups / objects
    $stack = [$values];
    if(count($group_by) && !$is_controller_entity) {
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
                $row = createObjectRow($doc, $object, $view_fields, $translations, $schema, $settings);
                $table->appendChild($row);
            }

            // #todo - if operations are defined, add a line for each group
        }
        else if(isset($elem['_is_group'])) {
            $row = createGroupRow($doc, $elem, $view_fields, $translations);
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
}


// 3) handle 'operations' property, if set
// #todo

$html = $doc->saveHTML();

/*
$context->httpResponse()
        ->header('Content-Type', 'text/html')
        ->body($html)
        ->send();

die();
*/

/*
    Generate PDF content
*/

// instantiate and use the dompdf class
$options = new DompdfOptions();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml((string) $html);
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont("helvetica", "regular");
$canvas->page_text($canvas->get_width() - 70, $canvas->get_height() - 35, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0,0,0));
$canvas->page_text(30, $canvas->get_height() - 35, "Export - ".$view_legend, $font, 9, array(0,0,0));

$canvas->page_text(30, 30, $view_title, $font, 14, array(0,0,0));

/*
    Output PDF envelope
*/

// get generated PDF raw binary
$output = $dompdf->output();

$context->httpResponse()
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

function createHeaderRow($doc, &$view_items, &$translations) {
    $row = $doc->createElement('tr');
    $default_width = round(100.0 / count($view_items), 2);
    foreach($view_items as $item) {
        $field = $item['value'];
        $width = (isset($item['width']))?intval($item['width']):$default_width;
        if($width <= 0) {
            continue;
        }
        $name = isset($translations[$field])?$translations[$field]['label']:$field;
        $cell = $row->appendChild($doc->createElement('th', htmlspecialchars($name)));
        $cell->setAttribute('width', $width.'%');
    }
    return $row;
}

function createObjectRow($doc, $object, &$view_items, &$translations, &$schema, &$settings) {
    $row = $doc->createElement('tr');
    $default_width = round(100.0 / count($view_items), 2);
    // for each field, create a widget, append to a cell, and append cell to row
    foreach($view_items as $item) {
        $field = $item['value'];
        $width = (isset($item['width']))?intval($item['width']):$default_width;
        if($width <= 0) continue;

        $value = $object[$field];

        $type = $schema[$field]['type'];
        // #todo - handle 'alias'
        if($type == 'computed') {
            $type = $schema[$field]['result_type'];
        }

        $usage = (isset($schema[$field]['usage']))?$schema[$field]['usage']:'';
        $class = 'left';

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
                $class = 'right';
            }
        }
        else if($type == 'date') {
            $class = 'center';
            $value = ($value)?date($settings['date_format'], $value):'';
        }
        else if($type == 'time') {
            $class = 'center';
            $value = date($settings['time_format'], strtotime('today') + $value);
        }
        else if($type == 'datetime') {
            $class = 'center';
            $value = ($value)?date($settings['date_format'].' '.$settings['time_format'], $value):'';
        }
        else {
            if($type == 'string') {
                $class = 'center';
            }
            else {
                if(strpos($usage, 'amount/money') === 0) {
                    $class = 'right';
                    $value = $settings['format_currency']($value);
                }
                if(is_numeric($value)) {
                    $class = 'right';
                }
            }
        }

        // handle html content
        if($type == 'string' && strlen($value) && $usage == 'text/html') {
            $class = 'left';
            $elem = $doc->createDocumentFragment();
            $elem->appendXML($value);

            $cell = $doc->createElement('td');
            $cell->appendChild($elem);
        }
        else {
            // translate 'select' values
            if($type == 'string' && isset($schema[$field]['selection'])) {
                if(isset($translations[$field]) && isset($translations[$field]['selection'])) {
                    $value = $translations[$field]['selection'][$value];
                }
            }
            $cell = $doc->createElement('td', htmlspecialchars($value));
        }

        if($type == 'string' && strpos($usage, 'text/') === 0) {
            $class += ' allow-wrap';
        }

        $cell->setAttribute('class', $class);

        $row->appendChild($cell);
    }

    return $row;
}

function createGroupRow($doc, $group, &$view_items, &$translations) {

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

    $cell->setAttribute('colspan', count($view_items));
    $row->appendChild($cell);

    return $row;
}