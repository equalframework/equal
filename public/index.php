<?php
/*  index.php - public entry point of Qinoa framework.

    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

// handle the '_escaped_fragment_' parameter in case page is requested by a crawler
if(isset($_REQUEST['_escaped_fragment_'])) {
    $uri = $_REQUEST['_escaped_fragment_'];
    header('Status: 200 OK');
    header('Location: '.$uri);
    exit();
}

// this script is used to cache result of 'show' requests 
// ('show' requests should always return static HTML, and expect no params)
/*
if( isset($_REQUEST['show']) ) {
    $cache_filename = '../cache/'.$_REQUEST['show'];
    if(file_exists($cache_filename)) {
        print(file_get_contents($cache_filename));
        exit();
    }
}
*/

function getAppOutput() {
    ob_start();	
    include('../run.php'); 
    return ob_get_clean();
};

$content = getAppOutput();

// store restult if it needs to be cached
if( isset($cache_filename) && is_writable(dirname($cache_filename)) ) {
    file_put_contents($cache_filename, $content);
}

// relay result to php://stdout
print($content);