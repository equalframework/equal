<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => "Run a batch of scheduled task. Expected to be run with CLI `$ ./equal.run --do=cron_run`",
    'params'        => [
        'id' =>  [
            'type'          => 'integer',
            'description'   => 'Optional identifier of the specific task to run. If not given, all due tasks are candidates to execution.',
            'default'       => 0
        ]
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


// run the scheduled tasks that require it
$cron->run($params['id']);

$context->httpResponse()
        ->status(204)
        ->send();