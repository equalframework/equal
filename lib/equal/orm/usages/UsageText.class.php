<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageText extends Usage {

    public function getType(): string {
        return 'text';
    }

    public function getSqlType(): string {
        $len = $this->getLength();
        if($len == 'short') {
            $len = 255;
        }
        if($len == 'medium') {
            $len = 16777215;
        }
        else if($len == 'long') {
            $len = 4294967295;
        }
        if(is_numeric($len)) {
            if($len <= 255) {
                return 'varchar('.$len.')';
            }
            else if($len <= 65535) {
                return 'text';
            }
            else if($len <= 16777215) {
                return 'mediumtext';
            }
            else if($len <= 4294967295) {
                return 'longtext';
            }
        }
        return 'text';
    }

    public function validate($value): bool {
        $len = $this->getLength();
        switch($this->getSubtype()) {
            case 'plain':
                // expected len is either empty or a single int
                $len = intval($len);
                if($len && strlen($value) > $len) {
                    throw new \Exception(serialize(["broken_usage" => "String exceeds length constraint."]), QN_ERROR_INVALID_PARAM);
                }
                break;
            case 'html':
                break;
            case 'xml':
                break;
            case 'markdown':
                break;
            case 'wiki':
                break;
        }
        return true;
    }

    public function export($value, $lang=DEFAULT_LANG): string {
        return $value;
    }

}
