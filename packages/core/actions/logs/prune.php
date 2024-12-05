<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Author: The eQual Framework Contributors
    Original Author: Cedric Francoys
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Log;
use core\setting\Setting;

list($params, $providers) = eQual::announce([
    'description'   => 'Prunes log items based on the retention duration defined in the auto-vacuum setting.',
    'params'        => [],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'constants'     => ['LOGS_EXPIRY_DELAY'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;


// retrieve logs expiry delay, in months
$delay = Setting::get('core', 'main', 'logs.expiry', constant('LOGS_EXPIRY_DELAY'));

// compute pivot date for removing older logs
$time = strtotime("-$delay months");

if($time >= time() || $time <= 0) {
    throw new Exception('unexpected_error', EQ_ERROR_UNKNOWN);
}

Log::search(['created', '<=', $time])->delete(true);

$context->httpResponse()
        ->status(204)
        ->send();
