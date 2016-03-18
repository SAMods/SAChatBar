<?php
if (!defined('SMF'))
	die('No direct access...');
/*
@Name: test
@Description: testing filter hook on chatbox template replaces X with close
@Author: SA
@Version: 1
@Author URL: http://samods.github.io/SAChatBar/
@Plugin ID: sa_test
*/
	
//SA Chat hooks
register_hook('hook_chatbox_template', 'testcallback','sa_test');

function testcallback(&$data){

global $buddy_settings;
	
	$data = str_replace('<a href="javascript:void(0)" onclick="javascript:xchat(\''.$buddy_settings['id_member'].'\')">X</a>',
		'<a href="javascript:void(0)" onclick="javascript:xchat(\''.$buddy_settings['id_member'].'\')">Close</a>',
		$data
	);	
}

?>