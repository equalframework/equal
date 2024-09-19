<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Original Author: Cedric Francoys
    License:  GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

/*
    Session config
*/
// disable SID and sessions
ini_set('session.use_cookies',      '0');
ini_set('session.use_only_cookies', '1');
// make sure not to rewrite URL
ini_set('session.use_trans_sid',    '0');
ini_set('url_rewriter.tags',        '');

/*
    URI sanitization
*/
// handle the '_escaped_fragment_' parameter to support requests made by crawlers
if(isset($_REQUEST['_escaped_fragment_'])) {
    $uri = $_REQUEST['_escaped_fragment_'];
    header('Status: 200 OK');
    header('Location: '.$uri);
    exit();
}

/*
    Request handling
*/

// #debug - for debugging, uncomment this to force the header of generated HTTP response
/*
header("HTTP/1.1 200 OK");
header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD,TRACE');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Expose-Headers: *');
header('Allow: *');
flush();
*/

// parse requested operation and relay result to php://stdout
include('../run.php');