<?php
set_error_handler( array( 'ErrorHandler', 'parseError' ) );

/**
 * DEVELOPMENT PHASE
 *
 * Project hosted at http://github.com/amereservant/PHP-Custom-Errors-And-Exceptions-Handler-Class
 * All information can be found there.
 */
 
final class ErrorHandler {
    static public $display = false;
    private static $displayLevel;
    private static $logLevel;
    private static $errorLog = array( );
    private static $errorDisplay = array( );

    public static function parseError( $errorNumber, $errorString, $errorFile, $errorLine ) {
        if(Config::getParam('display', 'display_mode') == 'user')
        {
            $errorDispInfo['errorString'] = self::getUserMessage($errorNumber);
        }
        $errorInfo['errorString'] = self::getErrorType($errorNumber);
        $errorInfo['errorString'] .= self::getErrorString($errorString, $errorFile, $errorLine);
        
        $errorInfo['backTrace'] = self::parseBackTrace( debug_backtrace( ), 'error' );
	    if( self::checkErrorLevel($errorNumber, 'logs') )
        {
	        self::$errorLog[] = $errorInfo;
        }
        if( self::checkErrorLevel($errorNumber, 'display') )
        {
            if(isset($errorDispInfo['errorString']) && !empty($errorDispInfo['errorString']))
            {
                $errorInfo['errorString'] = $errorDispInfo['errorString'];
            }
            self::$errorDisplay[] = $errorInfo;
        }
    }
    
    private static function getErrorType( $errorNumber ) {
        switch ( $errorNumber ) {
	        case E_NOTICE:
	        case E_USER_NOTICE:
		    $type = 'Notice';
		    break;
	        case E_WARNING:
	        case E_USER_WARNING:
		    $type = 'Warning';
		    break;
	        case E_ERROR:
	        case E_USER_ERROR:
		    $type = 'Fatal Error';
		    break;
	        default:
		    $type = 'Unknown Error';
		    break;
        }
        return $type;
    }
    
    private static function getErrorString($errorString, $errorFile, $errorLine)
    {
        $string = ': "'.$errorString.'" in '.$errorFile.' on line '. $errorLine .'.';
        return $string;
    }
    
    private static function parseBackTrace( $backTrace, $type ) {
	    if($type == 'error')
	    {
            unset( $backTrace[0] );
        }
        $backTrace = array_reverse( $backTrace );
        if ( count( $backTrace ) < 1 ) {
            return;
        }
        $string = '';
	    $tabs = "";
	    foreach( $backTrace as $key => $value ) {
	        if ( $key ) {
		    $tabs .= "\t";
	        }
	        $string .= sprintf( "$tabs File %s on line %s.\n\t$tabs%s%s%s\n",
			         $value['file'],
			         $value['line'],
			         ( !empty( $value['class'] ) ? $value['class'].$value['type'] : null ),
			         $value['function'],
			         ( count( $value['args'] ) ? '( "'.implode( '", "', $value['args'] ).'" )' : '( )' ) );
	    }
	    return $string;
    }

    public static function getErrors( $type ) {
	    $logCount = count(self::$errorLog);
	    $displayCount = count(self::$errorDisplay);
	    if ($type == 'log' && $logCount < 1 ) {
	        return;
	    }
	    elseif($type == 'display' && $displayCount < 1 ) {
	        return;
	    }
	    elseif($type == 'both' && $displayCount < 1 && $logCount < 1)
	    {
	        return;
	    }
	    $string = '';
	    $stringLog = '';
	    $dispmode = Config::getParam('display', 'display_mode');
	    $logmode = Config::getParam('logs', 'log_mode');
	    $logto = (int)Config::getParam('logs', 'log_to');
	    if(($type == 'log' || $type == 'both') && $logCount > 0)
	    {
	        foreach( self::$errorLog as $error ) 
	        {
	            $stringLog .= ($logto == 3 ? '[' . date('d-M-Y H:i:s') . ']':'') . 
	                print_r( ($logmode == "both" ? $error : ($logmode == 'backtrace' ? 
	                $error['backTrace'] : $error['errorString'])), true ) . 
	                ($logto == 3 ? "\n":'');
	        }
	        if( (bool) Config::getParam('logs', 'log_errors') )
	        {
	            $logto == 4 ? error_log($stringLog, 4):($logto == 3 ? error_log($stringLog,
	                3, Config::getParam('logs', 'logfile')):error_log($stringLog, 0));
	        }
	    }
	    if(($type == 'display' || $type == 'both') && $displayCount > 0)
	    {
	        foreach( self::$errorDisplay as $disp )
	        {
	            $string .= print_r( ($dispmode == "both" ? $disp : ($dispmode == 'backtrace' ?
	                $disp['backTrace'] : $disp['errorString'])), true );
	        }
	        echo "<pre>$string</pre>";
	    }
    }
    
    public static function newError($errorString, $errorNumber)
    {
        $backtrace = debug_backtrace();
        if(count($backtrace) < 1)
        {
            return;
        }
        $errorFile = !empty($backtrace[0]['file']) ? $backtrace[0]['file']:'';
	    $errorLine = !empty($backtrace[0]['line']) ? $backtrace[0]['line']:'';
	    if(Config::getParam('display', 'display_mode') == 'user')
        {
            $errorDispInfo['errorString'] = self::getUserMessage($errorNumber);
        }
        $errorInfo['errorString'] = self::getErrorType($errorNumber);
        $errorInfo['errorString'] .= self::getErrorString($errorString, $errorFile, $errorLine);
        $errorInfo['backTrace'] = self::parseBackTrace( debug_backtrace( ), 'method' );
	    
	    if( self::checkErrorLevel($errorNumber, 'logs') )
        {
	        self::$errorLog[] = $errorInfo;
	    }
	    if( self::checkErrorLevel($errorNumber, 'display') )
	    {
	        if(isset($errorDispInfo['errorString']) && !empty($errorDispInfo['errorString']))
            {
                $errorInfo['errorString'] = $errorDispInfo['errorString'];
            }
            self::$errorDisplay[] = $errorInfo;
        }
    }
    
    private static function checkErrorLevel($errorNumber, $section)
    {
        if(!self::$displayLevel) { self::$displayLevel = Config::getParam('display', 'display_level'); }
	    if(!self::$logLevel) { self::$logLevel = Config::getParam('logs', 'log_level'); }
	    $setLevel = $section == 'logs' ? self::$logLevel : self::$displayLevel;
        
        if($setLevel & $errorNumber) {
            return true;
        }
        if($setLevel & E_WARNING) {
            if($errorNumber & E_WARNING || $errorNumber & E_USER_WARNING) {
                return true;
            }
        }
        if($setLevel & E_NOTICE) {
            if($errorNumber & E_NOTICE || $errorNumber & E_USER_NOTICE) {
                return true;
            }
        }
        if($setLevel & E_ERROR) {
            if($errorNumber & E_USER_ERROR) {
                return true;
            }
        }
        return false;
    }
    
    private static function getUserMessage($errorNumber)
    {
        switch($errorNumber)
        {
            case E_USER_ERROR:
                return 'FATAL ERROR: A fatal error has occurred.  Please notify the ' . 
                       'Administrator if it continues.';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                return 'WARNING: There is a problem and this program may not work correctly'.
                       ' until it is fixed.  Please notify the Adminstrator if it continues.';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'NOTICE: There\'s a small issue with the script.  Please let the ' . 
                       'Administrator know about it.';
                break;
            default:
                return 'An unknown error has occurred.  Please notify Administrator of this.';
                break;
        }
    }
            
    public static function test()
    {
        self::newError('Triggered from a method.', E_USER_NOTICE);
    }
}
?>
