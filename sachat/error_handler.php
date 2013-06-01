<?php
function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context){

    switch ($error_level) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            logdb($error_file, $error_line, $error_message, "Fatal");
            break;
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            logdb($error_file, $error_line, $error_message, "ERROR");
        break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            logdb($error_file, $error_line, $error_message, "WARN");
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            logdb($error_file, $error_line, $error_message, "INFO");
            break;
        case E_STRICT:
           logdb($error_file, $error_line, $error_message, "DEBUG");
            break;
        default:
            logdb($error_file, $error_line, $error_message, "WARN");
    }
}

function shutdownHandler() //will be called when php script ends.
{
    $lasterror = error_get_last();
    switch ($lasterror['type'])
    {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_PARSE:
        logdb($lasterror['file'], $lasterror['line'],  $lasterror['message'], "FATAL");

    }
}

function logdb($errorfile, $errorline, $errormes, $errortype){
  global $smcFunc, $last_error;

   $error_info = array($errortype,$errorfile,$errormes,$errorline); 
   if (empty($last_error) || $last_error != $error_info){
       $smcFunc['db_insert']('','
		      {db_prefix}2sichat_error',
		      array('type' => 'string','file' => 'string','info' => 'string','line' =>'string'),
		      $error_info,
	           array('id')
	   );
	   $last_error = $error_info;
	}
}
?>