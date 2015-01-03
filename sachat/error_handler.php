<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SAChat'))
		die('No direct access...');
		
	function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context) {

		switch ($error_level) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_PARSE:
				logError($error_file, $error_line, $error_message, "Fatal");
				break;
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
				logError($error_file, $error_line, $error_message, "ERROR");
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				logError($error_file, $error_line, $error_message, "WARN");
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				logError($error_file, $error_line, $error_message, "INFO");
				break;
			case E_STRICT:
				logError($error_file, $error_line, $error_message, "DEBUG");
				break;
			default:
				logError($error_file, $error_line, $error_message, "WARN");
		}
	}

	function shutdownHandler() { //will be called when php script ends.
		$lasterror = error_get_last();
		switch ($lasterror['type']) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_PARSE:
				logError($lasterror['file'], $lasterror['line'], $lasterror['message'], "FATAL");
		}
	}

	/**
	 * @param string $errortype
	 */
	function logError($errorfile, $errorline, $errormes, $errortype) {
		global $smcFunc, $modSettings, $last_error;

		if(!empty($modSettings['2sichat_e_logs'])){
			$error_info = array($errortype, $errorfile, $errormes, $errorline);
			if (empty($last_error) || $last_error != $error_info) {
				$smcFunc['db_insert']('', '
					  {db_prefix}2sichat_error', array('type' => 'string', 'file' => 'string', 'info' => 'string', 'line' => 'string'), $error_info, array('id')
				);
				$last_error = $error_info;
			}
		}
	}

?>