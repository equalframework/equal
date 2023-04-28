<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Field;

list($params, $providers) = announce([
    'description'   => "mark the given object(s) as archived.",
    'params'        => [
        'json' =>  [
            'description'   => 'Unique identifier of the object to remove.',
            'type'          => 'text',
            'default'       => ''
        ]
    ],
    'constants'     => ['UPLOAD_MAX_FILE_SIZE'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'access' => [
        'visibility'        => 'private'
    ],
    'providers'     => ['context', 'orm', 'access', 'adapt']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\access\AccessController  $ac
 * @var \equal\data\DataAdapterProvider $dap
 */
list($context, $orm, $ac, $dap) = [$providers['context'], $providers['orm'], $providers['access'], $providers['adapt']];

$json = '';

if($params['json']) {
    $json = $params['json'];
}
else {
    $stdin = fopen('php://stdin','r');
    stream_set_blocking($stdin, false);

    $read = [$stdin];
    $write = null;
    $except = null;
    $count = stream_select($read, $write, $except, 0, 1000);

    if($count > 0) {
        $bytes = 0;
        $chunk_size = 1024;
        $json = '';
        while($s = fgets($stdin, $chunk_size)) {
            $bytes += $chunk_size;
            if($bytes > constant('UPLOAD_MAX_FILE_SIZE')) {
                throw new Exception('max_size_exceeded', QN_ERROR_INVALID_PARAM);
            }
            $json .= $s;
        }
    }
}

$data = [];

if(strlen($json)) {
    $data = json_decode($json, true, JSON_BIGINT_AS_STRING);
    if(is_null($data)) {
        throw new Exception('invalid_json', QN_ERROR_INVALID_PARAM);
    }
}

if(empty($data)) {
    throw new Exception('missing_data', QN_ERROR_INVALID_PARAM);
}

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

foreach($data as $class) {

    if(!isset($class['name']) || !isset($class['data'])) {
        throw new Exception('invalid_schema', QN_ERROR_INVALID_PARAM);
    }

    $entity = $class['name'];
    $lang = $class['lang'];
    $model = $orm->getModel($entity);
    $schema = $model->getSchema();

    $objects_ids = [];

    foreach($class['data'] as $odata) {
        foreach($odata as $field => $value) {
            $f = new Field($schema[$field]);
            $odata[$field] = $adapter->adaptIn($value, $f->getUsage());
        }
        if(isset($odata['id'])) {
            $res = $orm->search($entity, ['id', '=', $odata['id']]);
            if($res > 0 && count($res)) {
                // object already exist, but either values or language differs
                $id = $odata['id'];
                $res = $orm->update($entity, $id, $odata, $lang);
                $objects_ids[] = $id;
            }
            else {
                $objects_ids[] = $orm->create($entity, $odata, $lang);
            }
        }
        else {
            $objects_ids[] = $orm->create($entity, $odata, $lang);
        }
    }

    // force a first generation of computed fields, if any
    $computed_fields = [];
    foreach($schema as $field => $def) {
        if($def['type'] == 'computed') {
            $computed_fields[] = $field;
        }
    }
    $orm->read($entity, $objects_ids, $computed_fields, $lang);
}
