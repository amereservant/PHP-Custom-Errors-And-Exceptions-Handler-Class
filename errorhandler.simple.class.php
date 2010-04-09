<?php
/**
 * DEVELOPMENT PHASE
 *
 * Project hosted at http://github.com/amereservant/PHP-Custom-Errors-And-Exceptions-Handler-Class
 * All information can be found there.
 */

set_error_handler( array( 'ErrorHandler', 'logError' ) );

final class ErrorHandler {
    public static $display = false;
    private static $errorLog = array( );

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

    private static function parseBackTrace( $backTrace, $type ) {
	if( $type == 'error' ) {
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

    public static function logError( $errorNumber, $errorString, $errorFile, $errorLine ) {
	$errorInfo['errorString'] = sprintf( '%s: %s in %s on line %s.',
				       self::getErrorType( $errorNumber ),
				       $errorString,
				       $errorFile,
				       $errorLine );
	$errorInfo['backTrace'] = self::parseBackTrace( debug_backtrace( ), 'error' );
	self::$errorLog[] = $errorInfo;
    }

    public static function logErrorQueue( ) {
	if ( count( self::$errorLog ) > 0 ) {
	    $string = '';
	    foreach( self::$errorLog as $error ) {
		$string .= print_r( $error['errorString'], true );
	    }
	    file_put_contents( Config::$errorLog, $string, FILE_APPEND );
	    if ( self::$display ) {
		echo "<pre>$string</pre>";
	    }
	}
    }
}
