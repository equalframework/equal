<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Outputs plain text version of PHP current configuration (from `phpinfo`).',
    'access'        => [
        'visibility'        => 'private'
    ],
    'response'      => [
        'content-type'      => 'text/plain',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ]
]);

echo phpinfo();
