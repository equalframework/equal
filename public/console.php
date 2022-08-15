<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
/**
* This file is supposed to remain standalone (free of any dependency other than the eq_error.log file)
* For security reasons its access should be restricted to development environment only.
*/
define('QN_LOG_FILE', '../log/eq_error.log');
define('PHP_LOG_FILE', '../log/error.log');
 
date_default_timezone_set('Europe/Brussels');

error_reporting(0);

function normalize_error_code($errcode) {
    switch($errcode) {
        case 'Notice':
        case 'NOTICE':
        case 'DEBUG':
        case E_USER_NOTICE:
            return E_USER_NOTICE;
        case 'WARNING':
        case E_USER_WARNING:
            return E_USER_WARNING;
        case 'ERROR':            
        case E_USER_ERROR:
            return E_USER_ERROR;
        case 'FATAL':
        case E_ERROR:
        case 'Fatal error':
        case 'Parse error':
        case 'Catchable fatal error':
            return E_ERROR;
    }
    return E_ALL;
}

function get_error_class($errcode) {
    $type = $errcode;
    $icon = 'fa-info';
    $class= '';
    switch($errcode) {
        case 'Notice':
        case 'NOTICE':
        case 'DEBUG':
        case E_USER_NOTICE:
            $type = 'Debug';
            $icon = 'fa-bug';
            $class = 'text-success';
            break;
        case 'WARNING':
        case E_USER_WARNING:
            $type = 'Warning';
            $icon = 'fa-warning';
            $class = 'text-warning';
            break;
        case 'ERROR':            
        case E_USER_ERROR:
            $type = 'Error';
            $icon = 'fa-times-circle';
            $class = 'text-danger';
            break;
        case 'FATAL':
        case E_ERROR:
            $type = 'Fatal error';
        case 'Fatal error':
        case 'Parse error':
            $icon = 'fa-ban';
            $class = 'text-danger';
            break;
    }
    return [$type, $icon, $class];
}

function get_stack($stack) {    
    $res = "<table style=\"margin-left: 20px;\">".PHP_EOL;
    for($i = 0, $n = count($stack); $i < $n; ++$i) {
        $entry = $stack[$i];
        list($function, $file) = explode('@', $entry);
        $function = str_replace('#', strval($n-$i).'.', $function);
        $res .= "<tr>
            <td> $function&nbsp;</td>
            <td><b>@</b>&nbsp;$file</td>
        </tr>".PHP_EOL;
        
    }
    $res .= '</table>'.PHP_EOL;
    return $res;
}

function get_line($entry) {
    list($thread_id, $datetime, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
    list($type, $icon, $class) = get_error_class($errcode);

    $msg = urldecode($msg);

    $in = (strlen($origin))?"<b>in</b> <code class=\"$class\">$origin</code>":'';
    return "<div style=\"margin-left: 10px;\"><a class=\"$class\" title=\"$type\" ><i class=\"fa $icon\" aria-hidden=\"true\"></i> $datetime $type</a> <b>@</b> [<code class=\"$class\">{$file}:{$line}</code>] $in: $msg</div>".PHP_EOL;
}

function get_header($thread_id, $selected_thread_id, $previous_thread=null, $next_thread=null) {
    global $threads_dt, $threads_ec;
    
    if(isset($threads_dt[$thread_id])) {
        $datetime = $threads_dt[$thread_id];
    }
    else $datetime = '';
    
    if(substr($thread_id, 0, 3) == 'PHP') {
        $thread_pid = substr($thread_id, 3);        
    }
    else {
        $thread_pid = $thread_id;
    }
    list($up, $down, $active) = ['', '', ''];
    if($previous_thread) {
        $up = "<a target=\"details\" href=\"?thread_id=$previous_thread\"><i class=\"fa fa-caret-up\"></i></a>";
        $down = "<a target=\"details\" href=\"?thread_id=$next_thread\"><i class=\"fa fa-caret-down\"></i></a>";
    }
    else {
        if($selected_thread_id == $thread_id) {
            $active = 'active';        
        }        
    }
    
    if(isset($threads_dt[$thread_id])) {
        $errcode = $threads_ec[$thread_id];
    }
    else {
        $errcode = E_ERROR;
    }
    
    list($type, $icon, $class) = get_error_class($errcode);
    
    // return "<div style=\"margin-left: 10px; $color\"><a title=\"PID $thread_pid\" href=\"?thread_id=$thread_id\">".date('Y-m-d H:i:s', explode(' ', $thread_time)[1])." ".$thread_script."</a>&nbsp;{$up}&nbsp;{$down}</div>".PHP_EOL;    
    return "<div id=\"$thread_pid\" class=\"$active\" style=\"margin-left: 10px;\"><a class=\"$class\" title=\"$type\" ><i class=\"fa $icon\" aria-hidden=\"true\"></i> </a> <div style=\"display: inline-block; width: 150px;\"> <a target=\"details\" title=\"PID $thread_pid\" href=\"?thread_id=$thread_id\" onclick=\"document.querySelector('div.active').className='';document.getElementById('$thread_pid').className='active';\">thread ".$thread_pid."</a>&nbsp;{$up}&nbsp;{$down}</div><div style=\"display: inline-block;\">$datetime</div></div>".PHP_EOL;        
}

$history = [];
// quick fix to get datetime inside get_header function
$threads_dt = [];
// quick fix to get errcode inside get_header function
$threads_ec = [];

// todo : && if(!file_exists(PHP_LOG_FILE))
if(!file_exists(QN_LOG_FILE)) die('no log found');
// read raw content
$log = file_get_contents(QN_LOG_FILE);
// extract content to an array or rows
$lines = explode(PHP_EOL, $log);
// workaround to make sure threads ids are in a sequence (which is not the cas with concurrent processes)
sort($lines);
// count lines
$len = count($lines);
//get the last line
$k = 1;

if($len < 2) die('empty file');

if(isset($_GET['thread_id']) && strlen($_GET['thread_id']) > 0) {
    $thread_id = $_GET['thread_id'];  
}
else {
    $entry = $lines[$len-$k-1];
    // skip preceeding lines
    while(substr($entry, 0, 1) == '#' || strlen($entry) < 20) {
        ++$k;
        if(($len-$k) <= 0) die();
        $entry = $lines[$len-$k-1];
    }
    // fetch the thread_id
// todo : use a function to fetch an entry, and validate it based on values count (otherwise, ignore)    
    list($thread_id, $datetime, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
    list($datetime, $microtime) = explode('+', $datetime);    
    list($month, $day, $year, $hour, $minute, $second) = sscanf($datetime, "%d-%d-%d %d:%d:%d");    
    $timestamp = mktime($hour, $minute , $second, $month, $day, $year);
    // syntax:  $this->thread_id.';'.time().';'.$code.';'.$origin.';'.$trace['file'].';'.$trace['line'].';'.$msg.PHP_EOL;
    $threads_dt[$thread_id] = $datetime;
    if(!isset($threads_ec[$thread_id])) {
        $threads_ec[$thread_id] = normalize_error_code($errcode);
    }
    else {
        $err = normalize_error_code($errcode);
        if($threads_ec[$thread_id] > $err) {
            $threads_ec[$thread_id] = $err;
        }
    }
}

// init next and previous threads ids
$previous_thread = null;
$next_thread = null;


// review all lines preceeding that thread
for($i = 0; $i < $len-1; ++$i) {
    $entry = $lines[$i];
    // skip stack descriptors
    if(substr($entry, 0, 1) == '#' || strlen($entry) < 20) continue;    


    // fetch the thread_id
    list($tid, $datetime, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);    
    list($datetime, $microtime) = explode('+', $datetime);    
    list($month, $day, $year, $hour, $minute, $second) = sscanf($datetime, "%d-%d-%d %d:%d:%d");    
    $timestamp = mktime($hour, $minute , $second, $month, $day, $year);

    $threads_dt[$tid] = $datetime;
    if(!isset($threads_ec[$tid])) {
        $threads_ec[$tid] = normalize_error_code($errcode);
    }
    else {
        $err = normalize_error_code($errcode);
        if($threads_ec[$tid] > $err) {
            $threads_ec[$tid] = $err;
        }
    }  
    if($tid == $thread_id) {
        $history[$timestamp.'+'.$microtime] = $entry;        
        break;        
    }
    if($previous_thread != $tid) {
        $history[$timestamp.'+'.$microtime] = $entry;
    }
    // remebrer previous thread id
    $previous_thread = $tid;
}

// find next thread id
$prev = $previous_thread;
for($j = $i+1;$j < $len-1; ++$j){
    $entry = $lines[$j];
    if(strlen($entry) == 0) break;
    if(substr($entry, 0, 1) == '#' || strlen($entry) < 20) continue;
    list($tid, $datetime, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
    if(!isset($threads_ec[$tid])) {
        $threads_ec[$tid] = normalize_error_code($errcode);
    }
    else {
        $err = normalize_error_code($errcode);
        if($threads_ec[$tid] > $err) {
            $threads_ec[$tid] = $err;
        }
    }
    
    if($tid == $thread_id) continue;
    
    list($datetime, $microtime) = explode('+', $datetime);    
    list($month, $day, $year, $hour, $minute, $second) = sscanf($datetime, "%d-%d-%d %d:%d:%d");    
    $timestamp = mktime($hour, $minute , $second, $month, $day, $year);
    $threads_dt[$tid] = $datetime;    
    
    if(!$next_thread && $tid != $thread_id) {
        $next_thread = $tid;
    }
    if($prev != $tid) {
        $history[$timestamp.'+'.$microtime] = $entry;    
    }
    $prev = $tid;
}


$current_thread = get_header($thread_id, $thread_id, $previous_thread, $next_thread);

// check for errors from error.log (check if last line is newer than eq_error.log's last line) 
if(file_exists(PHP_LOG_FILE)) {
    $php_log = file_get_contents(PHP_LOG_FILE);
    $php_lines = explode(PHP_EOL, $php_log);

    $php_len = count($php_lines);
    for($l = 1; $l <= $php_len; ++$l) {
        $line = $php_lines[$php_len-$l];
        $match = [];

        if(preg_match("/\[([^\s]*) ([^\s]*) ([^\s]*)\] ([^\s]*) (.*): ((.*):)? (.*) in ([^\s]*) on line ([0-9]+)/", $line, $match)) {
            $timestamp = strtotime($match[1].' '.$match[2]);
            $datetime = date("m-d-Y H:i:s", $timestamp);
            $microtime = '0.00000000';
            list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = [0, $timestamp, $match[5], '', $match[7], $match[8], $match[6]];
            
            $tid = 'PHP'.substr(md5('0;0 '.$timestamp.';'.$file), 0, 6);
            $entry = "$tid;$datetime+$microtime;$errcode;$origin;$file;$line;$msg";
            $history[$timestamp.'+'.$microtime] = $entry;
            $threads_dt[$tid] = $datetime;
            if(!isset($threads_ec[$tid])) {
                $threads_ec[$tid] = normalize_error_code($errcode);
            }
            else {
                $err = normalize_error_code($errcode);
                if($threads_ec[$tid] > $err) {
                    $threads_ec[$tid] = $err;
                }
            }
            if(substr($thread_id, 0, 3) == 'PHP' && $tid == $thread_id) {
                $current_thread .= get_line($entry);
            }            
        }
    }
}


if(substr($thread_id, 0, 3) != 'PHP') {
    // get lines that belong to current thread
    while(true) {
        $entry = $lines[$i];
        if(strlen($entry) == 0) break;
        if(substr($entry, 0, 1) != '#') {
            $values = explode(';', $entry);            
            $tid = $values[0];
            
            if($tid != $thread_id) break;    
            $current_thread .= get_line($entry);
        }
        else {
            $j = 0;
            $stack = [];
            while(substr($entry, 0, 1) == '#') {
                $stack[] = $entry;
                ++$i;
                if($i >= $len-1) break;
                $entry = $lines[$i];
            }
            $current_thread .= get_stack($stack);
        }
        ++$i;
        if($i >= $len-1) break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css" />
<style>
html,body {
    padding:0;
    margin:0;
    height:100%;
}

div.active {
    background-color: lightblue;
}
</style>
</head>
<body>
<?php
if(isset($_REQUEST['list'])) {

    // order history by timestamp
    ksort($history);
    foreach($history as $timestamp => $entry) {
        list($tid, $datetime, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
        echo get_header($tid, $thread_id);
    }
    ?>
    <script>window.scrollTo(0,document.body.scrollHeight);</script>
    <?php
}
else if( isset($_REQUEST['details']) || isset($_REQUEST['thread_id']) ) {

    echo $current_thread;
    
}
else {
?>
<style>    
html, body {
    overflow: hidden;
}
</style>
<iframe name="list" width="100%" height="30%" src="/console.php?list=true" frameborder="0" allowfullscreen></iframe>

<iframe name="details" width="100%" height="70%" src="/console.php?details=true" frameborder="0" allowfullscreen style="padding-bottom: 5px;"></iframe>
<?php 
}
?>
</body>
</html>