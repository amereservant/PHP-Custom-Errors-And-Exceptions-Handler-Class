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
    private static $errorLog = array( );

    public static function parseError( $errorNumber, $errorString, $errorFile, $errorLine ) {
    $errorInfo['errorString'] = self::getErrorType($errorNumber);
	$errorInfo['errorString'] .= self::getErrorString($errorString, $errorFile, $errorLine);
	$errorInfo['backTrace'] = self::parseBackTrace( debug_backtrace( ), 'error' );
	self::$errorLog[] = $errorInfo;
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
	    $string .= sprintf( "\n$tabs File %s on line %s.\n\t$tabs%s%s%s\n",
			     $value['file'],
			     $value['line'],
			     ( !empty( $value['object'] ) ? get_class( $value['object'] ).$value['type'] : null ),
			     $value['function'],
			     ( count( $value['args'] ) ? '( "'.implode( '", "', $value['args'] ).'" )' : '( )' ) );
	}
	return $string;
    }

    public static function logErrors( ) {
	    if ( count( self::$errorLog ) < 1 ) {
	        return;
	    }
	    $string = '';
	    foreach( self::$errorLog as $error ) {
	        $string .= print_r( $error['errorString'], true );
	    }
	    //$handle = fopen( 'error.log', 'a' );
	    //fwrite( $handle, $string );
	    //fclose( $handle );
	    error_log($string, 3, 'mylog.txt');
	    if ( self::$display ) {
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
	    $errorInfo['errorString'] = self::getErrorType($errorNumber);
	    $errorInfo['errorString'] .= self::getErrorString($errorString, $errorFile, $errorLine);
	    $errorInfo['backTrace'] = self::parseBackTrace( debug_backtrace( ), 'method' );
	    self::$errorLog[] = $errorInfo;
    }
    
    public static function test()
    {
        self::newError('Triggered from a method.', E_USER_NOTICE);
    }
}
?>
