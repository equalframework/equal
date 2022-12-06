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
    //0xxx : calls related to QN methods
    '0101' => array(
                'description'       =>  "Get cron provider",
                'return'            =>  array('boolean'),
                'expected'          =>  true,
                'test'              =>  function () {
                                            list($params, $providers) = announce([
                                                'providers' => ['cron']
                                            ]);
                                            /**
                                             * @var \equal\cron\Scheduler $cron
                                            */
                                            $cron = $providers['cron'];
                                            $cron->schedule(
                                                "booking.quote.reminder.test",
                                                time() +  86400,
                                                'test_test',
                                                [ 'id' => 1, 'lang' => 'fr' ]
                                            );
                                            return true;
                                        }

                )
];