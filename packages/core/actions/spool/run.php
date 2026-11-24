<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Mail;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   => "Send emails that are currently in spool queue.",
    'params'        => [],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);


// initialize local vars with inputs
/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

Mail::flush();

$context
    ->httpResponse()
    ->status(204)
    ->send();
