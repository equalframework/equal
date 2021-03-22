<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
require_once ('../vendor/phpQuery.class.php');


$GLOBALS['categories'] = [
    "Miroirs et Trumeaux"       =>  ["miroir", "trumeau", "dessin"],
    "Chaises"	                =>  ["chaise"],
    "Fauteuils"	                =>  ["fauteuil", "banquette", "canapé"],
    "Luminaires"                =>	["lampe", "lustre", "lanterne", "applique"],
    "Mobilier"          	    =>  ["table", "bibliothèque", "buffet", "cheminée", "guéridon", "commode", "dressoir", "desserte", "étagère", "armoire", "secrétaire", "coffre"],
    "Porcelaine et Faïence"     =>	["porcelaine", "imari", "faïence", "céramique", "bol", "assiette"],
    "Argenterie et Verrerie"    =>	["en argent", "en verre"],
    "Bronzes de Vienne"         =>	["bronze de vienne"],    
    "Sculptures"                =>	["en bronze", "en bois"],
    "Tableaux"                  =>	["dessin", "gravure", "aquarelle", "huile sur toile", "encoignure", "estampe"],
    "Divers"                    =>  []
];


$GLOBALS['categories_ids'] = [
    "Miroirs et Trumeaux"       =>  10,
    "Chaises"	                =>  7,
    "Fauteuils"	                =>  11,
    "Luminaires"                =>	5,
    "Mobilier"          	    =>  3,
    "Porcelaine et Faïence"     =>	9,
    "Argenterie et Verrerie"    =>	8,
    "Bronzes de Vienne"         =>	12,
    "Sculptures"                =>	6,
    "Tableaux"                  =>	4,
    "Divers"                    =>  13
];


function get_category_id($description) {
    global $categories, $categories_ids;
    $result = "Divers";
    foreach($categories as $category => $evidences) {
        foreach($evidences as $evidence) {
            if(stripos($description, $evidence) !== false) {
                $result = $category;
                break 2;
            }
        }
    }
    return $categories_ids[$result];
}

// load excel file
$spreadsheet = IOFactory::load("packages/hemeleers/catalogue.xlsx");
// convert first spreadsheet to an array
$data = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);


$headers = array_shift($data);


// init result with header line
$result = [ 
    [
    "ID",
    "Active (0/1)",
    "Name *",
    "Description",
    "Short description",
    "Categories (x,y,z...)",
    "Price tax excluded or Price tax included",
    "On sale (0/1)",
    "Reference #",
    "Image URLs (x,y,z...)"
    ]
];

foreach($data as $row) {
    
    
    $item = [
        '0',
        '1',
        ucfirst($row[2]),           // Name                 => description (col 2)
        '<p>'.$row[2].'</p>',       // Description          => description (col 2)
        $row[2],                    // Short description    => description (col 2)
        get_category_id($row[2]),   // Categories           => category id (col 2)
        $row[3],                    // Price                => price (col 3)
        1,                          // On sale              => true
        $row[4]                     // Reference            => ref (col 4)
    ];
    
    if($row[5] == 'ok') {
        $item[] = 'http://frantzhemeleers.be/tmp/'.$row[0].'_'.str_replace('/', '_', $row[4]).'.JPG';
    }
    else {
        $item[] = '';
    }
    
    $result[] = $item;
}


foreach($result as $line) {
    echo implode(';', $line).PHP_EOL;
}