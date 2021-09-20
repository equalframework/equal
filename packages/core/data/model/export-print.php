<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;

list($params, $providers) = announce([
    'description'   => "Returns a view populated with a collection of objects and outputs it as a PDF document.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to use (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'id' => [
            'description'   => 'Identitifier of the object to print.',
            'type'          => 'integer',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string', 
            'default'       => 'print.default'
        ],
        'domain' =>  [
            'description'   => 'List of unique identifiers of the objects to read.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' =>  [
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]        
    ],
    'response'      => [
        'accept-origin' => '*'        
    ],
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [$providers['context'], $providers['orm']];

$entity = $params['entity'];


// #todo - improve this
$parts = explode('\\', $entity);    
$package = array_shift($parts);
$class_path = implode('/', $parts);
$parent = get_parent_class($entity);

$file = QN_BASEDIR."/packages/{$package}/views/{$class_path}.{$params['view_id']}.html";

if(!file_exists($file)) {
    throw new Exception("unknown_view_id", QN_ERROR_UNKNOWN_OBJECT);
}


$loader = new TwigFilesystemLoader(QN_BASEDIR."/packages/{$package}/views/");
$twig = new TwigEnvironment($loader);

$template = $twig->load("{$class_path}.{$params['view_id']}.html");


$values = $params['entity']::search($params['domain'])->read($fields)->adapt('txt')->get();

$html = $template->render([]);



// instantiate and use the dompdf class
$options = new DompdfOptions();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml((string) $html);
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont("helvetica", "regular");
$canvas->page_text(530, $canvas->get_height() - 35, "p. {PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0,0,0));
// $canvas->page_text(40, $canvas->get_height() - 35, "Export", $font, 9, array(0,0,0));


// get generated PDF raw binary
$output = $dompdf->output();

$context->httpResponse()
        ->header('Content-Type', 'application/pdf')
        // ->header('Content-Disposition', 'attachment; filename="document.pdf"')
        ->header('Content-Disposition', 'inline; filename="document.pdf"')
        ->body($output)
        ->send();