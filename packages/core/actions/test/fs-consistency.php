<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
$params = eQual::announce([
    'description'   => 'Checks current installation directories integrity',
    'params'        => [],
    'constants'     => ['FILE_STORAGE_MODE', 'HTTP_PROCESS_USERNAME']
]);

// array holding files and directories to be tested
$paths = [
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  QN_LOG_STORAGE_DIR
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/cache'
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/bin'
    ],
    [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/spool'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  EQ_BASEDIR.'/lib'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  EQ_BASEDIR.'/config'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  EQ_BASEDIR.'/config/schema.json'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  EQ_BASEDIR.'/config/routing'
    ]
];


if(constant('FILE_STORAGE_MODE') == 'FS') {
    $paths[] = [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  EQ_BASEDIR.'/bin'
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

        // check user permissions
        if($fuid == $uid) {
            if( ($mask & QN_R_READ) && !($perms & 0x0100)) {
                return -QN_R_READ;
            }
            if( ($mask & QN_R_WRITE) && !($perms & 0x0080)) {
                return -QN_R_WRITE;
            }
        }
        // check group permissions
        else if($fgid == $uid) {
            if( ($mask & QN_R_READ) && !($perms & 0x0020)) {
                return -QN_R_READ;
            }
            if( ($mask & QN_R_WRITE) && !($perms & 0x0010)) {
                return -QN_R_WRITE;
            }
        }
        // check others permission
        else {
            if( ($mask & QN_R_READ) && !($perms & 0x0004)) {
                return -QN_R_READ;
            }
            if( ($mask & QN_R_WRITE) && !($perms & 0x0002)) {
                return -QN_R_WRITE;
            }
        }
    }
    return true;
}


$uid = 0;

$username = constant('HTTP_PROCESS_USERNAME');

// get UID of a user by its name
exec("id -u \"$username\" 2>&1", $output);

if(count($output)) {
    $uid = intval(reset($output));
}

if(!$uid) {
    throw new Exception(serialize(['unknown_user' => $username]), QN_ERROR_INVALID_CONFIG);
}

// check mod
foreach($paths as $item) {
    if(!file_exists($item['path'])) {
        throw new Exception(serialize(['missing_mandatory_node' => $item['path']]), QN_ERROR_INVALID_CONFIG);
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