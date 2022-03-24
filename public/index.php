<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
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
// parse requested operation and relay result to php://stdout
include('../run.php');