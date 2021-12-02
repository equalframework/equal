<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

/*
    Session config
*/
// Disable SID and sessions
ini_set('session.use_cookies',      '0');
ini_set('session.use_only_cookies', '1');
// and make sure not to rewrite URL
ini_set('session.use_trans_sid',    '0');
ini_set('url_rewriter.tags',        '');


// handle the '_escaped_fragment_' parameter in case page is requested by a crawler
if(isset($_REQUEST['_escaped_fragment_'])) {
    $uri = $_REQUEST['_escaped_fragment_'];
    header('Status: 200 OK');
    header('Location: '.$uri);
    exit();
}

function getAppOutput() {
    ob_start();
    include('../run.php');
    return ob_get_clean();
};

$content = getAppOutput();

// relay result to php://stdout
print($content);