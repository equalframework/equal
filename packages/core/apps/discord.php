<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Redirect to eQual Discord server.',
    'params'        => [],
    'response'      => [
        'location'      => 'https://discord.gg/xNAXyhbYBp'
    ]
]);
