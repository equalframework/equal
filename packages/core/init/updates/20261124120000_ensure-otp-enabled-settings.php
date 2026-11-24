<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;

Setting::ensure_value('core', 'security', 'auth.totp.enabled', false);
Setting::ensure_value('core', 'security', 'auth.email_otp.enabled', false);
