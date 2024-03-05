<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
$params = eQual::announce([
    'description'   => 'Outputs plain text version of PHP current configuration (from `phpinfo`).',
    'help'          => 'This controller might reveal details about current PHP config. It is therefore marked as private and is meant to be used in CLI only.',
    'params'        => [
        'json' => [
            'type'          => 'boolean',
            'default'       => false,
            'description'   => 'Force output to a JSON formatted string.'
        ]
    ],
    'access'        => [
        'visibility'        => 'private'
    ],
    'response'      => [
        'content-type'      => 'text/plain',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ]
]);

$info = phpinfo_array();

if($params['json']) {
    echo json_encode($info, JSON_PRETTY_PRINT);
}
else {
    $i = 0;
    foreach($info as $section => $content) {
        if($i > 0) {
            echo PHP_EOL;
        }
        echo $section.PHP_EOL;
        echo str_pad('', strlen($section), '-').PHP_EOL;
        foreach($content as $subsection => $value) {
            if(is_array($value)) {
                echo '    '.$subsection.PHP_EOL;
                echo '    '.str_pad('', strlen($subsection), '-').PHP_EOL;
                foreach($value as $key => $val) {
                    echo '    '.'    '.str_pad($key, 41, ' ').'=> '.$val.PHP_EOL;
                }
            }
            else {
                echo '    '.str_pad($subsection, 41, ' ').'=> '.$value.PHP_EOL;
            }
        }
        ++$i;
    }
}


function phpinfo_array(){
    ob_start();
    phpinfo(INFO_GENERAL|INFO_CONFIGURATION|INFO_MODULES);
    $output = ob_get_clean();

    $map_info = [];
    $data = explode("\n", $output);
    $section = '';
    $subsection = null;
    foreach($data as $line) {
        if(strlen(trim($line)) <= 0) {
            continue;
        }
        if(strpos($line, "\e[1m") !== false) {
            continue;
        }
        if(strpos($line, "Directive =>") !== false) {
            continue;
        }
        if(strpos($line, '=>') === false) {
            // if there is a tab, it is a subsection
            if(strpos($line, "    ") !== false) {
                $subsection = trim($line);
            }
            else {
                if(strpos($line, ',') !== false) {
                    // ignore no section line
                    continue;
                }
                $section = trim($line);
                $subsection = null;
            }
        }
        else {
            if(!isset($map_info[$section])) {
                $map_info[$section] = [];
            }
            if($subsection && !isset($map_info[$section][$subsection])) {
                $map_info[$section][$subsection] = [];
            }
            list($key, $value) = explode('=>', $line, 2);
            $key = trim($key);
            $key = str_replace("\e[0m", '', $key);
            if($subsection) {
                $map_info[$section][$subsection][$key] = trim($value);
            }
            else {
                $map_info[$section][$key] = trim($value);
            }
        }
    }

    return $map_info;
}