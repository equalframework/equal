<?php
namespace core\pipeline;
use equal;
use equal\php\Context;

list($params, $providers) = eQual::announce([
    'description' => "Load a full pipeline with its nodes, metadata, links and parameters.",
    'params' => [
        'name' => [
            'description' => "Pipeline name",
            'type' => 'string',
            'required' => true
        ]
    ],
    'response' => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers' => ['context']
]);

/** @var Context $context */
$context = $providers['context'];

// Step 1: Get pipeline by name
$pipelineList = \eQual::run('get', 'model_collect', [
    'entity' => 'core\\pipeline\\Pipeline',
    'domain' => [['name', '=', $params['name']]],
    'fields' => ['id', 'name', 'nodes_ids'],
    'nolimit' => true
], true);

if (count($pipelineList) === 0) {
    throw new \Exception("pipeline_not_found");
}

$pipeline = $pipelineList[0];
$nodes_ids = $pipeline['nodes_ids'] ?? [];

$result = [
    'pipeline' => [
        'id' => $pipeline['id'],
        'name' => $pipeline['name']
    ],
    'nodes' => [],
    'links' => [],
    'parameters' => []
];

// Step 2: Load all nodes
$nodes = \eQual::run('get', 'model_collect', [
    'entity' => 'core\\pipeline\\Node',
    'domain' => [['id', 'in', $nodes_ids]],
    'fields' => [
        'id', 'name', 'description',
        'out_links_ids', 'in_links_ids',
        'operation_controller', 'operation_type',
        'params_ids'
    ],
    'nolimit' => true
], true);

// Step 3: Load Meta for nodes
$metaList = \eQual::run('get', 'model_collect', [
    'entity' => 'core\\Meta',
    'domain' => [['reference', 'in', array_map(fn($id) => 'node.' . $id, $nodes_ids)]],
    'fields' => ['reference', 'value'],
    'nolimit' => true
], true);

$metaMap = [];
foreach ($metaList as $meta) {
    $nodeId = (int) str_replace('node.', '', $meta['reference']);
    $metaMap[$nodeId] = json_decode($meta['value'], true);
}

$linkIds = [];
$paramIds = [];

// Step 4: Process nodes and collect link/param IDs
foreach ($nodes as $node) {
    $nodeId = $node['id'];

    $result['nodes'][] = [
        ...$node,
        'meta' => $metaMap[$nodeId] ?? ['position' => ['x' => 0, 'y' => 0], 'icon' => '', 'color' => '']
    ];

    $linkIds = array_merge($linkIds, $node['in_links_ids'], $node['out_links_ids']);
    $paramIds = array_merge($paramIds, $node['params_ids']);
}

// Step 5: Load links
$linkIds = array_unique($linkIds);
if (!empty($linkIds)) {
    $links = \eQual::run('get', 'model_collect', [
        'entity' => 'core\\pipeline\\NodeLink',
        'domain' => [['id', 'in', $linkIds]],
        'fields' => ['id', 'reference_node_id', 'source_node_id', 'target_node_id', 'target_param'],
        'nolimit' => true
    ], true);
    $result['links'] = $links;
}

// Step 6: Load parameters
$paramIds = array_unique($paramIds);
if (!empty($paramIds)) {
    $params = \eQual::run('get', 'model_collect', [
        'entity' => 'core\\pipeline\\Parameter',
        'domain' => [['id', 'in', $paramIds]],
        'fields' => ['id', 'node_id', 'param', 'value'],
        'nolimit' => true
    ], true);

    // Decode values
    foreach ($params as &$p) {
        $p['value'] = json_decode($p['value'], true);
    }

    $result['parameters'] = $params;
}

$context->httpResponse()
    ->body($result)
    ->send();