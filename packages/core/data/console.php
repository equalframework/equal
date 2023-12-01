<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns a descriptor of current installation Settings, holding specific values for current User, if applicable.',
    'access'        => [
        'visibility'        => 'public'
    ],
    'params' => [
        'thread_id' => [
            'type' => 'string',
            'description' => 'Thread_id of the line'
        ],
        'level' => [
            'type'         => 'string',
            'description' => 'Level of the  WARNING | DEBUG | INFO | ERROR'
        ],
        'mode' => [
            'type'        => 'string',
            'description' => 'php | orm | sql | api | app'
        ],
        'time' => [
            'type' => 'string',
            'description' => 'Indicates the time of the log'

        ],
        'mtime' => [
            'type' => 'string',
            'description' => 'Mtime allows to look for a precise time'

        ],
        'help' => [
            'type' => 'boolean',
            'description' => 'Set to true to display help'
        ],
        'limit' => [
            'type' => 'integer',
            'description' => 'Returns the selected number of lines'
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


list($context, $om, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

/**
 * @var string $level
 * @return string ANSI escape codes to change the color of the level text according to their values
 * example $level = "WARNING" returns yellow color
 */
function calColor(string $level)
{
    $green = "\e[32;1m";
    $red = "\e[31;1m";
    $blue = "\e[34;1m";
    $yellow = "\e[33;1m";
    $white = "\e[0m";

    if (is_null($level)) return $white;
    switch (strtoupper($level)) {
        case 'WARNING':
        case E_USER_WARNING:
            return $yellow;
        case 'DEBUG':
        case E_USER_DEPRECATED:
            return $green;
        case 'INFO':
        case 'NOTICE':
        case E_USER_NOTICE:
            return $blue;
        case 'ERROR':
        case 'FATAL':
        case 'Fatal error':
        case 'Parse error':
            return $red;
        default:
            return $white;
    }
}

/**
 * Displays a thread
 * @var Array $thread
 */
function displayThread(array $thread)
{
    $green = "\e[32;1m";
    $red = "\e[31;1m";
    $white = "\e[0m";
    $bold = "\e[00;1m";
    $text = "";

    $text .= "$red {$thread['thread_id']} $white";
    $text .= "$green {$thread['time']} $white";
    if ($thread['mtime']) {
        $text .= "$bold {$thread['mtime']} $white";
    }
    if (is_string($thread['level'])) {
        $text .=  calColor($thread['level']) . "[{$thread['level']}]$white";
    }
    if(isset($thread['mode'])) {
        $text .= " {$thread['mode']}";
    }
    if(isset($thread['function'])) {
        $text .= "{$bold} {$thread['function']} ";
    }
    if(isset($thread['file'])) {
        $text .= "{$white}@ {$thread['file']} : ";
    }
    if(isset($thread['line'])) {
        $text .= "line $bold{$thread['line']}$white ";
    }

    $text .= PHP_EOL;

    if(is_string($thread['message'])) {
        // check message format to display in lines if it is an associative array
        if(is_array(json_decode($thread['message'], true))) {
            $newMessage = json_decode($thread['message'], true);
            foreach($newMessage as $val) {
                if(is_array($val)) {
                    $m = "";
                    foreach ($val as $id => $v) {
                        $m .= "$white {$bold}{$id}{$white} : \e[3m{$v} \e[23m".PHP_EOL;
                    }
                    $text .= "$m".PHP_EOL;
                }
                elseif (is_string($val)) {
                    // message displays in italics
                    $text .= "\e[3m{$val} \e[23m".PHP_EOL;
                }
            }
        }
        else {
            $text .= "\e[3m{$thread['message']} \e[23m".PHP_EOL;
        }
        // message displays in italics
    }
    if (isset($thread['stack'])) {
        for ($i = 0; $i < count($thread['stack']); $i++) {
            $stack = $thread['stack'][count($thread['stack']) - $i - 1];
            $text .= $i == count($thread['stack']) - 1 ? "\n └ " : "\n ├ ";
            $text .= "{$stack['function']} @ {$stack['file']} {$stack['line']} $white";
        }
    }
    return ($text);
}

/**
 * Filters a thread arguments are given in params
 * @return Array $thread | null
 */
function filterThreadByParams(array $thread, array $params)
{
    if (isset($params['mode']) && $params['mode'] !== '' && $thread['mode'] == strtoupper($params['mode'])) {
        return $thread;
    }
    if (isset($params['level']) && isset($params['level']) != '' && $thread['level'] == strtoupper($params['level'])) {
        return $thread;
    };
    if (isset($params['thread_id']) && $params['thread_id'] != '' && $thread['thread_id'] == $params['thread_id']) {
        return $thread;
    }
    if (isset($params['mtime']) && $params['mtime'] != '' && $thread['mtime'] == $params['mtime']) {
        return $thread;
    }
    if (isset($params['time']) && $params['time'] != '' && str_contains(($thread['time']), $params['time'])) {
        return $thread;
    }
    if (!isset($params['time']) && !isset($params['mtime']) && !isset($params['thread_id']) && !isset($params['level']) && !isset($params['mode'])) {
        return $thread;
    }
}

if (file_exists(QN_BASEDIR.'/log/eq_error.log')) {
    // read raw data from pointer log file
    $fp = fopen(QN_BASEDIR.'/log/eq_error.log', "r");
    echo "START LOG".PHP_EOL;
    $cpt = 0;
    if ($fp) {
        while ((($data = stream_get_line($fp, 65535, PHP_EOL)) !== false) && ((isset($params["limit"]) && $cpt <= $params["limit"]) || !isset($params["limit"]))) {

            $thread = json_decode($data, true);
            if (!is_null($thread)) {
                $filteredThread = filterThreadByParams($thread, $params);
                if (!is_null($filteredThread)) {
                    print(displayThread($thread));
                    echo (PHP_EOL."---------------------------------------------------------------------------------------------------------------------------------------------".PHP_EOL);
                };
            }
            $cpt++;
        }
        fclose($fp);
    }
    echo PHP_EOL."END LOG \e[0m ".PHP_EOL;
};

// $context->httpResponse()
//     ->body($text)
//     ->send();
