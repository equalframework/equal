<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Redirect to eQual Discord server.',
    'params'        => [],
    'response'      => [
        'location'      => 'https://discord.gg/xNAXyhbYBp'
    ]
]);
