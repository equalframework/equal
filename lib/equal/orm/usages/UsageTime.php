<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\data\DataGenerator;

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

    public function generateRandomValue(): string {
        return DataGenerator::time();
    }

}
