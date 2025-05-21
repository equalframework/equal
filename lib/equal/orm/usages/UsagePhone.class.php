<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


use equal\data\DataGenerator;

class UsagePhone extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            $this->length = 17;
        }
    }

    /**
     * Validates a phone number in a normalized format (only digits and an optional + or 00 prefix).
     *
     * Supported formats:
     * - International E.164:           +32478456789
     * - International alternative:     0032478456789
     * - National with leading zero:    0478456789
     * - Special national numbers:      080019400, 116000
     *
     * Rejected examples:
     * - Too short / ambiguous:         123
     * - Not normalized (contains punctuation/spaces): +32 (478) 45.67.89
     */
     public function getConstraints(): array {
         return [
             'invalid_phone' => [
                 'message'   => 'String does not comply with phone format.',
                 'function'  =>  function($value) {
                    return (bool) (preg_match('/^(\+|00)?[1-9][0-9]{6,14}$|^0[1-9][0-9]{5,13}$|^([1-9][0-9]{3,6})$/', $value));
                 }
             ]
         ];
    }

    public function generateRandomValue(): string {
        return DataGenerator::phoneNumberE164();
    }

}
