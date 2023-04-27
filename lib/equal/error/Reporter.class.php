<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\error;

use equal\organic\Service;
use equal\php\Context;


class Reporter extends Service {

    private $debug_mode;
    private $debug_level;

    private $thread_id;

    /**
     * Constructor defines which methods have to be called when errors and uncaught exceptions occur
     *
     */
    public function __construct() {
        // #memo - $thread_id depends on the current PHP thread. A same thread can stack several contexts. In the console, logs are grouped based on their thread_id.
        $this->thread_id = substr(md5(getmypid().';'.hrtime(true)), 0, 8);
        $this->debug_mode  = (defined('DEBUG_MODE'))?constant('DEBUG_MODE'):0;
        $this->debug_level = (defined('DEBUG_LEVEL'))?constant('DEBUG_LEVEL'):0;
        // ::errorHandler() will deal with error and debug messages depending on debug source value
        ini_set('display_errors', 1);
        // use QN_REPORT_x for reporting, E_ERROR for fatal errors only, E_ALL for all errors
        error_reporting($this->debug_level);
        set_error_handler(__NAMESPACE__."\Reporter::errorHandler");
        set_exception_handler(__NAMESPACE__."\Reporter::uncaughtExceptionHandler");
    }

    /**
     * Static list of constants required by current provider
     *
     */
    public static function constants() {
        return ['QN_LOG_STORAGE_DIR', 'QN_REPORT_FATAL', 'QN_REPORT_ERROR', 'QN_REPORT_WARNING', 'QN_REPORT_DEBUG', 'QN_REPORT_INFO', 'QN_REPORT_DEBUG'];
    }

    /**
     * Handles uncaught exceptions, which include deliberately triggered fatal-error
     * In all cases, these are critical errors that cannot be recovered and need an immediate stop (fatal error)
     */
    public static function uncaughtExceptionHandler($exception) {
        $code = $exception->getCode();
        $msg = $exception->getMessage();
        if($code != QN_REPORT_FATAL) {
            $msg = '[uncaught exception]-'.$msg;
        }
        // retrieve instance and log error
        $instance = self::getInstance();
        $trace = $exception->getTrace();
        if(count($trace)) {
            $instance->log($code, $msg, $trace[0]);
        }
        // prevent processing
        exit(1);
    }

    /**
    * Main method for error handling.
    * This is invoked either in scripts using `trigger_error()` calls or when a internal PHP error is raised.
    *
    * @param mixed $errno
    * @param mixed $errmsg
    * @param mixed $errfile
    * @param mixed $errline
    * @param mixed $errcontext
    */
    public static function errorHandler($errno, $errmsg, $errfile='', $errline=0, $errcontext=[]) {
        // dismiss handler if not required
        if (!(error_reporting() & $errno)) {
            return;
        }
        // adapt error code
        $code = $errno;

        $depth = 0;
        switch($errno) {
            // handler was invoked using trigger_error()
            case QN_REPORT_DEBUG:       // E_USER_DEPRECATED
            case QN_REPORT_INFO:        // E_USER_NOTICE
            case QN_REPORT_WARNING:     // E_USER_WARNING
            case QN_REPORT_ERROR:       // E_USER_ERROR
            // #memo - fatal errors always stop the script before reaching this point
            case QN_REPORT_FATAL:       // E_ERROR
                $depth = 2;
                break;
            // handler was invoked by PHP internals
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                $code = QN_REPORT_FATAL;
                break;
            case E_RECOVERABLE_ERROR:
                $code = QN_REPORT_ERROR;
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
                $code = QN_REPORT_WARNING;
                break;
        }
        // retrieve instance and log error
        $instance = self::getInstance();
        $instance->log($code, $errmsg, self::getTrace($depth));
    }

    /**
     * Handler for debug messages requests.
     * Appends one line to the log file.
     */
    private function log($code, $msg, $trace) {
        // discard non-applicable log requests
        if($this->debug_mode == 0 || $this->debug_level == 0 || !($code & $this->debug_level))  {
            return;
        }
        // check reporting mode, if provided
        $mode = QN_MODE_PHP;
        if(strpos($msg, '::') == 3) {
            // default to mask QN_MODE_PHP
            $source = QN_MODE_PHP;
            $parts = explode('::', $msg, 2);
            if(count($parts) > 1) {
                $source = (strlen($parts[0]))?('QN_MODE_'.$parts[0]):$source;
                $msg = $parts[1];
            }
            if(!is_numeric($source) && @constant($source)) {
                $source = constant($source);
            }
            $mode = (int) $source;
        }
        // discard non-applicable log requests
        if(!($this->debug_mode & $mode)) {
            return;
        }

        $time_parts = explode(" ", microtime());
        // build error message
        $error_json = [
            'thread_id'     => $this->thread_id,
            'time'          => date('c', $time_parts[1]),
            'mtime'         => substr($time_parts[0], 2, 6),
            'level'         => qn_debug_code_name($code),
            'mode'          => qn_debug_mode_name($mode),
            'class'         => $trace['class'],
            'function'      => $trace['function'],
            'file'          => $trace['file'],
            'line'          => $trace['line'],
            'message'       => $msg,
            'stack'         => []
        ];

        // append backtrace if required (fatal errors)
        if(in_array($code, [QN_REPORT_WARNING, QN_REPORT_ERROR, QN_REPORT_FATAL])) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            // remove 2 calls related to Reporter Service
            array_splice($stack, 0, 2);
            $error_json['stack'] = $stack;
        }

        // #todo - when created using CLI, file is assigned with current uid (which might prevent the http service to access it)
        $filepath = QN_LOG_STORAGE_DIR.'/eq_error.log';

        // #memo - if logging level is set to E_ALL with all mode enabled the log file grows quickly

        // append message to log file (bypass if debug is disabled)
        file_put_contents($filepath, json_encode($error_json).PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Returns the (n-offset)th line from current backtrace.
     * Result is adapted depending on the scope the trace refers to.
     *
     */
    private static function getTrace($offset=0) {
        // #memo - first trace is current call
        $n = $offset + 1;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $n+1);

        $target = (array) (isset($backtrace[$n])?$backtrace[$n]:[]);

        // get last item from backtrace
        $trace = array_merge([
                'function'  => '',
                'line'      => 0,
                'file'      => '',
                'class'     => '',
                'object'    => null,
                'args'      => [],
            ], $target);

        // if trace refers to an included file, go one level up
        if($n > 0 && isset($trace['function']) && in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])) {
            --$n;
            $trace['function'] = '';
            $trace['file'] = $backtrace[$n]['file'];
            $trace['line'] = $backtrace[$n]['line'];
        }

        // if file is missing, use the parent file
        if($n > 0 && !isset($trace['file'])) {
            if(isset($backtrace[$n-1]['file'])) {
                $trace['file'] = $backtrace[$n-1]['file'];
            }
            if(isset($backtrace[$n-1]['line'])) {
                $trace['line'] = $backtrace[$n-1]['line'];
            }
        }
        return $trace;
    }

    public function fatal($msg) {
        $this->log(QN_REPORT_FATAL, $msg, self::getTrace(2));
        die('fatal_error');
    }

    public function error($msg) {
        $this->log(QN_REPORT_ERROR, $msg, self::getTrace(2));
    }

    public function warning($msg) {
        $this->log(QN_REPORT_WARNING, $msg, self::getTrace(2));
    }

    public function info($msg) {
        $this->log(QN_REPORT_INFO, $msg, self::getTrace(2));
    }

    public function debug($msg) {
        $this->log(QN_REPORT_DEBUG, $msg, self::getTrace(2));
    }

}