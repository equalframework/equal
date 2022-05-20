<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Task;
use core\setting\Setting;

list($params, $providers) = announce([
    'description'   => "Run a batch of scheduled task. Expected to be run with CLI `$ ./equal.run --do=cron_run`",
    'params'        => [
    ],
    'access' => [
        'visibility'   => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'cron'] 
]);


/**
 * @var \equal\php\Context $context
 * @var \equal\cron\Scheduler $cron
 */
list($context, $cron) = [$providers['context'], $providers['cron']];


// run scheduled tasks that require it
$cron->run();

$context->httpResponse()
        ->status(204)
        ->send();