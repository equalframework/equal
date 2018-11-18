<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\error;

use qinoa\organic\Service;
use qinoa\php\Context;


class Reporter extends Service {
	
    // PHP context instance
    private $context;
    
    /**
    * Contructor defines which methods have to be called when errors and uncaught exceptions occur
    *
    */
	public function __construct(Context $context) {
        $this->context = $context;
		set_error_handler(__NAMESPACE__."\Reporter::errorHandler");
		set_exception_handler(__NAMESPACE__."\Reporter::uncaughtExceptionHandler");
	}

    /**
     * Static list of constants required by current provider
     *
     */
    public static function constants() {
        return ['QN_LOG_STORAGE_DIR', 'QN_REPORT_FATAL', 'QN_REPORT_ERROR', 'QN_REPORT_WARNING', 'QN_REPORT_DEBUG'];
    }
    
    public function getThreadId() {
        static $thread_id = null;
        
        if(!$thread_id){
            // assign a unique thread ID (using apache pid, current unix time, and invoked script with operation, if any)
            $operation = $this->context->get('operation');
            $data = $this->context->getPid().';'.$this->context->getTime().';'.$_SERVER['SCRIPT_NAME'].(($operation)?" ($operation)":'');
            // return a base64 URL-safe encoded identifier
            $thread_id = strtr(base64_encode($data), '+/', '-_');
        }
        return $thread_id;
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
//        $instance->log($code, $msg, self::getTrace(1));
        $trace = $exception->getTrace();
        if(count($trace)) {
            $instance->log($code, $msg, $trace[0]);
        }
        die();
	}

	/**
    * Main method for error handling.
    * This is invoked either in scripts using trigger_error calls or when a internal PHP error is raised.
	*
	* @param mixed $errno
	* @param mixed $errmsg
	* @param mixed $errfile
	* @param mixed $errline
	* @param mixed $errcontext
	*/
	public static function errorHandler($errno, $errmsg, $errfile, $errline, $errcontext) {      
        // dismiss handler if not required
        if (!(error_reporting() & $errno)) return;
        // adapt error code
        $code = $errno;
        
        $depth = 1;
        switch($errno) {
            // handler was invoked using trigger_error()
            case QN_REPORT_DEBUG:       // E_USER_NOTICE
            case QN_REPORT_WARNING:     // E_USER_WARNING
            case QN_REPORT_ERROR:       // E_USER_ERROR
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
            case E_USER_DEPRECATED:
                $code = QN_REPORT_WARNING;
                break;
        }
        // retrieve instance and log error
        $instance = self::getInstance();
        $instance->log($code, $errmsg, self::getTrace($depth));
	}
    
    private function log($code, $msg, $trace) {
        // check reporting level 
        if($code <= error_reporting()) {
            // handle debug messages
            if($code == QN_REPORT_DEBUG) {
                // default to arbitrary '1' mask debug info 
                $source = '1';
                $parts = explode('::', $msg, 2);
                if(count($parts) > 1) {
                    $source = $parts[0];
                    $msg = $parts[1];
                }
                if(!is_numeric($source)) {
                    $mask = constant($source);
                }
                else {
                    $mask = (int) $source;
                }
                if(!(DEBUG_MODE & $mask)) return;
            }

            // build error message
            $origin = '{main}()';
            if(isset($trace['function'])) {
                if(isset($trace['class'])) $origin = $trace['class'].'::'.$trace['function'].'()';
                else $origin = $trace['function'].'()';
            }

            $file = (isset($trace['file']))?$trace['file']:'';
            $line = (isset($trace['line']))?$trace['line']:'';
            
            $error =  $this->getThreadId().';'.microtime(true).';'.$code.';'.$origin.';'.$file.';'.$line.';'.$msg.PHP_EOL;
            
            // append backtrace if required (fatal errors)
            
            if($code == QN_REPORT_FATAL) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $n = count($backtrace);
                if($n > 2) {
                    for($i = 2; $i < $n; ++$i) {
                        $trace = $backtrace[$i];
                        $origin = '';                    
                        if(isset($trace['class'])) $origin = $trace['class'].'::'.$trace['function'].'()';
                        else if(isset($trace['function'])) $origin = $trace['function'].'()';
                        $error .= "# $origin @ [{$trace['file']}:{$trace['line']}]".PHP_EOL;                
                    }
                }
            }
            // append error message to log file
            file_put_contents(QN_LOG_STORAGE_DIR.'/qn_error.log', $error, FILE_APPEND);                        
        }
    }
    
    private static function getTrace($increment=0) {
        // we need to go down 4 levels max
        $limit = 2+$increment;
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);

        $trace = [];
        // retrieve info from where the error was actually raised  ($backtrace[0] being related to current getTrace() call)
        $n = 1+$increment;
        
        if(count($backtrace) > $n) {
            // get last item from backtrace
            $trace = $backtrace[$n];

            if(isset($trace['function']) && isset($trace['class']) && in_array($trace['function'], ['errorHandler', 'debug'])) {
                unset($trace['function']);
            }
            else if(isset($trace['function']) && isset($trace['args']) && in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])) {
                unset($trace['function']);
                $trace['file'] = $backtrace[$n-1]['file'];
                $trace['line'] = $backtrace[$n-1]['line'];
            }
            else if(!isset($trace['file'])) {
                $trace['file'] = $backtrace[$n-1]['file'];
                $trace['line'] = $backtrace[$n-1]['line'];
            }        
        } 
/*        
        if($trace['function'] == 'trigger_error') {
                $trace['file'] = $backtrace[$n-1]['file'];
                $trace['line'] = $backtrace[$n-1]['line'];                
            
        }            
        if(in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])) {
            unset($trace['function']);
            if(isset($backtrace[$n-1]['function']) && in_array($backtrace[$n-1]['function'], ['include', 'require', 'include_once', 'require_once'])) {
                $trace['file'] = $backtrace[$n-2]['file'];
                $trace['line'] = $backtrace[$n-2]['line'];     
            }
            else {
                $trace['file'] = $backtrace[$n-1]['file'];
                $trace['line'] = $backtrace[$n-1]['line'];                
            }
        }
        else if(!isset($trace['function'])) {
            if(isset($backtrace[$n-2]['file'])) {
                $trace['file'] = $backtrace[$n-2]['file'];
                $trace['line'] = $backtrace[$n-2]['line'];                
            }
            else {
                $trace['file'] = $backtrace[$n-1]['file'];
                $trace['line'] = $backtrace[$n-1]['line'];                                
            }
        }
        else if(!isset($trace['file'])) {
            $trace['file'] = $backtrace[$n-1]['file'];
            $trace['line'] = $backtrace[$n-1]['line'];
        }
     */
        return $trace;        
    }

    public function fatal($msg) {
        $this->log(QN_REPORT_FATAL, $msg, self::getTrace());
        die();
    }
    
    public function error($msg) {
        $this->log(QN_REPORT_ERROR, $msg, self::getTrace());        
    }
    
    public function warning($msg) {
        $this->log(QN_REPORT_WARNING, $msg, self::getTrace());        
    }
    
    public function debug($source, $msg) {
        $this->log(QN_REPORT_DEBUG, $source.'::'.$msg, self::getTrace());
    }   
    
}