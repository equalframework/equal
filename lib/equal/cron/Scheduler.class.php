<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\cron;

use equal\organic\Service;
use equal\services\Container;


class Scheduler extends Service {


    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
    }

    public static function constants() {
        return [];
    }

    /**
     * Runs a batch of scheduled tasks.
     *
     * At each call we check all active tasks and execute the ones having the `moment` field (timestamp) overdue.
     * For recurring tasks we update the moment field to the next time, according to repeat axis and repeat step.
     * Non-recurring tasks are deleted once they've been run.
     * #memo - The Scheduler service always operates as root user.
     *
     * @param   int[]   $tasks_ids    Optional array of identifiers of specific tasks to run.
     *
     */
    public function run($tasks_ids=[]) {
        $orm = $this->container->get('orm');

        $selected_tasks_ids = $tasks_ids;
        $now = time();

        if(!count($tasks_ids)) {
            // no specific task is requested, fetch all active recurring tasks that are candidates to execution (limit to max 10 tasks per batch)
            $selected_tasks_ids = $orm->search('core\Task', [['is_active', '=', true], ['is_recurring', '=', true], ['moment', '<=', $now]], ['moment' => 'asc'], 0, 10);
        }

        if($selected_tasks_ids > 0 && count($selected_tasks_ids)) {
            $tasks = $orm->read('core\Task', $selected_tasks_ids, ['id', 'moment', 'status', 'is_exclusive', 'is_recurring', 'repeat_axis', 'repeat_step', 'after_execution', 'controller', 'params']);
            foreach($tasks as $tid => $task) {
                // prevent simultaneous execution of a same task
                if($task['status'] != 'idle') {
                    trigger_error("PHP::Ignoring execution of task that is already running [{$task['id']}] - [{$task['controller']}]", QN_REPORT_INFO);
                    continue;
                }
                // prevent concurrent execution for exclusive tasks
                if($task['is_exclusive']) {
                    $running_tasks_ids = $orm->search('core\Task', ['status', '=', 'running']);
                    if($running_tasks_ids > 0 && count($running_tasks_ids)) {
                        trigger_error("PHP::Ignoring execution of task that is exclusive [{$task['id']}] - [{$task['controller']}] (running tasks ".implode(',', $running_tasks_ids).")", QN_REPORT_INFO);
                        continue;
                    }
                }
                // mark the task as running and update last_run
                $orm->update('core\Task', $tid, ['status' => 'running', 'last_run' => $now]);
                // #todo - add current PID for force stop (exec("kill -9 $pid")) / $context->getPid()
                // if due time has passed or if specific tasks_ids are given, execute the task
                if($task['moment'] <= $now || count($tasks_ids) > 0) {
                    // if no specific tasks_ids are given, update each task
                    if(!count($tasks_ids)) {
                        // #memo - we must start by updating the task : some controllers might run for a duration longer than the remaining time before the next `run()` call
                        if($task['is_recurring']) {
                            $moment = $task['moment'];
                            while($moment < $now) {
                                $moment = strtotime("+{$task['repeat_step']} {$task['repeat_axis']}", $moment);
                            }
                            $orm->update('core\Task', $tid, ['moment' => $moment]);
                        }
                        else {
                            // delete of de-activate task according to `after_execution` property
                            if($task['after_execution'] == 'delete') {
                                $orm->delete('core\Task', $tid, true);
                            }
                            elseif($task['after_execution'] == 'disable') {
                                $orm->update('core\Task', $tid, ['is_active' => false]);
                            }
                            else {
                                // keep task as is (non-recurring)
                            }
                        }
                    }
                    list($status, $log) = ['', ''];
                    try {
                        $body = json_decode($task['params'], true);
                        // run the task
                        $data = \eQual::run('do', $task['controller'], $body, true);
                        $status = 'success';
	                    $log = json_encode($data, JSON_PRETTY_PRINT);
                    }
                    catch(\Exception $e) {
                        // error occurred during execution
                        trigger_error("PHP::Error while running scheduled job [{$task['id']}]: ".$e->getMessage(), QN_REPORT_ERROR);
                        $status = 'error';
                        $msg = $e->getMessage();
                        $data = @unserialize($msg);
                        if(is_array($data)) {
                            $data = json_encode($data, JSON_PRETTY_PRINT);
                        }
                        $log = ($data)?$data:$msg;
                    }
                    // create a new TaskLog holding result
                    $orm->create('core\TaskLog', ['task_id' => $tid, 'status' => $status, 'log' => "<pre>{$log}</pre>"]);
                    // mark the task as idle (so it can be executed again)
                    $orm->update('core\Task', $tid, ['status' => 'idle']);
                }
            }
        }
    }

    /**
     * Schedules a new task.
     * #memo - Scheduler always operates as root user.
     *
     * @param   string    $name         Name of the task to schedule, to ease task identification.
     * @param   integer   $moment       Timestamp of the moment of the first execution.
     * @param   string    $controller   Controller to invoke, with package notation.
     * @param   string    $params       JSON string holding the payload to relay to the controller.
     * @param   boolean   $recurring    Flag to mark the task as recurring.
     */
    public function schedule($name, $moment, $controller, $params, $recurring=false, $repeat_axis='day', $repeat_step='1') {
        $orm = $this->container->get('orm');
        trigger_error("PHP::Scheduling job", QN_REPORT_INFO);

        return $orm->create('core\Task', [
            'name'          => $name,
            'moment'        => $moment,
            'controller'    => $controller,
            'params'        => json_encode($params),
            'is_recurring'  => $recurring
        ]);
    }

    /**
     * Cancels a scheduled task (by deleting it).
     * #memo - Scheduler always operates as root user.
     *
     * @param   string    $name         Name of the task to cancel.
     */
    public function cancel($name) {
        $orm = $this->container->get('orm');
        $tasks_ids = $orm->search('core\Task', ['name', '=', $name]);
        if($tasks_ids > 0 && count($tasks_ids)) {
            return $orm->remove('core\Task', $tasks_ids, true);
        }
        return QN_ERROR_UNKNOWN_OBJECT;
    }

}
