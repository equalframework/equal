<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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

    public function getConstraints(): array {
        return [
            'invalid_phone' => [
                'message'   => 'String does not comply with usage constraint.',
                'function'  =>  function($value) {
                    return (bool) (preg_match('/^((\+[1-9]{2,3})|00|0)+([0-9]){1,13}$/', $value));
                }
            ]
        ];
    }

    public function generateRandomValue(): string {
        return DataGenerator::phoneNumberE164();
    }

}
