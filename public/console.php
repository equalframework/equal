<?php

define('LOG_FILE_NAME', 'eq_error.log');
$data = '';

// get log file, using variation from URL, if any
$log_file = LOG_FILE_NAME.( (isset($_GET['f']) && strlen($_GET['f']))?('.'.$_GET['f']):'');

if(file_exists('../log/'.$log_file)) {
    // read raw data from log file
    $data = file_get_contents('../log/'.$log_file);
}

// retrieve logs history (variations on filename)
$log_variations = [];
foreach(glob('../log/'.LOG_FILE_NAME.'.*') as $file) {
    $log_variations[] = pathinfo($file, PATHINFO_EXTENSION);
}

// get query from URL, if any
$query = (isset($_GET['q']))?$_GET['q']:'';

// adapt params
if(isset($_GET['level']) && $_GET['level'] == '') {
    unset($_GET['level']);
}
if(isset($_GET['mode']) && $_GET['mode'] == '') {
    unset($_GET['mode']);
}
if(isset($_GET['date']) && $_GET['date'] == '') {
    unset($_GET['date']);
}


// 1) filtering : discard lines that do not match the query

$lines = explode(PHP_EOL, $data);
$data = '';
foreach($lines as $line) {
    if(strlen($line) <= 0) {
        continue;
    }
    if(strlen($query) > 0 && stripos($line, $query) === false) {
        continue;
    }
    $data .= $line.',';
}

// 2) extract lines to be rendered

// #memo - log file contains JSON objects separated with new line chars
// convert notation to a valid JSON array
$json = '['.substr($data, 0, -1).']';
// convert JSON to a PHP associative array
$lines = json_decode($json, true);

if($lines === null) {
    die('Invalid JSON in log file.');
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css" />
<style>
html, body {
    padding:0;
    margin:0;
    height:100%;
}
body {
    padding-top:  120px;
}

div.snack {
    width: 250px;
    border: solid 1px grey;
    background: black;
    height: 40px;
    line-height: 40px;
    padding: 0 10px;
    position: absolute;
    z-index: 1;
    border-radius: 3px;
    bottom: -20px;
    opacity: 0;
    left: 20px;
    color: #ccc;
    transition: all 0.5s;
}

div.snack.show {
    bottom: 20px;
    opacity: 1;
}

div.thread {
    position: relative;
    margin-left: 10px;
    font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
}

div.thread div.thread-title {
    margin-left: 20px;
}

div.thread div.thread-title div.thread-hash {
    display: inline-block;
    width: 100px;
}

div.thread i.icon {
    display: inline-block;
    text-align: center;
    width: 20px;
}

div.thread div.thread_line {
    position: relative;
    margin-left: 30px;
}

div.thread div.thread_line div.line-title {
    margin-left: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

div.thread i.chevron {
    position: absolute;
    display: block;
    top: 2px;
    width: 15px;
    text-align: center;
    cursor: pointer;
}

input.selector {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    width: 100%;
    height: 100%;
    opacity: 0
}

div.thread_line div.trace_line {
    margin-left: 20px;
}

div.thread_line div.trace_line i.icon-copy {
    position: absolute;
    right: 21px;
    top: 25px;
    z-index: 2;
    cursor: pointer;
    height: 30px;
    width: 50px;
    background: #f5f5f5;
    line-height: 30px;
    text-align: right;
    padding-right: 10px;
}

input.selector + div > div.trace_line,
input.selector + div > div.thread_line
{
    display: none;
}

input.selector:checked + div > div.trace_line,
input.selector:checked + div > div.thread_line
{
    display: block;
}

input.selector:checked + div > i.fa {
    transform: rotate(90deg);
}

pre {
    margin-right: 20px;
    overflow: hidden !important;
}

</style>
<script>
function copy(node) {
    document.querySelector(".snack").classList.add("show");
    var copyText = document.querySelector("#clipboard");
    copyText.value = node.nextSibling.textContent;
    copyText.select();
    document.execCommand("copy");
    setTimeout(function() {
        document.querySelector(".snack").classList.remove("show");
    }, 2000);
}
</script>
</head>
<body>
<input style="display: block; position: absolute; top: -100px;" id="clipboard" type="text">
<div class="snack">Copied to clipboard</div>
<div id="header" style="position: fixed; top: 0; height: 100px; width: 100%; background: white; z-index: 4;">
    <form method="GET" style="padding: 20px;background: #f1f1f1;margin: 5px;border: solid 1px grey;border-radius: 5px;">
        <div style="display: flex; align-items: flex-end;">
            <div style="display: flex; flex-direction: column;">
                <label>Level:</label>
                <select style="height: 33px; margin-right: 25px;" name="level">
                    <option value="">All</option><option value="DEBUG" '.((isset($_GET['level']) && $_GET['level'] == 'DEBUG')?'selected':'').'>DEBUG</option>
                    <option value="INFO" '.((isset($_GET['level']) && $_GET['level'] == 'INFO')?'selected':'').'>INFO</option>
                    <option value="WARNING" '.((isset($_GET['level']) && $_GET['level'] == 'WARNING')?'selected':'').'>WARNING</option>
                    <option value="ERROR" '.((isset($_GET['level']) && $_GET['level'] == 'ERROR')?'selected':'').'>ERROR</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column;">
                <label>Layer:</label>
                <select style="height: 33px; margin-right: 25px;" name="mode">
                    <option value="">All</option>
                    <option value="PHP" '.((isset($_GET['mode']) && $_GET['mode'] == 'PHP')?'selected':'').'>PHP</option>
                    <option value="SQL" '.((isset($_GET['mode']) && $_GET['mode'] == 'SQL')?'selected':'').'>SQL</option>
                    <option value="ORM" '.((isset($_GET['mode']) && $_GET['mode'] == 'ORM')?'selected':'').'>ORM</option>
                    <option value="API" '.((isset($_GET['mode']) && $_GET['mode'] == 'API')?'selected':'').'>API</option>
                    <option value="APP" '.((isset($_GET['mode']) && $_GET['mode'] == 'APP')?'selected':'').'>APP</option></select>
            </div>
            <div style="display: flex; flex-direction: column;">
                <label>Keyword:</label>
                <input style="height: 33px; margin-right: 25px;" name="q" type="text" value="'.(isset($_GET['q'])?$_GET['q']:'').'">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label>Date:</label>
                <input style="height: 33px; margin-right: 25px;" name="date" type="date" value="'.(isset($_GET['date'])?$_GET['date']:'').'">
            </div>
            <div style="display: flex; flex-direction: column;">
                <button type="submit" class="btn btn-info">Filter</button>
            </div>
            <div style="margin-left: auto;">
                <label>File:</label>
                <select style="height: 33px; margin-right: 25px;" name="f" onchange="this.form.submit()">
                    <option value="">'.LOG_FILE_NAME.'</option>'.
                    implode(PHP_EOL, array_map(function($a) {return '<option value="'.$a.'" '.((isset($_GET['f']) && $_GET['f'] == $a)?'selected':'').'>'.$a.'</option>';}, $log_variations)).'
                </select>
            </div>
        </div>
    </form>
</div>
';


// pass-1: group logs by thread_id
$map_threads = [];
foreach($lines as $line) {
    if(!isset($map_threads[$line['thread_id']])) {
        $map_threads[$line['thread_id']] = [];
    }
    $map_threads[$line['thread_id']][] = $line;
}

$map_codes = [
    'DEBUG'     => E_USER_DEPRECATED,
    'INFO'      => E_USER_NOTICE,
    'WARNING'   => E_USER_WARNING,
    'ERROR'     => E_USER_ERROR,
    'FATAL'     => E_ERROR
];

// pass-2: show list by thread
foreach($map_threads as $thread => $lines) {

    $datetime = $lines[0]['time'];
    $code = PHP_INT_MAX;
    // find lowest error code for applying styles
    foreach($lines as $index => $line) {
        // filter & discard
        if(isset($_GET['level']) && $line['level'] != $_GET['level']) {
            unset($lines[$index]);
            continue;
        }
        if(isset($_GET['mode']) && $line['mode'] != $_GET['mode']) {
            unset($lines[$index]);
            continue;
        }
        if(isset($_GET['date']) && strpos($line['time'], $_GET['date']) !== 0) {
            unset($lines[$index]);
            continue;
        }
        $lcode = $map_codes[$line['level']];
        if($lcode < $code) {
            $code = $lcode;
        }
    }
    if(!count($lines)) {
        continue;
    }
    list($type, $icon, $class) = get_level_class($code);

    // append a thread line
    $html .= "
    <div class=\"thread\">
        <div class=\"thread-title\">
            <div class=\"$class\" title=\"$type\">
                <div class=\"thread-hash\" ><i class=\"icon fa $icon\"></i> $thread </div>
                $datetime
            </div>
        </div>
        <input type=\"checkbox\" class=\"selector\">
        <div>
            <i class=\"chevron fa fa-chevron-right\"></i>
        ";
    foreach($lines as $line) {
        // bypass invalid lines
        if(!is_array($line) || !isset($line['level']) || !isset($line['class']) || !isset($line['function']) || !isset($line['message']) || !isset($line['stack'])) {
            continue;
        }
        list($type, $icon, $class) = get_level_class($line['level']);

        $origin = ((strlen($line['class']))?$line['class'].'::':'').$line['function'];

        $in = (strlen($origin))?"<b>in</b> <code class=\"$class\">$origin</code>":'';
        $msg = $line['message'];

        $n = count($line['stack']);
        $m = strlen($msg);

        $msg_excerpt = substr($msg, 0, 64).(($m > 64)?"&hellip;":'');

        $html .= "
            <div class=\"thread_line\">
                <div class=\"line-title\"><a class=\"$class\" title=\"$type\"><i class=\"icon fa $icon\"></i> {$line['time']} {$line['mtime']} {$line['mode']}</a> <b>@</b> [<code class=\"$class\">{$line['file']}:{$line['line']}</code>] $in: ".$msg_excerpt."</div>
                <input class=\"selector\" type=\"checkbox\">
                <div>
            ";

        if($n || $m > 64) {
            $html .= "<i class=\"chevron fa fa-chevron-right\"></i>";
            if($m > 64) {
                $html .= "<div class=\"trace_line\"><i class=\"fa fa-clipboard icon-copy\" onclick=\"copy(this)\"></i><pre>".$msg."</pre></div>";
            }
            for($i = 0; $i < $n; ++$i) {
                $trace = array_merge([
                        'function'  => '',
                        'line'      => 0,
                        'file'      => '',
                        'class'     => '',
                        'object'    => null,
                        'args'      => [],
                    ], $line['stack'][$i]);
                $html .= "<div class=\"trace_line\">".($n-$i).". {$trace['file']} line {$trace['line']}; ({$trace['function']});</div>";
            }
        }

        $html .= "
            </div>
        </div>".PHP_EOL;
    }
    $html .= "</div>
    </div>".PHP_EOL;
}


$html .= '
</body>
</html>';


echo $html;


function get_level_class($errcode) {
    $type = $errcode;
    $icon = 'fa-info';
    $class= '';
    switch($errcode) {
        case 'DEBUG':
        case E_USER_DEPRECATED:
            $type = 'DEBUG';
            $icon = 'fa-bug';
            $class = 'text-success';
            break;
        case 'INFO':
        case 'NOTICE':
        case E_USER_NOTICE:
            $type = 'INFO';
            $icon = 'fa-info';
            $class = 'text-info';
            break;
        case 'WARNING':
        case E_USER_WARNING:
            $type = 'WARNING';
            $icon = 'fa-warning';
            $class = 'text-warning';
            break;
        case 'ERROR':
        case E_USER_ERROR:
            $type = 'ERROR';
            $icon = 'fa-times-circle';
            $class = 'text-danger';
            break;
        case 'FATAL':
        case E_ERROR:
            $type = 'FATAL';
        case 'Fatal error':
        case 'Parse error':
            $icon = 'fa-ban';
            $class = 'text-danger';
            break;
    }
    return [$type, $icon, $class];
}