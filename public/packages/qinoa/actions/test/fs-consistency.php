<?php
$params = announce([
    'description'   => 'Checks current installation directories integrity',
    'params'        => [],
    'constants'     => ['FILE_STORAGE_MODE', 'FILE_STORAGE_DIR', 'ROUTING_METHOD', 'ROUTING_CONFIG_DIR']
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
        'path'      =>  QN_BASEDIR.'/spool'
    ],
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  QN_BASEDIR.'/config'
    ],    
    [
        'rights'    =>  QN_R_READ,
        'path'      =>  QN_BASEDIR.'/config/config.inc.php'
    ]    
];

if(ROUTING_METHOD == 'JSON') {
    $paths[] = [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  ROUTING_CONFIG_DIR
    ];
    
    $paths[] = [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  ROUTING_CONFIG_DIR.'/default.json'
    ];    
}

if(FILE_STORAGE_MODE == 'FS') {
    $paths[] = [
        'rights'    =>  QN_R_READ | QN_R_WRITE,
        'path'      =>  FILE_STORAGE_DIR
    ];
}


function check_permissions($path, $mask) {
    if($mask & QN_R_READ) {
        if(!is_readable($path)) return -QN_R_READ;
    }
    if($mask & QN_R_WRITE) {
        if(!is_writable($path)) return -QN_R_WRITE;
    }    
    return true;
}

// check owner
// if(extension_loaded ('posix')) {
//  if ( posix_getuid() != fileowner($file_name) && posix_getgid() != filegroup($file_name))
// }

// check mod
foreach($paths as $item) {
    if(!file_exists($item['path'])) {
        throw new Exception("Missing mandatory directory {$item['path']}", QN_ERROR_INVALID_CONFIG);        
    }
    if( ($res = check_permissions($item['path'], $item['rights'])) <= 0) {
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
        throw new Exception("PHP process has no {$missing} access on {$item['path']}", QN_ERROR_INVALID_CONFIG);
    }
}


// no error, exit code will be 0