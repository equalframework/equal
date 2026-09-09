<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Change;
use core\Log;

$oldest_log = Log::search([], ['limit' => 1, 'sort' => ['id' => 'asc']])->first();

Change::search(['log_id', '<', $oldest_log['id']])->delete(true);
