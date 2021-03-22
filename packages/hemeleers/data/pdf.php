<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
require_once ('../vendor/phpQuery.class.php');


$GLOBALS['categories'] = [
    "Miroirs et Trumeaux"       =>  ["miroir", "trumeau", "dessin"],
    "Chaises & Fauteuils"	    =>  ["fauteuil", "banquette", "canapé", "chaise"],
    "Luminaires"                =>	["lampe", "lustre", "lanterne", "applique"],
    "Mobilier"          	    =>  ["table", "bibliothèque", "buffet", "cheminée", "guéridon", "commode", "dressoir", "desserte", "étagère", "armoire", "secrétaire", "coffre"],
    "Porcelaine et Faïence"     =>	["porcelaine", "imari", "faïence", "céramique", "bol", "assiette"],
    "Argenterie et Verrerie"    =>	["en argent", "en verre"],
    "Bronzes de Vienne"         =>	["bronze de vienne"],    
    "Sculptures"                =>	["en bronze", "en bois"],
    "Tableaux"                  =>	["dessin", "gravure", "aquarelle", "huile sur toile", "encoignure", "estampe"],
    "Divers"                    =>  []
];


function get_category($description) {
    global $categories;
    $result = "Divers";
    foreach($categories as $category => $evidences) {
        foreach($evidences as $evidence) {
            if(stripos($description, $evidence) !== false) {
                $result = $category;
                break 2;
            }
        }
    }
    return $result;
}


// load products (DB export) from .csv file
/*
original SQL query : 

select ps_product.id_product, ps_product.reference, ps_product_lang.name, ps_product_lang.description, ps_product.price, ps_category_lang.link_rewrite, ps_product_lang.link_rewrite, ps_image.id_image 
from ps_product, ps_product_lang, ps_category_lang, ps_image
where ps_category_lang.id_category = ps_product.id_category_default AND ps_product_lang.id_product = ps_product.id_product AND ps_product_lang.id_lang = 1 
AND ps_category_lang.id_lang = 1
AND ps_image.id_product = ps_product.id_product
AND ps_image.position = 1

*/
$spreadsheet = IOFactory::load("packages/hemeleers/ps_products.csv");
// convert first spreadsheet to an array
$products = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

// drop header row
array_shift($products);


$catalog = [];

foreach($products as $product) {
    if(empty($product[0])) break;
    $item = [
        "id"            => sprintf("%04d", $product[0]),
        // "category"      => get_category($product[4]),
        "category"      => ucfirst(str_replace('-', ' ', $product[5])),
        "price"         => round($product[4]*1.21).' eur',
        "description"   => $product[3],
        "name"          => $product[2],
        "reference"     => $product[1],
        "image"         => 'https://frantzhemeleers.be/img/p/'.implode('/', str_split((string) $product[7])).'/'.$product[7].'-small_default.jpg',
        "link"          => sprintf("https://frantzhemeleers.be/fr/%s/%s-%s.html", $product[5], $product[0], $product[6])
    ];
    
    $catalog[] = $item;
}

// for tests purpose (comment in prod)
// $catalog = array_slice($catalog, 0, 20);

usort($catalog, function ($a, $b) {
    $cmp = strcmp($a["category"], $b["category"]);
    if($cmp == 0) return strcmp($a["id"], $b["id"]);
    return strcmp($a["category"], $b["category"]);
});

// parser function allowing recursive calls
function parse($elem, $values) {
    // we duplicate the current document
    $new_elem = $elem->clone();
    // and loop through all top-level var nodes (not belonging to a parent <var>)
    foreach($new_elem['var'] as $node) {
        $var = pq($node);
        if($var->parents()->filter('var')->length()==0) {	
            // ignore invalid nodes (missing `id` attribute)
            if(($field = $var->attr('id')) == null) continue;
            $parent = $var->parent();
            // if node contains more var nodes 
            if($var->find('var')->length() > 0) {
                // parse content and append it to the parent
                // if(is_array($values[$field])) 
                $outer_html = pq('<div />')->append($var->children());
                foreach($values[$field] as $item) {
                    $parent->append(parse($outer_html, $item));
                }
            }
            else {            
                // append a sibling node to the parent
                if(isset($values[$field])) {
                    $format = $var->attr('format'); 
                    $style = $var->attr('style'); 
                    $class = $var->attr('class'); 
                    switch($format) {
                       case 'link':
                           $txt = pq('<a />')->append($values[$field])->attr('href', $values[$field]);
                           break;
                       case 'image':
                            $txt = pq('<img />')->attr('src', $values[$field]);
                            break;
                       default:
                           $txt = str_replace(array("\n", "\r\n"), "<br />", strip_tags($values[$field]));
                    }
                    if($style) $txt->attr('style', $style);
                    if($class) $txt->attr('class', $class);                    
                    $parent->append($txt);
                }
            }
            // and remove the node
            $var->remove();
        }
    }
    
    return $new_elem->children();
};


$html = file_get_contents("packages/hemeleers/views/Catalog.form.print.html");	
$doc = phpQuery::newDocumentHTML($html, 'UTF-8');
$body = pq('body');
$html = parse($body, ['products' => $catalog]);
$html = $doc->find('body')->empty()->append($html);


// instantiate and use the dompdf class
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml((string) $doc);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

$canvas = $dompdf->get_canvas();
$font = $dompdf->getFontMetrics()->get_font("klavica", "regular");
$canvas->page_text(560, $canvas->get_height() - 35, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(255,255,255));

$canvas->page_text(40, $canvas->get_height() - 45, "FrantzHemeleers.be - Vente Exceptionnelle", $font, 9, array(0,0,0));
$canvas->page_text(40, $canvas->get_height() - 35, "Mars 2020", $font, 9, array(0,0,0));

//$canvas->page_text(40, 25, "Catalogue de la Vente", $font, 16, array(0,0,0));
//$canvas->page_text(460, 25, "Frantz Hemeleers", $font, 11, array(0,0,0));
//$canvas->page_text(460, 37, "Antiquités & Décoration", $font, 11, array(0,0,0));


// Output the generated PDF to Browser
$dompdf->stream();