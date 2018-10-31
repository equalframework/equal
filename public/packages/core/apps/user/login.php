<?php
/*
    This file is part of the Resipedia project <http://www.github.com/cedricfrancoys/resipedia>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => 'Returns a fully-loaded document object along with current user history (actions performed on document and comments)',
    'params'        => [
        'user_name' 	=>  [
            'description'   => 'User to logon to.',
            'type'          => 'string', 
            'default'       => ''
        ],
        'lang'			=>  [
            'description'   => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'response'      => [
        'content-type'  => 'text/html',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context'] 
]);

list($context, $api) = [$providers['context']];



$html = file_get_contents('packages/core/html/templates/login.html');

$context->httpResponse()->body($html)->send();