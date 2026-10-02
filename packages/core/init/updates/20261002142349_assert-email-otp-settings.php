<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;

Setting::assert_value('core', 'security', 'auth.email_otp.period', 600);
Setting::assert_value('core', 'security', 'auth.email_otp.digits', 6);
Setting::assert_value('core', 'security', 'auth.email_otp.allowed_failed_attempts', 5);
