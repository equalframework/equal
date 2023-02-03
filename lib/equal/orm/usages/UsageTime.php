<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;
use core\setting\Setting;

class UsageTime extends Usage {

    /*
        time/plain (h:m:s)
     */
    public function getConstraints(): array {
        /*
        switch($this->getSubtype()) {
            default:
        }
        */
        return [];
    }

}
