<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\pipeline\Pipeline;

list($params, $providers) = eQual::announce([
    'description'   => 'Run the given pipeline.',
    'params'        => [
        'pipeline_id' => [
            'description' => 'Pipeline\'s id',
            'type'        => 'integer'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*',
        'schema' => [
            'type' => '',
            'qty'  => ''
        ]
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context               $context
 */
$context = $providers['context'];

eQual::run("get", "core_check-pipeline", $params, true);

$pipeline = Pipeline::id($params['pipeline_id'])
    ->read([
        'nodes_ids' => [
            'id',
            'name',
            'operation_controller',
            'operation_type',
            'in_links_ids' => ['reference_node_id', 'source_node_id', 'target_param'],
            'out_links_ids' => ['target_node_id'],
            'params_ids' => ['value', 'param']
        ]
    ])
    ->first();

$pipeline_nodes = $pipeline['nodes_ids']->get(true);

$count = 0;

$result_map = $name_map = [];

foreach ($pipeline_nodes as $node) {
    if ($node['operation_type'] != null) {
        $result_map[$node['id']] = null;
        $name_map[$node['id']] = $node['name'];
        if (empty($node['in_links_ids'])) {
            $parameters = [];
            foreach ($node['params_ids'] as $param) {
                $parameters[$param['param']] = json_decode($param['value']);
            }
            $result_map[$node['id']] = eQual::run($node['operation_type'], $node['operation_controller'], $parameters, true);
            $count++;
        }
    }
}

while ($count != count($result_map)) {
    foreach ($pipeline_nodes as $node) {
        if ($node['operation_type'] != null && $result_map[$node['id']] == null) {
            $is_computable = true;
            $parameters = [];
            foreach ($node['in_links_ids'] as $link) {
                if ($result_map[$link['reference_node_id']] != null) {
                    $parameters[$link['target_param']] = $result_map[$link['reference_node_id']];
                } else {
                    $is_computable = false;
                    break;
                }
            }
            if ($is_computable) {
                foreach ($node['params_ids'] as $param) {
                    $parameters[$param['param']] = json_decode($param['value']);
                }
                $result_map[$node['id']] = eQual::run($node['operation_type'], $node['operation_controller'], $parameters, true);
                $count++;
            }
        }
    }
}

$res = [];

foreach ($name_map as $key => $value) {
    $res[$value] = $result_map[$key];
}

$context->httpResponse()
    ->body($res)
    ->send();
