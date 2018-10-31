<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;




$filename = "test.xlsx";

if(!file_exists($filename)) {
    throw new Exception("File not found");
}

$spreadsheet = IOFactory::load($filename);

$data = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
    

$last_row = count($data);

$nodes = [];
$edges = [];

$parents = array(-1);
$group = 1;

for ($row = 0; $row < $last_row; ++$row) {
    $id = $row;
    $offset = 0;
    do {
        $label = $data[$row][$offset];
        ++$offset;
    } while(!strlen($label));

    if($offset == 2) ++$group;
    $parent = $parents[$offset-1];

    $parents[$offset] = $id;
    
    // create nodes
    $nodes[] = ["id" => $id, "label" => $label, "group" => $group];
    // create edges between nodes (skip first row - which has no parent)
    if($row) {
        $edges[] = ["from" => $parent, "to" => $id];
    }
}

echo json_encode($nodes, JSON_PRETTY_PRINT);
echo json_encode($edges, JSON_PRETTY_PRINT);
