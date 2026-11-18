<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\http\HttpRequest;
use core\User;
use core\Group;

// todo : convert to AAA logic : Arrange, Act, Assert (, Restore)

$tests = [

    '0101' => [
            'description'       =>  "Get cron provider.",
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                                        list($params, $providers) = eQual::announce([
                                            'providers' => ['cron']
                                        ]);
                                        return is_callable([$providers['cron'], 'run'], true);
                                    }

        ],
    '0102' => [
            'description'       =>  "Schedule a task.",
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                                        list($params, $providers) = eQual::announce([
                                            'providers' => ['cron']
                                        ]);
                                        /**
                                         * @var \equal\cron\Scheduler $cron
                                        */
                                        $cron = $providers['cron'];
                                        $res = $cron->schedule(
                                            "task.test",
                                            time() +  86400,
                                            'test_test',
                                            [ 'id' => 1, 'lang' => 'fr' ],
                                            recurring: true,
                                            repeat_axis: 'hour',
                                            repeat_step: 2,
                                            is_exclusive: true
                                        );
                                        if($res < 0) {
                                            return false;
                                        }

                                        $orm = ObjectManager::getInstance();
                                        $tasks = $orm->read(
                                            'core\Task',
                                            $res,
                                            ['is_recurring', 'repeat_axis', 'repeat_step', 'is_exclusive']
                                        );
                                        $orm->remove('core\Task', [$res]);

                                        return isset($tasks[$res])
                                            && $tasks[$res]['is_recurring'] === true
                                            && $tasks[$res]['repeat_axis'] === 'hour'
                                            && $tasks[$res]['repeat_step'] === 2
                                            && $tasks[$res]['is_exclusive'] === true;
                                    }

        ]
];
