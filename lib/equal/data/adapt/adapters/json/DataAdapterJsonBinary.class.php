<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonBinary implements DataAdapter {

    public function getType() {
        return 'json/binary';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        return 'String';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (JSON -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        /*
            $value is expected to be either a base64 string,
            or an array holding data from the $_FILES array (multipart/form-data) having the following keys set:
            [
                'name'      string  filename provided by client
                'type'      string  MIME type / content-type
                'size'      int     size in bytes
                'tmp_name'  string  current path of temp file holding binary data
                'error'     int     error code
            ]
        */
        if(!is_array($value)) {
            // handle data URL notation
            if(substr($value, 0, 5) == 'data:') {
                // extract the base64 part @see RFC2397 (data:[<mediatype>][;base64],<data>)
                $value = str_replace(' ', '+', substr($value, strpos($value, ',')+1));
            }
            $result = base64_decode($value, true);
            if(!$result) {
                throw new \Exception(serialize(["not_valid_base64" => "Invalid base64 data URI value."]), QN_ERROR_INVALID_PARAM);
            }
            if(strlen($result) > constant('UPLOAD_MAX_FILE_SIZE')) {
                throw new \Exception("file_exceeds_maximum_size", QN_ERROR_INVALID_PARAM);
            }
        }
        else {
            // #todo - this should be done with PHP context extraction
            if(!isset($value['tmp_name'])) {
                // throw new \Exception("binary data has not been received or cannot be retrieved", QN_ERROR_UNKNOWN);
                trigger_error("ORM::binary data has not been received or cannot be retrieved", QN_REPORT_WARNING);
                $result = '';
            }
            else {
                if(isset($value['error']) && $value['error'] == 2 || isset($value['size']) && $value['size'] > constant('UPLOAD_MAX_FILE_SIZE')) {
                    throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                }
                $result = file_get_contents($value['tmp_name']);
            }
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> JSON).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        return base64_encode($value);
    }

}
