<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageImage extends Usage {

    public function getType(): string {
        return 'image';
    }

    public function getSqlType(): string {
        return 'longblob';
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

    public function export($value, $lang=DEFAULT_LANG): string {
        return $value;
    }

}
