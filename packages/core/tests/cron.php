<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
                                        list($params, $providers) = announce([
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
                                        list($params, $providers) = announce([
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
                                            [ 'id' => 1, 'lang' => 'fr' ]
                                        );
                                        return ($res >= 0);
                                    }

        ]
];