<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

class UsageBinary extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        if($this->length == 0) {
            $this->length = constant('UPLOAD_MAX_FILE_SIZE');
        }
    }

    public function getConstraints(): array {
        return [
            'size_exceeded' => [
                'message'   => 'Image exceeds length constraint.',
                'function'  =>  function($value) {
                    $len = intval($this->getLength());
                    $strlen = strlen($value);
                    return !( ($len && $strlen > $len) || $strlen > constant('UPLOAD_MAX_FILE_SIZE'));
                }
            ]
        ];
    }

}
