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

    public function validate($value) {
        $len = intval($this->getLength());
        $strlen = strlen($value);
        if( ($len && $strlen > $len) || $strlen > UPLOAD_MAX_FILE_SIZE) {
            throw new \Exception(serialize(["broken_usage" => "Image exceeds length constraint."]), QN_ERROR_INVALID_PARAM);
        }
    }

    public function export($value, $lang=DEFAULT_LANG): string {
        return $value;
    }

}
