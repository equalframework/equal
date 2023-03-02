<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Task;

list($params, $providers) = announce([
    'description'   => "Run a batch of scheduled task. Expected to be run with CLI `$ ./equal.run --do=cron_run`",
    'params'        => [
        'ids' =>  [
            'type'              => 'one2many',
            'foreign_object'    => Task::getType(),
            'description'       => 'Optional identifiers of the specific tasks to run. If not given, all due tasks are candidates to execution.',
            'default'           => []
        ]
    ],
    'access' => [
        'visibility'   => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'cron', 'access']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\cron\Scheduler               $cron
 * @var \equal\access\AccessController      $am
 */
list($context, $cron, $am) = [$providers['context'], $providers['cron'], $providers['access']];

if(!$am->hasGroup('admin')) {
    throw new Exception('restricted_operation', QN_ERROR_NOT_ALLOWED);
}

// run the scheduled tasks that require it
$cron->run($params['ids']);

$context->httpResponse()
        ->status(204)
        ->send();
