<?php
/**
*    This file is part of the eQual framework.
*    https://github.com/equalframework/equal
*
*    Some Rights Reserved, The eQual Framework, 2010-2026
*    Original Author(s): Cedric Francoys
*    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Lesser General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public License
*    along with this program. If not, see <http://www.gnu.org/licenses/>.
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