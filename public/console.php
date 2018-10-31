<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
/**
* This file is supposed to remain standalone (free of any dependency other than the qn_error.log file)
* For security reasons its access should be restricted to development environment only.
*/
define('QN_LOG_FILE', '../log/qn_error.log');
define('PHP_LOG_FILE', '../log/error.log');
 
date_default_timezone_set('Europe/Brussels');
     
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
    list($thread_id, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
    if(strpos($timestamp, '.') > 0) {
        $time = str_pad(explode('.', $timestamp)[1], 4, '0').'ms';
    }
    else {
        $time = date('H:i:s', $timestamp);
    }

    $type = $errcode;
    $icon = 'fa-info';
    $class= '';
    switch($errcode) {
        case 'Notice':
        case E_USER_NOTICE:
            $type = 'Debug';
            $icon = 'fa-bug';
            $class = 'text-success';
            break;
        case E_USER_WARNING:
            $type = 'Warning';
            $icon = 'fa-warning';
            $class = 'text-warning';
            break;
        case E_USER_ERROR:
            $type = 'Error';
            $icon = 'fa-times-circle';
            $class = 'text-danger';
            break;        
        case E_ERROR:
            $type = 'Fatal error';
        case 'Fatal error':
        case 'Parse error':
            $icon = 'fa-ban';
            $class = 'text-danger';
            break;
    }
    $in = (strlen($origin))?"<b>in</b> <code class=\"$class\">$origin</code>":'';
    return "<div style=\"margin-left: 10px;\"><a class=\"$class\" title=\"$type\" ><i class=\"fa $icon\" aria-hidden=\"true\"></i> $time $type</a> <b>@</b> [<code class=\"$class\">{$file}:{$line}</code>] $in: $msg</div>".PHP_EOL;
}

function get_header($thread_id, $selected_thread_id, $previous_thread=null, $next_thread=null) {
    if(substr($thread_id, 0, 3) == 'PHP') {
        $info = base64_decode(strtr(substr($thread_id, 3), '-_', '+/'));        
    }
    else {
        $info = base64_decode(strtr($thread_id, '-_', '+/'));
    }
    list($thread_pid, $thread_time, $thread_script) = explode(';', $info);
    list($up, $down, $color) = ['', '', ''];
    if($previous_thread) {
        $up = "<a href=\"?thread_id=$previous_thread\"><i class=\"fa fa-caret-up\"></i></a>";
        $down = "<a href=\"?thread_id=$next_thread\"><i class=\"fa fa-caret-down\"></i></a>";
    }
    else {
        if($selected_thread_id == $thread_id) {
            $color = 'background-color: lightblue;';        
        }        
    }
    return "<div style=\"margin-left: 10px; $color\"><a title=\"PID $thread_pid\" href=\"?thread_id=$thread_id\">".date('Y-m-d H:i:s', explode(' ', $thread_time)[1])." ".$thread_script."</a>&nbsp;{$up}&nbsp;{$down}</div>".PHP_EOL;    
}

$history = [];

// todo : && if(!file_exists(PHP_LOG_FILE))
if(!file_exists(QN_LOG_FILE)) die('no log found');
// read raw content
$log = file_get_contents(QN_LOG_FILE);
// extract content to an array or rows
$lines = explode(PHP_EOL, $log);
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
    while(substr($entry, 0, 1) == '#' || strlen($entry) < 20) {
        ++$k;
        if(($len-$k) <= 0) die();
        $entry = $lines[$len-$k-1];
    }
    // fetch the thread_id
// todo : use a funciton to fetch an entry, and validate it based on values count (otherwise, ignore)    
    list($thread_id, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
    // syntax:  $this->thread_id.';'.time().';'.$code.';'.$origin.';'.$trace['file'].';'.$trace['line'].';'.$msg.PHP_EOL;
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
    list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);    
    if($tid == $thread_id) {
        $history[$timestamp] = $entry;        
        break;        
    }
    if($previous_thread != $tid) {
        $history[$timestamp] = $entry;        
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
    list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);    
    if($tid == $thread_id) continue;
    if(!$next_thread && $tid != $thread_id) {
        $next_thread = $tid;
    }
    if($prev != $tid) {
        $history[$timestamp] = $entry;    
    }
    $prev = $tid;
}


$current_thread = get_header($thread_id, $thread_id, $previous_thread, $next_thread);

// check for errors from error.log (check if last line is newer than qn_error.log's last line) 
if(file_exists(PHP_LOG_FILE)) {
    $php_log = file_get_contents(PHP_LOG_FILE);
    $php_lines = explode(PHP_EOL, $php_log);

    $php_len = count($php_lines);
    for($l = 1; $l <= $php_len; ++$l) {
        $line = $php_lines[$php_len-$l];
        $match = [];

        //[31-Jan-2018 21:21:36 Europe/Brussels] PHP Fatal error:  Class 'qinoa\services\Service' not found in C:\DEV\wamp64\www\resiway\vendor\qinoa\services\Container.class.php on line 6        
        if(preg_match("/\[([^\s]*) ([^\s]*) ([^\s]*)\] ([^\s]*) (.*): (.*) in ([^\s]*) on line ([0-9]+)/", $line, $match)) {
            $timestamp = strtotime($match[1].' '.$match[2]);
            list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = [0, $timestamp, $match[5], '', $match[7], $match[8], $match[6]];
            $tid = 'PHP'.strtr(base64_encode('0;0 '.$timestamp.';'.$file), '+/', '-_');
            $entry = "$tid;$timestamp.0000;$errcode;$origin;$file;$line;$msg";
            $history[$timestamp] = $entry;
            if(substr($thread_id, 0, 3) == 'PHP' && $tid == $thread_id) {
                $current_thread .= get_line($entry);
            }            
        }
    }
}



// todo : handle param thread_id when targeting PHP error.log
if(substr($thread_id, 0, 3) != 'PHP') {
    // get lines that belong to current thread
    while(true) {
        $entry = $lines[$i];
        if(strlen($entry) == 0) break;
        if(substr($entry, 0, 1) != '#') {
            list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);    
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
<link rel="stylesheet" type="text/css" href="packages/resipedia/apps/assets/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="packages/resipedia/apps/assets/css/font-awesome.min.css" />
<style>
html,body {
    padding:0;
    margin:0;
    height:100%;
}
</style>
</head>
<body>
<div style="width: 100%; height: 34%; overflow: scroll;">
<?php
ksort($history);
foreach($history as $timestamp => $entry) {
        list($tid, $timestamp, $errcode, $origin, $file, $line, $msg) = explode(';', $entry);
        echo get_header($tid, $thread_id);
}
?>
</div>
<div style="width: 100%; height: 66%; overflow: scroll;">
<?php echo $current_thread; ?>
</div>
</body>
</html>