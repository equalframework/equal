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
            'description'   => 'Pipeline\'s id',
            'type'          => 'integer'
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*',
        'schema' => [
            'type'          => '',
            'qty'       => ''
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

$pipeline = Pipeline::id($params['pipeline_id'])
    ->read([
        'nodes_ids' => [
            'id',
            'in_links_ids' => ['source_node_id'],
            'out_links_ids' => ['target_node_id']
        ]
    ])
    ->first();

$pipeline_nodes = $pipeline['nodes_ids']->get(true);

$graph = [];

foreach ($pipeline_nodes as $node) {
    $graph[$node['id']] = [];
    foreach ($node['in_links_ids'] as $link) {
        $graph[$node['id']][] = $link['source_node_id'];
    }
    foreach ($node['out_links_ids'] as $link) {
        $graph[$node['id']][] = $link['target_node_id'];
    }
    $graph[$node['id']] = array_unique($graph[$node['id']]);
}

if (!isGraphConnected($graph)) {
    throw new Exception('non-compliant_pipeline', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
    ->body(['success' => true])
    ->send();

function isGraphConnected($graph)
{
    $visited = [];
    $start_node = array_key_first($graph);

    depthSearch($graph, $start_node, $visited);

    foreach ($graph as $node => $adjacent_nodes) {
        if (!isset($visited[$node])) {
            return false;
        }
    }

    return true;
};

function depthSearch($graph, $node, &$visited)
{
    $visited[$node] = true;

    foreach ($graph[$node] as $adjacent_node) {
        if (!isset($visited[$adjacent_node])) {
            depthSearch($graph, $adjacent_node, $visited);
        }
    }
};
