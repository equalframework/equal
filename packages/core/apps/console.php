<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns a descriptor of current installation Settings, holding specific values for current User, if applicable.',
    'access'        => [
        'visibility'      => 'private'
    ],
    'params' => [
        'thread_id' => [
            'type'        => 'string',
            'description' => 'Thread identifier of the line (8 hex chars).'
        ],
        'level' => [
            'type'        => 'string',
            'description' => 'Level of the threads to display.',
            'selection'   => [
                'debug',
                'info',
                'warning',
                'error'
            ]
        ],
        'mode' => [
            'type'        => 'string',
            'description' => 'Mode of the threads to display.',
            'selection'   => [
                'php',
                'orm',
                'sql',
                'api',
                'app'
            ]
        ],
        'date' => [
            'type'        => 'date',
            'description' => 'Date (time) of the threads to display.'
        ],
        'limit' => [
            'type' => 'integer',
            'description' => 'Limit of the number of lines to return.'
        ]
    ],
    'response'      => [
        'content-type'      => 'text/plain',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [$providers['context']];

$style_red          = function($text) {return "\e[31;1m".$text."\e[0m";};
$style_green        = function($text) {return "\e[32;1m".$text."\e[0m";};
$style_blue         = function($text) {return "\e[34;1m".$text."\e[0m";};
$style_yellow       = function($text) {return "\e[33;1m".$text."\e[0m";};
$style_white        = function($text) {return "\e[0m".$text."\e[0m";};
$style_bold         = function($text) {return "\e[1m".$text."\e[0m";};
$style_italic       = function($text) {return "\e[3m".$text."\e[0m";};
$style_underline    = function($text) {return "\e[4m".$text."\e[0m";};

/**
 * @var string $level
 * @return string ANSI escape codes to change the color of the level text according to their values
 * example $level = "WARNING" returns yellow color
 */
$style_level = function (string $level) use($style_red, $style_green, $style_blue, $style_yellow, $style_white, $style_bold, $style_italic, $style_underline) {
    if(!is_null($level)) {
        switch(strtoupper($level)) {
            case 'WARNING':
            case E_USER_WARNING:
                return $style_yellow($level);
            case 'DEBUG':
            case E_USER_DEPRECATED:
                return $style_green($level);
            case 'INFO':
            case 'NOTICE':
            case E_USER_NOTICE:
                return $style_blue($level);
            case 'ERROR':
            case 'FATAL':
            case 'Fatal error':
            case 'Parse error':
                return $style_red($level);
            default:
                return $style_white($level);
        }
    }
    return $style_white($level);
};

/**
 * Displays a thread
 * @var Array $thread
 */
$thread_display = function (array $thread) use($style_red, $style_green, $style_blue, $style_yellow, $style_white, $style_bold, $style_italic, $style_underline, $style_level) {

    $text = "";

    $info = array_merge([
        'thread_id' => '',
        'time'      => '',
        'mtime'     => '',
        'level'     => '',
        'mode'      => '',
        'function'  => '',
        'file'      => '',
        'line'      => '',
        'message'   => '',
        'stack'     => []
    ], $thread);

    $text .= $style_red($info['thread_id']).' ';
    $text .= $style_bold($info['time']).' ';
    $text .= $style_bold($info['mtime']).' ';
    $text .= $style_level($info['level']).' ';
    $text .= $info['mode'].' ';
    $text .= $style_underline($info['function']).'() ';
    $text .= "@ {$info['file']} : ";
    $text .= 'line '.$style_bold($info['line']).' ';

    $text .= PHP_EOL;

    if(strlen($info['message'])) {
        // check message format to display in lines if it is an associative array
        $message = json_decode($info['message'], true);
        if(is_array($message)) {
            foreach($message as $value) {
                if(is_array($value)) {
                    $m = "";
                    foreach($value as $id => $val) {
                        if(is_array($val)) {
                            $val = implode(',', $val);
                        }
                        $m .= $style_bold($id).' : '.$style_italic($val).PHP_EOL;
                    }
                    $text .= "$m".PHP_EOL;
                }
                elseif(is_string($value)) {
                    // message displays in italics
                    $text .= $style_italic($value).PHP_EOL;
                }
            }
        }
        else {
            // message displays in italics
            $text .= $style_italic($info['message']);
        }
    }
    $stack = (array) $info['stack'];
    if(count($stack)) {
        for($i = 0, $n = count($stack); $i < $n; $i++) {
            $index = $n - $i - 1;
            $function = (isset($stack[$index]['function']))?$stack[$index]['function']:'';
            $file = (isset($stack[$index]['file']))?$stack[$index]['file']:'';
            $line = (isset($stack[$index]['line']))?$stack[$index]['line']:'';
            $text .= PHP_EOL.(($i == ($n - 1))?' └ ':' ├ ');
            $text .= "{$function} @ {$file} {$line} ";
        }
    }
    return $text;
};

/**
 * Filters a thread arguments are given in params
 * @return Array $thread | null
 */
$thread_filter = function (array $thread, array $params) {
    if(isset($params['mode']) && $params['mode'] !== '') {
        return (strcasecmp($thread['mode'], $params['mode']) == 0);
    }
    if(isset($params['level']) && isset($params['level']) != '') {
        return (strcasecmp($thread['level'], $params['level']) == 0);
    }
    if(isset($params['thread_id']) && $params['thread_id'] != '') {
        return (strpos($thread['thread_id'], $params['thread_id']) === 0);
    }
    if(isset($params['date'])) {
        $delta = strtotime($thread['time']) - intval($params['date']);
        return ($delta >= 0 && $delta <= 86400);
    }
    return true;
};

$result = [];

if(file_exists(QN_BASEDIR.'/log/eq_error.log')) {
    // read raw data from pointer log file
    $fp = fopen(QN_BASEDIR.'/log/eq_error.log', "r");
    $result[] = "START LOG";
    $i = 0;
    $prev_thread_id = 0;
    if($fp) {
        while((($data = stream_get_line($fp, 65535, PHP_EOL)) !== false)) {
            if(isset($params["limit"]) && $i > $params["limit"]) {
                break;
            }
            $thread = json_decode($data, true);
            if(!is_null($thread) && $thread_filter($thread, $params)) {
                if($prev_thread_id) {
                    if($thread['thread_id'] != $prev_thread_id) {
                        $result[] = "===========================================================================================================================================";
                    }
                    else {
                        $result[] = "-------------------------------------------------------------------------------------------------------------------------------------------";
                    }
                }
                foreach(explode(PHP_EOL, $thread_display($thread)) as $line) {
                    $result[] = $line;
                }

                $prev_thread_id = $thread['thread_id'];
                $i++;
            }
        }
        fclose($fp);
    }
    $result[] = PHP_EOL."END LOG \e[0m";
}

$context->httpResponse()
        ->body(implode(PHP_EOL, $result))
        ->send();
