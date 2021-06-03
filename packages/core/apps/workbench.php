<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => 'Dashboard providing access to eQual design utilities.',
    'params'        => [],
    'response'      => [
        'location'      => '/workbench/'
    ]
]);