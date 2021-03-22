<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\orm\Domain;

list($params, $providers) = announce([
    'description'   => 'Returns a list of entites according to given domain (filter), start offset, limit and order.',
    'params'        => [],
    'response'      => [
        'location'      => '/workbench/'
    ],     
    'providers'     => [ 'context' ] 
]);

list($context) = [ $providers['context'] ];

header('Location: /workbench/');

$context->httpResponse()
        ->body($result)
        ->send();