<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Field;

[$params, $providers] = eQual::announce([
    'description'   => "mark the given object(s) as archived.",
    'params'        => [
        'json' => [
            'type'          => 'text',
            'description'   => 'Unique identifier of the object to remove.',
            'default'       => ''
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'constants'     => ['UPLOAD_MAX_FILE_SIZE'],
    'providers'     => ['orm', 'adapt']
]);

/**
 * @var \equal\orm\ObjectManager                $orm
 * @var \equal\data\adapt\DataAdapterProvider   $dap
 */
['orm' => $orm, 'adapt' => $dap] = $providers;

/**
 * This script is meant to be run using CLI, in conjunction with -i arg.
 * In order to prevent the context service from consuming data from stdin.
 * Example: ./equal.run -i --do=model_import <tmp.json
 */
$json = '';

if($params['json']) {
    $json = $params['json'];
}
else {
    $stdin = fopen('php://stdin','r');
    if(!stream_set_blocking($stdin, false)) {
        // this doesn't work under Win64: if no json param is given, script will wait for an input
        // throw new Exception('stream_unavailable', QN_ERROR_UNKNOWN);
    }

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
                throw new Exception('max_size_exceeded', EQ_ERROR_INVALID_PARAM);
            }
            $json .= $s;
        }
    }
}

$data = [];

if(strlen($json)) {
    $data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
    if(is_null($data)) {
        throw new Exception('invalid json: '.json_last_error_msg(), EQ_ERROR_INVALID_PARAM);
    }
}

if(empty($data)) {
    throw new Exception('missing_data', EQ_ERROR_INVALID_PARAM);
}

/** @var \equal\data\adapt\DataAdapter $adapter */
$adapter = $dap->get('json');

foreach($data as $class) {

    if(!isset($class['name']) || !isset($class['data'])) {
        throw new Exception('invalid_schema', EQ_ERROR_INVALID_PARAM);
    }

    $entity = $class['name'];
    $lang = $class['lang'];
    $model = $orm->getModel($entity);
    $schema = $model->getSchema();

    $objects_ids = [];

    foreach($class['data'] as $odata) {
        foreach($odata as $field => $value) {
            /** @var equal\orm\Field */
            $f = $model->getField($field);
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
