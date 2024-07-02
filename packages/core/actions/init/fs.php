<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
$params = eQual::announce([
    'description'   => 'Checks current installation directories integrity.',
    'params'        => [],
    'constants'     => ['FILE_STORAGE_MODE', 'HTTP_PROCESS_USERNAME']
]);

// array holding files and directories to be tested
$paths = [
    [
        'rights'    =>  EQ_R_READ | EQ_R_WRITE,
        'path'      =>  QN_LOG_STORAGE_DIR
    ],
    [
        'rights'    =>  EQ_R_READ | EQ_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/cache'
    ],
    [
        'rights'    =>  EQ_R_READ | EQ_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/bin'
    ],
    [
        'rights'    =>  EQ_R_READ | EQ_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/spool'
    ],
    [
        'rights'    =>  EQ_R_READ,
        'path'      =>  EQ_BASEDIR.'/lib'
    ],
    [
        'rights'    =>  EQ_R_READ,
        'path'      =>  EQ_BASEDIR.'/config'
    ],
    [
        'rights'    =>  EQ_R_READ,
        'path'      =>  EQ_BASEDIR.'/config/routing'
    ]
];

if(constant('FILE_STORAGE_MODE') == 'FS') {
    $paths[] = [
        'rights'    =>  EQ_R_READ | EQ_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/bin'
    ];
}

$uid = 0;

$username = constant('HTTP_PROCESS_USERNAME');

// get UID of a user by its name
$output = [];
$result_code = 0;
exec("id -u \"$username\" 2>&1", $output, $result_code);

if($result_code !== 0) {
    throw new Exception('uid_unavailable : '.implode("\n", $output), EQ_ERROR_UNKNOWN);
}

$output = (array) $output;

if(count($output)) {
    $uid = intval(reset($output));
}

if(!$uid) {
    throw new Exception(serialize(['unknown_user' => $username]), EQ_ERROR_INVALID_CONFIG);
}

// set mod
foreach($paths as $item) {
    if(!file_exists($item['path'])) {
        continue;
    }

    ['path' => $path, 'rights' => $mask] = $item;

    chgrp($path, $uid);

    $perms = fileperms($path);

    if($mask & EQ_R_READ) {
        $perms |= 0040;
    }

    if($mask & EQ_R_WRITE) {
        $perms |= 0020;
    }

    chmod($path, $perms);
}

// no error, exit code will be 0