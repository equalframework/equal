<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
// handle the '_escaped_fragment_' parameter in case page is requested by a crawler
if(isset($_REQUEST['_escaped_fragment_'])) {
    $uri = $_REQUEST['_escaped_fragment_'];
    header('Status: 200 OK');
    header('Location: '.$uri);
    exit();
}

// handle cached response : GET requests might be cached (@see announce() and response/cacheable property)
if( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
    $url = 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'."{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $cache_id = md5($url);
    $cache_filename = '../cache/'.$cache_id;
    if(file_exists($cache_filename)) {
        list($headers, $result) = unserialize(file_get_contents($cache_filename));
        foreach($headers as $header => $value) {
            header("$header: $value");
        }
        print_r($result);
        exit();
    }
}


function getAppOutput() {
    ob_start();	
    include('../run.php'); 
    return ob_get_clean();
};

$content = getAppOutput();

// relay result to php://stdout
print($content);