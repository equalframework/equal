<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
$params = announce([
    'description'   => 'Checks current installation directories integrity',
    'params'        => [],
    'constants'     => ['FILE_STORAGE_MODE', 'ROUTING_METHOD']
]);

// array holding files and directories to be tested
$paths = [
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_LOG_STORAGE_DIR
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_BASEDIR.'/cache'
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_BASEDIR.'/bin'
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_BASEDIR.'/spool'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  QN_BASEDIR.'/config'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  QN_BASEDIR.'/config/schema.json'
    ]
];

if(ROUTING_METHOD == 'JSON') {
    $paths[] = [
        'rights'    =>  QN_R_READ,
        'path'      =>  QN_BASEDIR.'/config/routing'
    ];
}

if(FILE_STORAGE_MODE == 'FS') {
    $paths[] = [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_BASEDIR.'/bin'
    ];
}


function check_permissions($path, $mask, $uid=0) {
    if(!$uid) {
        if($mask & QN_R_READ) {
            if(!is_readable($path)) return -QN_R_READ;
        }
        if($mask & QN_R_WRITE) {
            if(!is_writable($path)) return -QN_R_WRITE;
        }
    }
    else {
        // #todo - allow uid <> gid
        $perms = fileperms($path);
        // get the user owner of the file
        $fuid = fileowner($path);
        // get the group owner of the file
        $fgid = filegroup($path);

        if($fuid == $uid) {
            // check user perms
            if($mask & QN_R_READ) {
                if(!($perms & 0x0100)) return -QN_R_READ;
            }
            if($mask & QN_R_WRITE) {
                if(!($perms & 0x0080)) return -QN_R_WRITE;
            }
        }
        else if($fgid == $uid) {
            // check group perms
            if($mask & QN_R_READ) {
                if(!($perms & 0x0020)) return -QN_R_READ;
            }
            if($mask & QN_R_WRITE) {
                if(!($perms & 0x0010)) return -QN_R_WRITE;
            }
        }
    }
    return true;
}


$uid = 0;
// #todo - add HTTP_PROCESS_USERNAME
$username = 'www-data';
// get UID of a use by its name
if(exec("id -u \"$username\"", $output)) {    
    $uid = intval(reset($output));
}

// check mod
foreach($paths as $item) {
    if(!file_exists($item['path'])) {
        throw new Exception("Missing mandatory node {$item['path']}", QN_ERROR_INVALID_CONFIG);
    }
    if( ($res = check_permissions($item['path'], $item['rights'], $uid)) <= 0) {
        switch(-$res) {
            case QN_R_READ:
                $missing = 'read';
                break;
            case QN_R_WRITE:
                $missing = 'write';
                break;
            default:
                $missing = '';
        }
        throw new Exception("PHP or HTTP process has no {$missing} access on {$item['path']}", QN_ERROR_INVALID_CONFIG);
    }
}


// no error, exit code will be 0