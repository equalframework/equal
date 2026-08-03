<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Author: The eQual Framework Community
    Original Author(s): Cedric Francoys
    License:  GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
error_reporting(0);
define('MAX_LOG_READ_BYTES', 100 * 1000 * 1000);
define('DEFAULT_THREAD_LIMIT', 100);
define('MAX_THREAD_LIMIT', 500);
define('DEFAULT_LINE_LIMIT', 250);
define('MAX_LINE_LIMIT', 1000);

// get log file, using variation from URL, if any
$log_file = (isset($_GET['f']) && strlen($_GET['f'])) ? basename($_GET['f']) : 'equal.log';
if(!preg_match('/^equal\.log(?:\.\d+)?$/', $log_file)) {
    $log_file = 'equal.log';
}

// retrieve logs history (variations on filename)
$log_variations = [];
foreach(glob('../log/equal.log*') ?: [] as $file) {
    $file = basename($file);
    if(preg_match('/^equal\.log(?:\.\d+)?$/', $file)) {
        $log_variations[] = $file;
    }
}
$log_variations = array_values(array_unique($log_variations));
usort($log_variations, function($a, $b) {
    $rank = function($file) {
        return ($file === 'equal.log') ? 0 : (int) substr($file, strlen('equal.log.'));
    };
    return $rank($a) <=> $rank($b);
});
if(!in_array($log_file, $log_variations, true)) {
    array_unshift($log_variations, $log_file);
}
$log_options = implode(PHP_EOL, array_map(function($file) use($log_file) {
    $escaped_file = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
    return '<option value="'.$escaped_file.'" '.(($log_file === $file)?'selected':'').'>'.$escaped_file.'</option>';
}, $log_variations));


// no param given: frond-end App provider
$is_data_request = isset($_GET['api']) || isset($_GET['thread_id']) || isset($_GET['empty-file']);
if(!$is_data_request) {
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

            a:focus {
                outline: none !important;
            }

            .equal-logo {
                position: relative;
                width: 40px;
                height: 40px;
                margin-right: 20px;
                cursor: pointer;
                display: block;
            }

            .equal-logo::after {
                position: absolute;
                top: -5px;
                content: \'\';
                width: 40px;
                height: 40px;
                background: url(/assets/img/equal_symbol.png);
                background-size: contain;
                border-radius: 50%;
            }

            .material-input,
            .material-select,
            .material-button,
            .material-icon-button {
                margin-right: 12px;
            }

            .material-input, .material-select {
                position: relative;
                background: white;
                font-family: \'Roboto\', sans-serif;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
            }

            .material-input input,
            .material-select select {
                font-size: 14px;
                padding: 10px 10px 10px 5px;
                display: block;
                width: 100%;
                border: none;
                border-bottom: 1px solid #757575;
                background: transparent;
                outline: none;
                transition: all 0.3s ease;
                appearance: none;
            }

            .material-input input::placeholder,
            .material-select select::placeholder {
                color: transparent;
            }

            .material-input label,
            .material-select label {
                color: #999;
                font-size: 14px;
                font-weight: normal;
                position: absolute;
                pointer-events: none;
                left: 5px;
                top: 14px;
                transition: 0.2s ease all;
            }

            .material-input input:focus ~ label,
            .material-input input:not(:placeholder-shown) ~ label,
            .material-select select:focus ~ label,
            .material-select select:not([value=""]) ~ label {
                top: -20px;
                font-size: 12px;
                color: #5264ae;
            }

            .material-input input:placeholder-shown ~ label,
            .material-select select:placeholder-shown ~ label {
                top: 10px;
                font-size: 14px;
            }

            .material-input .bar,
            .material-select .bar {
            position: relative;
                display: block;
                width: 100%;
            }

            .material-input .bar:before,
            .material-input .bar:after,
            .material-select .bar:before,
            .material-select .bar:after {
                content: \'\';
                height: 2px;
                width: 0;
                bottom: 0px;
                position: absolute;
                background: #5264ae;
                transition: 0.2s ease all;
            }

            .material-input .bar:before,
            .material-select .bar:before {
                left: 50%;
            }

            .material-input .bar:after,
            .material-select .bar:after {
                right: 50%;
            }

            .material-input input:focus ~ .bar:before,
            .material-input input:focus ~ .bar:after,
            .material-select select:focus ~ .bar:before,
            .material-select select:focus ~ .bar:after {
                width: 50%;
            }

            .material-button {
                display: inline-block;
                padding: 10px 20px;
                font-size: 16px;
                color: #fff;
                background-color: #3f51b5;
                border: none;
                border-radius: 25px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .material-button:hover {
                background-color: #4f61c5;
            }

            .material-icon-button {
                display: flex;
                justify-content: center;
                border-radius: 50%;
                width: 34px;
                height: 34px;
                background-color: #f5f5f5;
                align-items: center;
                border: none;
                cursor: pointer;
                padding: 0;
                text-decoration: none !important;
            }

            .material-icon-button i {
                font-size: 14px;
                color: #505050;
                display: block;
                margin: auto;
            }


            .material-icon-button:hover {
                background-color: #d5d5d5;
            }

            .material-select select {
                cursor: pointer;
            }

            .material-select select::-ms-expand {
                display: none;
            }

            .material-select:after {
                content: \'\\25BC\';
                position: absolute;
                right: 10px;
                top: 14px;
                color: #505050;
                pointer-events: none;
                transition: 0.2s ease all;
            }

            .material-select select:focus ~ .bar:before,
            .material-select select:focus ~ .bar:after {
                width: 50%;
            }

            .checkbox-select {
                position: relative;
                width: 130px;
                margin-right: 12px;
                background: white;
                font-family: "Roboto", sans-serif;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
            }

            .checkbox-select-trigger {
                width: 100%;
                min-height: 38px;
                padding: 12px 24px 4px 5px;
                border: none;
                border-bottom: 1px solid #757575;
                background: transparent;
                color: #333;
                cursor: pointer;
                font-size: 14px;
                outline: none;
                overflow: hidden;
                text-align: left;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .checkbox-select-trigger::after {
                content: "\\25BC";
                position: absolute;
                right: 10px;
                top: 14px;
                color: #505050;
                pointer-events: none;
            }

            .checkbox-select > label {
                color: #5264ae;
                font-size: 12px;
                font-weight: normal;
                left: 5px;
                pointer-events: none;
                position: absolute;
                top: -20px;
            }

            .checkbox-select-menu {
                display: none;
                position: absolute;
                left: 0;
                top: 42px;
                min-width: 100%;
                padding: 6px 0;
                background: white;
                border: solid 1px #dfdfdf;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
                z-index: 8;
            }

            .checkbox-select.open .checkbox-select-menu {
                display: block;
            }

            .checkbox-select-menu label {
                display: flex;
                align-items: center;
                gap: 7px;
                margin: 0;
                padding: 5px 10px;
                color: #333;
                cursor: pointer;
                font-size: 13px;
                font-weight: normal;
                white-space: nowrap;
            }

            .checkbox-select-menu label:hover,
            .checkbox-select-option:hover {
                background: #f5f5f5;
            }

            .checkbox-select-option {
                display: block;
                width: 100%;
                margin: 0;
                padding: 5px 10px;
                border: none;
                background: transparent;
                color: #333;
                cursor: pointer;
                font-size: 13px;
                text-align: left;
                white-space: nowrap;
            }

            .checkbox-select-separator {
                height: 1px;
                margin: 6px 0;
                background: #dfdfdf;
            }

            #header {
                position: fixed;
                top: 0;
                height: 140px;
                width: 100%;
                background: white;
                z-index: 4;
            }

            #start {
                padding-top: 140px;
            }

            #searchForm {
                padding: 25px 20px 15px 20px;
                background: #f5f5f5;
                margin: 10px;
                margin-bottom: 5px;
                border: solid 1px #dfdfdf;
                border-radius: 15px;
            }

            .loader-overlay {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 10;
                pointer-events: none;
            }

            .loader-overlay .loader-container {
                position: absolute;
                top: calc(40vh - 50px);
                left: calc(50% - 50px);
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
            div.feedback {
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
                margin: 2px 0 2px 10px;
                font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
            }

            div.thread div.thread-title {
                margin-left: 20px;
                cursor: pointer;
            }

            div.thread.selected div.thread-title,
            div.thread.opened div.thread-title {
                background-color: #e1f0f5;
            }

            div.thread div.thread-title div.text {
                color: #4f4f4f;
            }

            div.thread div.thread-title div.thread-hash {
                display: inline-block;
                width: 100px;
            }

            div.thread div.thread-title small.thread-line-count {
                display: inline-block;
                width: 50px;
                text-align: right;
            }

            div.thread div.thread-title span.thread-uri {
                display: inline-block;
                margin-left: 20px;
                max-width: 400px;
                overflow: hidden;
                text-overflow: ellipsis;
                vertical-align: bottom;
                white-space: nowrap;
            }

            div.thread div.thread-title span.thread-ip {
                display: inline-block;
                margin-left: 20px;
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
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

            div.thread div.thread_line div.line-title code {
                color: #4f4f4f;
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
                margin: 0 !important;
            }

            input.selector[data-action="toggle-thread"] {
                height: 22px;
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

            input.selector + div > button.load-lines {
                display: none;
            }

            input.selector:checked + div > button.load-lines {
                display: inline-block;
            }

            button.load-lines {
                position: relative;
                z-index: 2;
            }

            input.selector:checked + div > i.fa {
                transform: rotate(90deg);
            }

            pre {
                margin-right: 20px;
                overflow: hidden !important;
                white-space: break-spaces;
            }

            .quick-filters {
                align-items: center;
                display: flex;
                justify-content: space-between;
                width: 100%;
                padding: 0px 15px;
            }

            .quick-filter-actions {
                display: flex;
            }

            .quick-filter-actions button {
                margin-right: 5px;
            }

            .quick-view-actions {
                display: flex;
                gap: 8px;
            }

            .quick-filtered-out {
                display: none !important;
            }

            button.btn {
                height: 18px;
                border: none !important;
                border-radius: 0 !important;
                outline: 0 !important;
                padding: 2px 10px;
                font-size: 11px;
                opacity: 0.5;
            }

            button.btn-info:focus, button.btn-info.focus {
                background-color: #5982bf;
            }

            button.btn-system,
            button.btn-system:hover,
            button.btn-system:focus {
                background-color: #000;
                color: #fff;
            }

            button.btn.applied {
                opacity: 1;
            }

            button.load-more {
                margin: 10px 20px;
                padding: 6px 14px;
                font-size: 13px;
            }
        </style>
        <script>
            const THREAD_PAGE_SIZE = 200;
            const LINE_PAGE_SIZE = 250;
            const QUICK_FILTER_LEVELS = ["SYSTEM", "DEBUG", "INFO", "WARNING", "ERROR"];

            const state = {
                params: {},
                quickLevels: [...QUICK_FILTER_LEVELS],
                threadOffset: 0,
                hasMoreThreads: false,
                requestId: 0,
                loadingThreads: false
            };

            function showSnack() {
                const snack = document.querySelector(".snack");
                snack.classList.add("show");
                setTimeout(function() {
                    snack.classList.remove("show");
                }, 2000);
            }

            function fallbackCopy(text) {
                const copyText = document.querySelector("#clipboard");
                copyText.value = text;
                copyText.select();
                document.execCommand("copy");
                showSnack();
            }

            function copyText(text) {
                if(navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(showSnack).catch(function() {
                        fallbackCopy(text);
                    });
                    return;
                }
                fallbackCopy(text);
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

            async function apiFetch(api, params) {
                const queryParams = {...params, api: api};
                const query = new URLSearchParams(queryParams).toString();
                const response = await fetch("console.php?" + query);

                if(response.status !== 200) {
                    throw new Error(response.status);
                }

                return response.json();
            }

            function setLoading(active) {
                document.getElementById("loader").style.display = active ? "block" : "none";
            }

            function updateRootLoadMoreVisibility() {
                const hasSelectedThread = !!document.querySelector("#list .thread.selected");
                document.getElementById("loadMoreThreads").style.display = (state.hasMoreThreads && !hasSelectedThread) ? "inline-block" : "none";
            }

            function getFormParams() {
                const form = document.getElementById("searchForm");
                const levels = [...form.querySelectorAll("input[name=\"level\"]")];
                const modes = [...form.querySelectorAll("input[name=\"mode\"]")];
                const checkedLevels = levels.filter(function(input) { return input.checked; });
                const checkedModes = modes.filter(function(input) { return input.checked; });
                const params = {
                    q: form.elements.q.value,
                    mode: checkedModes.length === modes.length ? "" : (checkedModes.length ? checkedModes.map(function(input) { return input.value; }).join(",") : "__none__"),
                    level: checkedLevels.length === levels.length ? "" : (checkedLevels.length ? checkedLevels.map(function(input) { return input.value; }).join(",") : "__none__"),
                    date: form.elements.date.value,
                    f: form.elements.f.value
                };

                return params;
            }

            function applyQuickFilters() {
                const selectedThread = document.querySelector("#list .thread.selected");
                const showAllLevels = state.quickLevels.length === QUICK_FILTER_LEVELS.length;

                for(const thread of document.querySelectorAll("#list .thread")) {
                    const matches = showAllLevels || state.quickLevels.includes(thread.dataset.level);
                    thread.classList.toggle("quick-filtered-out", !selectedThread && !matches);
                }

                for(const line of document.querySelectorAll("#list .thread_line")) {
                    const matches = showAllLevels || state.quickLevels.includes(line.dataset.level);
                    line.classList.toggle("quick-filtered-out", !matches);
                }
            }

            function setQuickFilter(level) {
                if(state.quickLevels.length === QUICK_FILTER_LEVELS.length) {
                    state.quickLevels = [level];
                }
                else if(state.quickLevels.includes(level)) {
                    state.quickLevels = state.quickLevels.filter(function(value) {
                        return value !== level;
                    });
                    if(!state.quickLevels.length) {
                        state.quickLevels = [...QUICK_FILTER_LEVELS];
                    }
                }
                else {
                    state.quickLevels.push(level);
                }

                for(const button of document.querySelectorAll("#quickFilters button[data-level]")) {
                    button.classList.toggle("applied", state.quickLevels.includes(button.dataset.level));
                }
                applyQuickFilters();
            }

            function syncCheckboxSelects() {
                for(const select of document.querySelectorAll(".checkbox-select")) {
                    const inputs = [...select.querySelectorAll("input[type=\"checkbox\"]")];
                    const checked = inputs.filter(function(input) { return input.checked; });
                    const valueNode = select.querySelector(".checkbox-select-value");
                    if(checked.length === inputs.length) {
                        valueNode.textContent = "All";
                    }
                    else if(!checked.length) {
                        valueNode.textContent = "None";
                    }
                    else {
                        valueNode.textContent = checked.map(function(input) { return input.value; }).join(", ");
                    }
                }
            }

            function showFeedback(message, noResult) {
                const list = document.getElementById("list");
                const div = document.createElement("div");
                div.className = "feedback" + (noResult ? " no-result" : "");
                if(message) {
                    div.textContent = message;
                }
                list.replaceChildren(div);
            }

            function createThreadElement(thread) {
                const info = get_level_info(thread.level);
                const node = document.createElement("div");
                node.className = "thread " + String(info.type).replace(/\s+/g, "_");
                node.dataset.level = info.type;
                node.dataset.threadId = thread.thread_id ?? "";
                node.dataset.loaded = "false";

                const title = document.createElement("div");
                title.className = "thread-title";

                const titleContent = document.createElement("div");
                titleContent.className = info.class;
                titleContent.title = info.type;

                const hash = document.createElement("div");
                hash.className = "thread-hash";
                const hashIcon = document.createElement("i");
                hashIcon.className = "icon fa " + info.icon;
                hash.append(hashIcon, document.createTextNode(" " + (thread.thread_id ?? "")));

                titleContent.append(hash, document.createTextNode(" " + (thread.time ?? "")));
                if(thread.lines) {
                    const lineCount = document.createElement("small");
                    lineCount.className = "thread-line-count";
                    lineCount.textContent = "" + thread.lines;
                    titleContent.append(document.createTextNode(" "), lineCount);
                }
                if(thread.ip) {
                    const ip = document.createElement("span");
                    ip.className = "thread-ip";
                    ip.title = "Source IP";
                    ip.textContent = thread.ip;
                    titleContent.append(document.createTextNode(" "), ip);
                }
                if(thread.uri) {
                    const uri = document.createElement("span");
                    uri.className = "thread-uri";
                    uri.title = thread.uri;
                    uri.textContent = thread.uri;
                    titleContent.append(document.createTextNode(" "), uri);
                }
                title.append(titleContent);

                const selector = document.createElement("input");
                selector.type = "checkbox";
                selector.className = "selector";
                selector.dataset.action = "toggle-thread";

                const lines = document.createElement("div");
                lines.className = "thread-lines";
                lines.dataset.offset = "0";
                lines.dataset.hasMore = "true";

                const chevron = document.createElement("i");
                chevron.className = "chevron fa fa-chevron-right";
                lines.append(chevron);

                node.append(title, selector, lines);
                return node;
            }

            function createLineElement(line) {
                const info = get_level_info(line.level);
                const node = document.createElement("div");
                node.className = "thread_line " + String(info.type).replace(/\s+/g, "_");
                node.dataset.level = info.type;

                const title = document.createElement("div");
                title.className = "line-title";
                if(line.match) {
                    title.classList.add("match");
                }

                const meta = document.createElement("span");
                meta.className = info.class;
                meta.title = info.type;
                const metaIcon = document.createElement("i");
                metaIcon.className = "icon fa " + info.icon;
                meta.append(
                    metaIcon,
                    document.createTextNode(" " + (line.time ?? "") + " " + (line.mtime ?? "") + " " + (line.mode ?? ""))
                );

                const location = document.createElement("code");
                location.className = info.class;
                location.textContent = (line.file ?? "") + ":" + (line.line ?? "");

                const inside = document.createElement("b");
                inside.textContent = "in";

                const origin = document.createElement("code");
                origin.className = info.class;
                origin.textContent = ((line?.class?.length) ? line.class + "::" : "") + (line.function ?? "");

                const message = line.message ?? "";
                title.title = message;
                title.append(
                    meta,
                    document.createTextNode(" @ ["),
                    location,
                    document.createTextNode("] "),
                    inside,
                    document.createTextNode(" "),
                    origin,
                    document.createTextNode(": " + message)
                );

                const selector = document.createElement("input");
                selector.className = "selector";
                selector.type = "checkbox";

                const traces = document.createElement("div");
                traces.className = "line-traces";

                const chevron = document.createElement("i");
                chevron.className = "chevron fa fa-chevron-right";
                traces.append(chevron);

                const messageTrace = document.createElement("div");
                messageTrace.className = "trace_line";

                const copy = document.createElement("i");
                copy.className = "fa fa-clipboard icon-copy";
                copy.dataset.action = "copy-message";

                const pre = document.createElement("pre");
                pre.textContent = message;
                messageTrace.append(copy, pre);
                traces.append(messageTrace);

                const stack = Array.isArray(line.stack) ? line.stack : [];
                let index = stack.length;
                for(const trace of stack) {
                    const values = {
                        function: "",
                        line: 0,
                        file: "",
                        ...trace
                    };
                    const stackLine = document.createElement("div");
                    stackLine.className = "trace_line";
                    stackLine.textContent = index + ". " + values.file + " line " + values.line + " (" + values.function + ")";
                    traces.append(stackLine);
                    --index;
                }

                node.append(title, selector, traces);
                return node;
            }

            async function loadThreads() {
                if(state.loadingThreads || !state.hasMoreThreads && state.threadOffset > 0) {
                    return;
                }

                const requestId = state.requestId;
                state.loadingThreads = true;
                setLoading(true);
                updateRootLoadMoreVisibility();

                try {
                    const data = await apiFetch("threads", {
                        ...state.params,
                        offset: state.threadOffset,
                        limit: THREAD_PAGE_SIZE
                    });

                    if(requestId !== state.requestId) {
                        return;
                    }

                    const fragment = document.createDocumentFragment();
                    for(const thread of data.items ?? []) {
                        fragment.append(createThreadElement(thread));
                    }
                    document.getElementById("list").append(fragment);
                    applyQuickFilters();
                    state.threadOffset = data.next_offset ?? state.threadOffset;
                    state.hasMoreThreads = !!data.has_more;

                    if(state.threadOffset === 0 && !(data.items ?? []).length) {
                        showFeedback("", true);
                    }
                }
                catch(error) {
                    if(requestId === state.requestId) {
                        showFeedback("Unable to load log data.", false);
                    }
                }
                finally {
                    if(requestId === state.requestId) {
                        state.loadingThreads = false;
                        setLoading(false);
                        updateRootLoadMoreVisibility();
                    }
                }
            }

            async function loadThreadLines(threadNode) {
                const linesNode = threadNode.querySelector(".thread-lines");
                if(linesNode.dataset.loading === "true" || linesNode.dataset.hasMore === "false") {
                    return;
                }

                const requestId = state.requestId;
                linesNode.dataset.loading = "true";
                setLoading(true);

                try {
                    const data = await apiFetch("lines", {
                        ...state.params,
                        thread_id: threadNode.dataset.threadId,
                        offset: linesNode.dataset.offset || 0,
                        limit: LINE_PAGE_SIZE
                    });

                    if(requestId !== state.requestId) {
                        return;
                    }

                    const oldButton = linesNode.querySelector("button.load-lines");
                    if(oldButton) {
                        oldButton.remove();
                    }

                    const fragment = document.createDocumentFragment();
                    for(const line of data.items ?? []) {
                        fragment.append(createLineElement(line));
                    }
                    linesNode.append(fragment);

                    linesNode.dataset.offset = data.next_offset ?? linesNode.dataset.offset;
                    linesNode.dataset.hasMore = data.has_more ? "true" : "false";
                    threadNode.dataset.loaded = "true";

                    if(data.has_more) {
                        const loadMore = document.createElement("button");
                        loadMore.type = "button";
                        loadMore.className = "material-button load-more load-lines";
                        loadMore.dataset.action = "load-lines";
                        loadMore.textContent = "Load more";
                        linesNode.append(loadMore);
                    }
                    applyQuickFilters();
                }
                catch(error) {
                    const div = document.createElement("div");
                    div.className = "feedback";
                    div.textContent = "Unable to load thread lines.";
                    linesNode.append(div);
                }
                finally {
                    if(requestId === state.requestId) {
                        linesNode.dataset.loading = "false";
                        setLoading(false);
                    }
                }
            }

            function showOnlySelectedThread(selectedThread) {
                for(const thread of document.querySelectorAll("#list .thread")) {
                    const selector = thread.querySelector("input.selector");
                    if(thread === selectedThread) {
                        thread.style.display = "block";
                        thread.classList.add("selected");
                        thread.classList.add("opened");
                        continue;
                    }
                    thread.style.display = "none";
                    thread.classList.remove("selected");
                    thread.classList.remove("opened");
                    selector.checked = false;
                }
                applyQuickFilters();
                updateRootLoadMoreVisibility();
            }

            function showAllThreads() {
                for(const thread of document.querySelectorAll("#list .thread")) {
                    thread.style.display = "block";
                    thread.classList.remove("selected");
                }
                applyQuickFilters();
                updateRootLoadMoreVisibility();
            }

            async function feed(params) {
                state.params = params || {};
                state.threadOffset = 0;
                state.hasMoreThreads = true;
                state.requestId++;
                document.getElementById("list").replaceChildren();
                document.getElementById("loadMoreThreads").style.display = "none";
                await loadThreads();
            }

            document.addEventListener("DOMContentLoaded", async function() {
                const list = document.getElementById("list");
                const form = document.getElementById("searchForm");

                list.addEventListener("change", async function(event) {
                    if(!event.target.matches("input.selector[data-action=\"toggle-thread\"]")) {
                        return;
                    }

                    const thread = event.target.closest(".thread");
                    if(event.target.checked) {
                        showOnlySelectedThread(thread);
                        if(thread.dataset.loaded !== "true") {
                            await loadThreadLines(thread);
                        }
                        return;
                    }

                    showAllThreads();
                });

                list.addEventListener("click", async function(event) {
                    const actionNode = event.target.closest("[data-action]");
                    if(!actionNode) {
                        return;
                    }

                    if(actionNode.dataset.action === "copy-message") {
                        event.preventDefault();
                        event.stopPropagation();
                        const pre = actionNode.parentNode.querySelector("pre");
                        copyText(pre ? pre.textContent : "");
                    }

                    if(actionNode.dataset.action === "load-lines") {
                        event.preventDefault();
                        event.stopPropagation();
                        await loadThreadLines(actionNode.closest(".thread"));
                    }
                });

                document.getElementById("loadMoreThreads").addEventListener("click", loadThreads);

                document.getElementById("quickFilters").addEventListener("click", function(event) {
                    const button = event.target.closest("button[data-level]");
                    if(!button) {
                        return;
                    }
                    setQuickFilter(button.dataset.level);
                });

                form.addEventListener("click", function(event) {
                    const trigger = event.target.closest(".checkbox-select-trigger");
                    if(trigger) {
                        const select = trigger.closest(".checkbox-select");
                        const wasOpen = select.classList.contains("open");
                        for(const node of form.querySelectorAll(".checkbox-select.open")) {
                            node.classList.remove("open");
                        }
                        select.classList.toggle("open", !wasOpen);
                        return;
                    }

                    const option = event.target.closest(".checkbox-select-option[data-select]");
                    if(!option) {
                        return;
                    }
                    const select = option.closest(".checkbox-select");
                    const checked = option.dataset.select === "all";
                    for(const input of select.querySelectorAll("input[type=\"checkbox\"]")) {
                        input.checked = checked;
                    }
                    syncCheckboxSelects();
                });

                document.addEventListener("click", function(event) {
                    if(event.target.closest(".checkbox-select")) {
                        return;
                    }
                    for(const select of form.querySelectorAll(".checkbox-select.open")) {
                        select.classList.remove("open");
                    }
                });

                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    syncCheckboxSelects();
                    feed(getFormParams());
                });

                form.addEventListener("change", function(event) {
                    if(!event.target.matches(".checkbox-select input[type=\"checkbox\"]")) {
                        return;
                    }
                    syncCheckboxSelects();
                });

                form.elements.f.addEventListener("change", function() {
                    feed(getFormParams());
                });

                document.getElementById("emptyFile").addEventListener("click", function() {
                    feed({...getFormParams(), "empty-file": true});
                });

                syncCheckboxSelects();
                await feed(getFormParams());
            });

        </script>
        </head>
        <body>
        <input style="display: block; position: absolute; top: -100px;" id="clipboard" type="text">
        <div class="snack">Copied to clipboard</div>


        <div id="header">
            <form id="searchForm">
                <div style="display: flex; align-items: flex-end;">
                    <a class="equal-logo" href=""></a>
                    <div class="checkbox-select">
                        <button class="checkbox-select-trigger" type="button"><span class="checkbox-select-value">All</span></button>
                        <label>Levels</label>
                        <div class="checkbox-select-menu">
                            <button class="checkbox-select-option" type="button" data-select="all">Select all</button>
                            <button class="checkbox-select-option" type="button" data-select="none">Select none</button>
                            <div class="checkbox-select-separator"></div>
                            <label><input type="checkbox" name="level" value="SYSTEM" checked> SYSTEM</label>
                            <label><input type="checkbox" name="level" value="DEBUG" checked> DEBUG</label>
                            <label><input type="checkbox" name="level" value="INFO" checked> INFO</label>
                            <label><input type="checkbox" name="level" value="WARNING" checked> WARNING</label>
                            <label><input type="checkbox" name="level" value="ERROR" checked> ERROR</label>
                            <label><input type="checkbox" name="level" value="FATAL" checked> FATAL</label>
                        </div>
                    </div>
                    <div class="checkbox-select">
                        <button class="checkbox-select-trigger" type="button"><span class="checkbox-select-value">All</span></button>
                        <label>Layers</label>
                        <div class="checkbox-select-menu">
                            <button class="checkbox-select-option" type="button" data-select="all">Select all</button>
                            <button class="checkbox-select-option" type="button" data-select="none">Select none</button>
                            <div class="checkbox-select-separator"></div>
                            <label><input type="checkbox" name="mode" value="PHP" checked> PHP</label>
                            <label><input type="checkbox" name="mode" value="SQL" checked> SQL</label>
                            <label><input type="checkbox" name="mode" value="ORM" checked> ORM</label>
                            <label><input type="checkbox" name="mode" value="API" checked> API</label>
                            <label><input type="checkbox" name="mode" value="APP" checked> APP</label>
                            <label><input type="checkbox" name="mode" value="AAA" checked> AAA</label>
                            <label><input type="checkbox" name="mode" value="NET" checked> NET</label>
                        </div>
                    </div>

                    <div class="material-input" style="width: 200px;">
                        <input name="q" type="text" value="" placeholder=" ">
                        <label>Keywords</label>
                        <div class="bar"></div>
                    </div>

                    <div class="material-input" style="width: 200px;">
                        <input name="date" type="date" value="">
                        <label>Date</label>
                        <div class="bar"></div>
                    </div>

                    <div>
                        <button class="material-button" type="submit">Filter</button>
                    </div>
                    <div style="width: 50px;"></div>
                    <div style="margin-left: auto; display: flex; align-items: flex-end;">
                        <div class="material-select" style="width: 100px;">
                            <select name="f">
                                '.$log_options.'
                            </select>
                            <label>File</label>
                            <div class="bar"></div>
                        </div>
                        <button id="emptyFile" class="material-icon-button" type="button" title="Empty file"><i class="fa fa-trash-o"></i></button>
                    </div>
                </div>
            </form>
            <div id="quickFilters" class="quick-filters">
                <div class="quick-filter-actions">
                    <button id="btn-SYSTEM" class="btn btn-system applied" type="button" data-level="SYSTEM">SYSTEM</button>
                    <button id="btn-DEBUG" class="btn btn-success applied" type="button" data-level="DEBUG">DEBUG</button>
                    <button id="btn-INFO" class="btn btn-info applied" type="button" data-level="INFO">INFO</button>
                    <button id="btn-WARNING" class="btn btn-warning applied" type="button" data-level="WARNING">WARNING</button>
                    <button id="btn-ERROR" class="btn btn-danger applied" type="button" data-level="ERROR">ERROR</button>
                </div>
                <div class="quick-view-actions">
                    <a href="#end" class="material-icon-button" title="Jump to bottom"><i class="fa fa-long-arrow-down"></i></a>
                    <a href="#start" class="material-icon-button" title="Jump to top"><i class="fa fa-long-arrow-up"></i></a>
                </div>
            </div>
        </div>
        <div id="loader" class="loader-overlay"><div class="loader-container"><div class="loader-spinner"></div></div></div>
        <div id="start"></div>
        <div id="list"></div>
        <button id="loadMoreThreads" type="button" class="material-button load-more" style="display: none;">Load more</button>
        <div id="end"></div>
        </body>
        </html>
        ';
}
// params given: back-end data provider
else {
    header('Content-Type: application/json; charset=UTF-8');

    $api = $_GET['api'] ?? (isset($_GET['thread_id']) ? 'lines' : 'threads');
    $response = [
        'items'       => [],
        'next_offset' => 0,
        'has_more'    => false
    ];

    function console_int_param(string $name, int $default, int $max): int {
        $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
        if($value === false || $value === null) {
            $value = $default;
        }
        return max(0, min($value, $max));
    }

    function console_list_param(string $name): array {
        static $params = [];
        if(array_key_exists($name, $params)) {
            return $params[$name];
        }
        if(!isset($_GET[$name]) || $_GET[$name] === '') {
            $params[$name] = [];
            return $params[$name];
        }
        $params[$name] = array_values(array_filter(array_map('trim', explode(',', $_GET[$name])), 'strlen'));
        return $params[$name];
    }

    function console_level_rank($level): int {
        $ranks = [
            'FATAL'       => 0,
            'Fatal error' => 0,
            'Parse error' => 0,
            E_ERROR       => 0,
            'ERROR'       => 1,
            E_USER_ERROR  => 1,
            'WARNING'     => 2,
            E_USER_WARNING => 2,
            'INFO'        => 3,
            'NOTICE'      => 3,
            E_USER_NOTICE => 3,
            'DEBUG'       => 4,
            E_USER_DEPRECATED => 4,
            'SYSTEM'      => 5,
            0             => 5
        ];
        return array_key_exists($level, $ranks) ? $ranks[$level] : 6;
    }

    function console_line_matches(array $line, string $query): bool {
        $levels = console_list_param('level');
        if(count($levels) && !in_array((string) ($line['level'] ?? ''), $levels, true)) {
            return false;
        }
        $modes = console_list_param('mode');
        if(count($modes) && !in_array((string) ($line['mode'] ?? ''), $modes, true)) {
            return false;
        }
        if(isset($_GET['date']) && $_GET['date'] !== '' && strpos(($line['time'] ?? ''), $_GET['date']) !== 0) {
            return false;
        }
        if($query !== '' && stripos(($line['message'] ?? ''), $query) === false) {
            return false;
        }
        return true;
    }

    if(file_exists('../log/' . $log_file)) {

        if(isset($_GET['empty-file']) && $_GET['empty-file'] === 'true') {
            $f = fopen('../log/' . $log_file, "r+");
            ftruncate($f, 0);
            fclose($f);
            die(json_encode($response));
        }

        // get query from URL, if any
        $query = $_GET['q'] ?? '';
        $item_offset = console_int_param('offset', 0, PHP_INT_MAX);
        $limit = ($api === 'lines')
            ? console_int_param('limit', DEFAULT_LINE_LIMIT, MAX_LINE_LIMIT)
            : console_int_param('limit', DEFAULT_THREAD_LIMIT, MAX_THREAD_LIMIT);

        $filesize = filesize('../log/' . $log_file);

        // limit processing to the tail of the log file to prevent overload
        $max_read_bytes = constant('MAX_LOG_READ_BYTES');
        $read_offset = 0;

        if($filesize > $max_read_bytes) {
            // start reading from the last MAX_LOG_READ_BYTES bytes
            $read_offset = $filesize - $max_read_bytes;
        }

        // read raw data from log file
        if($f = fopen('../log/'.$log_file, 'r')) {

            // move pointer if needed (tail behavior)
            if($read_offset > 0) {
                fseek($f, $read_offset);

                // discard first partial line (we are likely in the middle of a line)
                fgets($f);
            }

            // lines request (return lines matching filters within a given thread_id)
            if($api === 'lines') {
                $skipped = 0;
                while(($data = fgets($f)) !== false) {
                    if(($line = json_decode($data,true)) === null) {
                        continue;
                    }
                    if(($line['thread_id'] ?? '') != ($_GET['thread_id'] ?? '')) {
                        continue;
                    }
                    if(!console_line_matches($line, $query)) {
                        continue;
                    }

                    if($skipped < $item_offset) {
                        ++$skipped;
                        continue;
                    }

                    $line['match'] = ($query !== '');
                    if(count($response['items']) >= $limit) {
                        $response['has_more'] = true;
                        break;
                    }
                    $response['items'][] = $line;
                }
                $response['next_offset'] = $item_offset + count($response['items']);
            }
            // threads request (return threads summary: lines count, max level, first time)
            else {
                $map_threads = [];
                // step-1 : load all threads_ids
                while (($data = fgets($f)) !== false) {
                    if(($line = json_decode($data,true)) === null) {
                        continue;
                    }
                    if(!isset($line['thread_id'])) {
                        continue;
                    }

                    $thread_id = $line['thread_id'];
                    if(!isset($map_threads[$thread_id])) {
                        $map_threads[$thread_id] = [
                            'thread_id' => $thread_id,
                            'lines'     => 0,
                            'uri'       => '',
                            'ip'        => '',
                            'net_seen'  => false,
                            'level'     => $line['level'] ?? 'SYSTEM',
                            // threads will be sorted on timestamp using a map: we must avoid collisions
                            'time'      => ($line['time'] ?? '').'.'.($line['mtime'] ?? '')
                        ];
                    }
                    elseif(console_level_rank($line['level'] ?? 'SYSTEM') < console_level_rank($map_threads[$thread_id]['level'])) {
                        $map_threads[$thread_id]['level'] = $line['level'];
                    }

                    if(!$map_threads[$thread_id]['net_seen'] && ($line['mode'] ?? '') === 'NET' && isset($line['message']) && is_string($line['message'])) {
                        $map_threads[$thread_id]['net_seen'] = true;
                        $message = json_decode($line['message'], true);
                        if(is_array($message) && isset($message['uri']) && is_scalar($message['uri'])) {
                            $map_threads[$thread_id]['uri'] = (string) $message['uri'];
                        }
                        if(is_array($message) && isset($message['ip']) && is_scalar($message['ip'])) {
                            $map_threads[$thread_id]['ip'] = (string) $message['ip'];
                        }
                    }

                    if(console_line_matches($line, $query)) {
                        ++$map_threads[$thread_id]['lines'];
                    }
                }
                // step-2 : keep only threads with matching lines
                $threads = [];
                foreach($map_threads as $thread) {
                    if($thread['lines'] <= 0) {
                        continue;
                    }
                    unset($thread['net_seen']);
                    $threads[] = $thread;
                }
                usort($threads, function($a, $b) {
                    return strcmp($b['time'], $a['time']);
                });

                $page = array_slice($threads, $item_offset, $limit + 1);
                if(count($page) > $limit) {
                    $response['has_more'] = true;
                    array_pop($page);
                }
                $response['items'] = $page;
                $response['next_offset'] = $item_offset + count($page);
            }
            fclose($f);
        }

    }

    echo json_encode($response);
}
