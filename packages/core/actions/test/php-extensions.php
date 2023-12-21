<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
$params = eQual::announce([
    'description'   => 'Checks current installation PHP loaded extensions.',
    'help'          => "This controller raises an exception if one or more mandatory PHP extensions are not loaded in the current environment.\n
                        If the `dbms` param is set to True, then it will also raise an exception if none of the supported DBMS are supported through a specific PHP extension.",
    'params'        => [
        'dbms'	=>  [
            'type'          => 'boolean',
            'description'   => 'Flag for testing support for at least one DBMS.',
            'default'       => false
        ]
    ]
]);

if(!is_callable('get_loaded_extensions')) {
    throw new Exception("missing_dependency", QN_ERROR_INVALID_CONFIG);
}

$extensions = get_loaded_extensions();

// mandatory PHP extensions
$ext_mandatory = ['gd', 'intl', 'iconv','json', 'libxml', 'mbstring', 'SimpleXML', 'zip'];

// supported DBMS extensions
$ext_dbms = ['sqlite3', 'mysqli', 'sqlsrv'];


$diff_mandatory = array_diff($ext_mandatory, $extensions);
if(count($diff_mandatory)) {
    throw new Exception(serialize(['missing_extension' => $diff_mandatory]), QN_ERROR_INVALID_CONFIG);
}

if($params['dbms']) {
    $diff_dbms = array_diff($ext_dbms, $extensions);
    if(count($diff_dbms) >= count($ext_dbms)) {
        throw new Exception(serialize(['missing_one_amongst' => $diff_dbms]), QN_ERROR_INVALID_CONFIG);
    }
}

// no error, exit code will be 0