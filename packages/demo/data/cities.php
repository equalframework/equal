<?php
list($params, $providers) = eQual::announce([
    'description'   => 'Get list of cities with pictures using teleport API.',
    'params'        => [
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

$json = file_get_contents('https://api.teleport.org/api/urban_areas/?embed=ua:item/ua:images');
$data = json_decode($json, true);

$cities = [];

foreach($data['_embedded']['ua:item'] as $item) {
    $cities[$item['slug']] = $item['_embedded']['ua:images']['photos'][0]['image']['mobile'];
}

$providers['context']
    ->httpResponse()
    ->body($cities)
    ->send();
