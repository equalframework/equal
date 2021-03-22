<?php


$json = file_get_contents('https://api.teleport.org/api/urban_areas/?embed=ua:item/ua:images');

$data = json_decode($json, true);

$cities = [];

foreach($data['_embedded']['ua:item'] as $item) {
    echo "'{$item['slug']}' => '{$item['_embedded']['ua:images']['photos'][0]['image']['mobile']}',\n";
    
    $cities[$item['slug']] = $item['_embedded']['ua:images']['photos'][0]['image']['mobile'];
}

echo json_encode($cities, JSON_PRETTY_PRINT);