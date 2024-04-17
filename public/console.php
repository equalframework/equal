<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2023
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
error_reporting(0);

// get log file, using variation from URL, if any
$log_file = (isset($_GET['f']) && strlen($_GET['f']))?$_GET['f']:'eq_error.log';

// retrieve logs history (variations on filename)
$log_variations = [];
foreach(glob('../log/*.log') as $file) {
    $log_variations[] = pathinfo($file, PATHINFO_EXTENSION);
}


// no param given : frond-end App provider
if(!count($_GET)) {
    echo '
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
            #start {
                padding-top:  120px;
            }
            .loader-overlay {
                display: none;
                position: relative;
            }

            .loader-overlay .loader-container {
                position: absolute;
                top: calc(40vh - 50px);
                left: calc(50% - 50px);
                z-index: 1;
            }

            .loader-overlay .loader-spinner {
                display: inline-block;
                width: 56px;
                height: 56px;
                border-radius: 50%;
                box-sizing: border-box;
                border: 5px solid #3f51b5;
                clip-path: polygon(50% 50%, 0% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%);
                animation: 1.6s loader_spinner linear infinite;
            }

            @keyframes loader_spinner {
                0% {
                    transform: rotate(0deg);
                    clip-path: polygon(50% 50%, 0% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%);
                }
                20% {
                    clip-path: polygon(50% 50%, 0% 0%, 50% 0%, 100% 0%, 100% 50%, 100% 50%, 100% 50%, 100% 50%, 100% 50%);
                }
                30% {
                    clip-path: polygon(50% 50%, 0% 0%, 50% 0%, 100% 0%, 100% 50%, 100% 100%, 50% 100%, 50% 100%, 50% 100%);
                }
                40% {
                    clip-path: polygon(50% 50%, 0% 0%, 50% 0%, 100% 0%, 100% 50%, 100% 100%, 50% 100%, 0% 100%, 0% 50%);
                }
                50% {
                    clip-path: polygon(50% 50%, 50% 0%, 50% 0%, 100% 0%, 100% 50%, 100% 100%, 50% 100%, 0% 100%, 0% 50%);
                }
                60% {
                    clip-path: polygon(50% 50%, 100% 50%, 100% 50%, 100% 50%, 100% 50%, 100% 100%, 50% 100%, 0% 100%, 0% 50%);
                }
                70% {
                    clip-path: polygon(50% 50%, 50% 100%, 50% 100%, 50% 100%, 50% 100%, 50% 100%, 50% 100%, 0% 100%, 0% 50%);
                }
                80% {
                    clip-path: polygon(50% 50%, 0% 100%, 0% 100%, 0% 100%, 0% 100%, 0% 100%, 0% 100%, 0% 100%, 0% 50%);
                }
                90%{
                    transform: rotate(360deg);
                    clip-path: polygon(50% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%);
                }
                100% {
                    clip-path: polygon(50% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%, 0% 50%);
                }
            }

            div.snack {
                width: 250px;
                border: solid 1px grey;
                background: black;
                height: 40px;
                line-height: 40px;
                padding: 0 10px;
                position: fixed;
                z-index: 1;
                border-radius: 5px;
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
            div.no-result {
                margin-left: 20px;
            }
            div.no-result::before {
                content: \'(no match or empty log)\';
                width: 100%;
                line-height: 30px;
                font-style: italic;
            }
            div.thread {
                position: relative;
                margin-left: 10px;
                font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
            }

            div.thread div.thread-title {
                margin-left: 20px;
                cursor: pointer;
            }

            div.thread.selected div.thread-title {
                background-color: #e1f0f5;
            }

            div.thread div.thread-title div.text {
                color: #4f4f4f;
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

            div.thread div.thread_line div.line-title.match, div.thread div.thread_line div.line-title.match code {
                background-color: yellow !important;
            }

            div.thread div.thread_line div.line-title span.text {
                color: #4f4f4f;
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
                opacity: 0;
                cursor: pointer;
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
                white-space: break-spaces;
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

            function get_level_info(level) {
                let type = level;
                let icon = "fa-info";
                let classname = "";
                switch(level) {
                    case "DEBUG":
                    case 16384: // E_USER_DEPRECATED
                        type = "DEBUG";
                        icon = "fa-bug";
                        classname = "text-success";
                        break;
                    case "INFO":
                    case "NOTICE":
                    case 1024:  // E_USER_NOTICE
                        type = "INFO";
                        icon = "fa-info";
                        classname = "text-info";
                        break;
                    case "WARNING":
                    case 512:   // E_USER_WARNING
                        type = "WARNING";
                        icon = "fa-warning";
                        classname = "text-warning";
                        break;
                    case "ERROR":
                    case 256:   // E_USER_ERROR
                        type = "ERROR";
                        icon = "fa-times-circle";
                        classname = "text-danger";
                        break;
                    case "FATAL":
                    case 1:     // E_ERROR
                        type = "FATAL";
                    case "Fatal error":
                    case "Parse error":
                        icon = "fa-ban";
                        classname = "text-danger";
                        break;
                    case "SYSTEM":
                    case 0:
                        icon = "fa-hashtag";
                        classname = "text";
                        break;
                }
                return {type: type, icon: icon, class: classname};
            }

            async function get_threads(params) {
                let query = new URLSearchParams(params).toString();
                if(typeof params == "undefined") {
                    query = "a=1";
                }
                const response = await fetch("console.php?"+query);
                const data = await response.json();
                return data;
            }

            async function get_lines(thread_id, params) {
                if(typeof params == "undefined") {
                    params = {};
                }
                params.thread_id = thread_id;
                let query = new URLSearchParams(params).toString();
                const response = await fetch("console.php?"+query);
                const data = await response.json();
                return data;
            }

            function createThreadElement(thread, params) {
                const template = document.getElementsByClassName("thread-template")[0].innerHTML;
                let div = document.createElement("div");
                let content = template;
                let info = get_level_info(thread.level);
                content = content.replace("$thread_id", thread.thread_id);
                content = content.replace("$time", thread.time);
                content = content.replace("$type", info.type);
                content = content.replace("$class", info.class);
                content = content.replace("$icon", info.icon);
                div.innerHTML = content;
                div.querySelector("input").addEventListener("click", async function(event) {
                        document.getElementById("loader").style.display = "block";
                        event.target.parentNode.classList.add("selected");
                        let list = document.getElementById("list");
                        // pass-1 - hide threads
                        for(let node of list.getElementsByClassName("thread")) {
                            if(event.target.checked) {
                                if(event.target.parentNode != node) {
                                    node.style.display = "none";
                                }
                            }
                        }
                        // pass-2 - load lines
                        for(let node of list.getElementsByClassName("thread")) {
                            if(event.target.checked) {
                                if(event.target.parentNode == node) {
                                    // if not yet present, load lines
                                    if(!node.classList.contains("loaded")) {
                                        node.classList.add("loaded");
                                        const lines = await get_lines(thread.thread_id, params);
                                        let list = node.getElementsByClassName("thread-lines")[0];
                                        for(const line of lines) {
                                            let element = createLineElement(line);
                                            list.append(element);
                                        }
                                    }
                                }
                            }
                            else {
                                node.style.display = "block";
                            }
                            node.classList.remove("selected");
                        }
                        event.target.parentNode.classList.add("selected");
                        document.getElementById("loader").style.display = "none";
                    });
                return div.firstElementChild;
            }

            function createLineElement(line) {
                const template = document.getElementsByClassName("line-template")[0].innerHTML;
                let div = document.createElement("div");
                let content = template;
                let info = get_level_info(line.level);
                let origin = ((line.class.length)?line.class+"::":"")+line.function;
                let inside = "<b>in</b> <code class=\""+info.class+"\">"+origin+"</code>";
                content = content.replace("$mode", line.mode);
                content = content.replace("$time", line.time);
                content = content.replace("$mtime", line.mtime);
                content = content.replace("$file", line.file);
                content = content.replace("$line", line.line);
                content = content.replace("$in", inside);
                content = content.replaceAll("$type", info.type);
                content = content.replaceAll("$class", info.class);
                content = content.replaceAll("$icon", info.icon);
                content = content.replaceAll("$msg", line.message);
                div.innerHTML = content;

                if(line.match) {
                    div.getElementsByClassName("line-title")[0].classList.add("match");
                }

                let list = div.getElementsByClassName("line-traces")[0];
                let count = line.stack.length, i = 0;
	            for(let trace of line.stack) {
                    let values = {
                            ...{
                                function: "",
                                line:     0,
                                file:     "",
                                class:    "",
                                object:   null,
                                args:     [],
                            },
                            ...trace
                        };
                    let element = createTraceElement(values, count-i);
                    list.append(element);
                    ++i;
                }
                return div.firstElementChild;
            }

            function createTraceElement(trace, i) {
                const template = document.getElementsByClassName("trace-template")[0].innerHTML;
                let div = document.createElement("div");
                let content = template;
                content = content.replace("$function", trace.function);
                content = content.replace("$line", trace.line);
                content = content.replace("$file", trace.file);
                content = content.replace("$class", trace.class);
                content = content.replace("$i", i);
                div.innerHTML = content;
                return div.firstElementChild;
            }

            async function feed(params) {
                list.style.display = "none";
                list.innerHTML = "";
                document.getElementById("loader").style.display = "block";
                const threads = await get_threads(params);
                let list = document.getElementById("list");
                for(const thread of threads) {
                    let element = createThreadElement(thread, params);
                    list.append(element);
                }
                if(!threads.length) {
                    list.innerHTML = "<div class=\"no-result\"></div>";
                }
                document.getElementById("loader").style.display = "none";
                list.style.display = "block";
            }

            document.addEventListener("DOMContentLoaded", async function() {
                await feed();
                document.getElementById("searchForm").addEventListener("submit", function (e) {
                        e.preventDefault();
                        const form = e.srcElement;
                        let params = {
                            q: form.elements.q.value,
                            mode: form.elements.mode.value,
                            level: form.elements.level.value,
                            date: form.elements.date.value
                        }
                        feed(params);
                    });
            });

        </script>
        </head>
        <body>
        <input style="display: block; position: absolute; top: -100px;" id="clipboard" type="text">
        <div class="snack">Copied to clipboard</div>

        <div class="thread-template" style="display:none">
            <div class="thread">
                <div class="thread-title">
                    <div class="$class" title="$type">
                        <div class="thread-hash"><i class="icon fa $icon"></i> $thread_id </div>
                        $time
                    </div>
                </div>
                <input type="checkbox" class="selector">
                <div class="thread-lines">
                    <i class="chevron fa fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <div class="line-template" style="display: none">
            <div class="thread_line">
                <div class="line-title"><span class="$class" title="$type"><i class="icon fa $icon"></i> $time $mtime $mode</span> <b>@</b> [<code class="$class">$file:$line</code>] $in: $msg</div>
                <input class="selector" type="checkbox">
                <div class="line-traces">
                    <i class="chevron fa fa-chevron-right"></i>
                    <div class="trace_line"><i class="fa fa-clipboard icon-copy" onclick="copy(this)"></i><pre>$msg</pre></div>
                </div>
            </div>
        </div>

        <div class="trace-template" style="display: none">
            <div class="trace_line">$i. $file line $line ($function)</div>
        </div>


        <div id="header" style="position: fixed; top: 0; height: 100px; width: 100%; background: white; z-index: 4;">
            <form id="searchForm" style="padding: 20px; background: #f5f5f5; margin: 5px; border: solid 1px #dfdfdf; border-radius: 5px;">
                <div style="display: flex; align-items: flex-end;">
                    <div style="display: flex; flex-direction: column;">
                        <label>Level:</label>
                        <select style="height: 33px; margin-right: 25px;" name="level">
                            <option value="">All</option>
                            <option value="SYSTEM">SYSTEM</option>
                            <option value="DEBUG">DEBUG</option>
                            <option value="INFO">INFO</option>
                            <option value="WARNING">WARNING</option>
                            <option value="ERROR">ERROR</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label>Layer:</label>
                        <select style="height: 33px; margin-right: 25px;" name="mode">
                            <option value="">All</option>
                            <option value="PHP">PHP</option>
                            <option value="SQL">SQL</option>
                            <option value="ORM">ORM</option>
                            <option value="API">API</option>
                            <option value="APP">APP</option>
                            <option value="AAA">AAA</option>
                            <option value="NET">NET</option></select>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label>Keyword:</label>
                        <input style="height: 33px; margin-right: 25px;" name="q" type="text" value="">
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label>Date:</label>
                        <input style="height: 33px; width: 150px;" name="date" type="date" value="">
                    </div>
                    <div style="display: flex; flex-direction: column; height: 30px; margin-left: 10px; margin-right: 25px;">
                        <div style="display: flex;">
                            <input type="checkbox" name="empty-file"> <span style="margin-left: 5px">Empty file</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <button type="submit" class="btn btn-info">Filter</button>
                    </div>
                    <div style="display: flex; flex-direction: column; margin-left:10px">
                        <a href="#end" class="btn btn-info">Go to bottom</a>
                    </div>
                    <div style="display: flex; flex-direction: column; margin-left:10px">
                        <a href="#start" class="btn btn-info">Go to top</a>
                    </div>
                    <div style="margin-left: auto;">
                        <label>File:</label>
                        <select style="height: 33px; margin-right: 25px;" name="f" onchange="this.form.submit()">
                            <option value="">'.$log_file.'</option>'.
                            implode(PHP_EOL, array_map(function($a) {return '<option value="'.$a.'" '.((isset($_GET['f']) && $_GET['f'] == $a)?'selected':'').'>'.$a.'</option>';}, $log_variations)).'
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div id="loader" class="loader-overlay"><div class="loader-container"><div class="loader-spinner"></div></div></div>
        <div id="start"></div>
        <div id="list"></div>
        <div id="end"></div>
        </body>
        </html>
        ';
}
// params given : back-end data provider
else {
    $result = [];

    $map_codes = [
        'SYSTEM'    => 0,
        'DEBUG'     => E_USER_DEPRECATED,
        'INFO'      => E_USER_NOTICE,
        'WARNING'   => E_USER_WARNING,
        'ERROR'     => E_USER_ERROR,
        'FATAL'     => E_ERROR
    ];

    if(file_exists('../log/'.$log_file)) {

        if(isset($_GET['empty-file']) && $_GET['empty-file'] === 'on') {
            $f = fopen('../log/'.$log_file,"r+");
            ftruncate($f, 0);
            fclose($f);
            header("Location: console.php");
            die();
        }

        // get query from URL, if any
        $query = $_GET['q'] ?? '';

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

        // read raw data from log file

        if($f = fopen('../log/'.$log_file, 'r')) {

            // lines request (return lines matching filters within a given thread_id)
            if(isset($_GET['thread_id'])) {
                while (($data = fgets($f)) !== false) {
                    if(($line = json_decode($data,true)) === null) {
                        continue;
                    }
                    if($line['thread_id'] != $_GET['thread_id']) {
                        continue;
                    }
                    $match = true;
                    $line['match'] = false;
                    if( $match && (isset($_GET['level']) && $line['level'] != $_GET['level']) ) {
                        $match = false;
                    }
                    if( $match && (isset($_GET['mode']) && $line['mode'] != $_GET['mode']) ) {
                        $match = false;
                    }
                    if( $match && (isset($_GET['date']) && strpos($line['time'], $_GET['date']) !== 0) ) {
                        $match = false;
                    }
                    if( $match && (strlen($query) > 0 && stripos($line['message'], $query) === false) ) {
                        $match = false;
                    }
                    if($match && strlen($query)) {
                        $line['match'] = true;
                    }
                    $result[] = $line;

                }

            }
            // threads request (return threads summary: lines count, max level, first time)
            else {
                $map_threads = [];
                // step-1 : load all threads_ids
                while (($data = fgets($f)) !== false) {
                    if(($line = json_decode($data,true)) === null) {
                        continue;
                    }
                    if(!isset($map_threads[$line['thread_id']])) {
                        $map_threads[$line['thread_id']] = [
                            'thread_id' => $line['thread_id'],
                            'lines'     => 0,
                            'level'     => $map_threads[$line['thread_id']]['level'],
                            // threads will be sorted on timestamp using a map : we must avoid collisions
                            'time'      => $line['time'].'.'.$line['mtime']
                        ];
                    }
                    elseif($map_codes[$line['level']] && (!$map_codes[$map_threads[$line['thread_id']]['level']] || $map_codes[$line['level']] < $map_codes[$map_threads[$line['thread_id']]['level']])) {
                        $map_threads[$line['thread_id']]['level'] = $line['level'];
                    }
                    $match = true;

                    if($map_threads[$line['thread_id']]['lines'] < 1) {
                        if( $match && (isset($_GET['level']) && $line['level'] != $_GET['level']) ) {
                            $match = false;
                        }
                        if( $match && (isset($_GET['mode']) && $line['mode'] != $_GET['mode']) ) {
                            $match = false;
                        }
                        if( $match && (isset($_GET['date']) && strpos($line['time'], $_GET['date']) !== 0) ) {
                            $match = false;
                        }
                        if( $match && strlen($query) && stripos($line['message'], $query) === false) {
                            $match = false;
                        }
                    }
                    if($match) {
                        ++$map_threads[$line['thread_id']]['lines'];
                    }
                }
                // step-2 : keep only threads with matching lines
                foreach($map_threads as $thread_id => $thread) {
                    if($thread['lines'] <= 0) {
                        continue;
                    }
                    // order threads by time (ascending)
                    $result[$thread['time']] = $thread;
                }
                $result = array_values($result);
            }
            fclose($f);
        }

    }

    echo json_encode($result, JSON_PRETTY_PRINT);
}
