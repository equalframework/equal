<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Task;

$email_otp_prune_task = Task::search([
        'controller', '=', 'core_security_challenge_emailotp-prune'
    ])
    ->first();

if(!$email_otp_prune_task) {
    /** @var equal\cron\Scheduler $cron */
    ['cron' => $cron] = eQual::inject(['cron']);

    $cron->schedule(
        'core.security.email_otp.prune',
        strtotime('tomorrow 03:00'),
        'core_security_challenge_emailotp-prune',
        [],
        true
    );
}

