<?php

	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	
	//Define SMF
	define('SMF', 1);
	
	error_reporting(E_ALL | E_STRICT);
	
	//set the micro start time
	$time_bstart = microtime();

	//debug load time not much to see realy load times and db query count
	$debug_load = false;

	//Experimental Optimizer
	define('loadOpt', 1);
	
	session_cache_limiter('nocache'); 
	session_start();

	if(!ini_get('date.timezone'))
		date_default_timezone_set('GMT');
	
	//create an empty plugin array
	$listeners = array();
	
	//Lets go head and load our files here.
	require_once(str_replace('//', '/', dirname(__FILE__) . '/') . '../Settings.php');
	require_once(dirname(__FILE__) . '/Sources/Subs.php');
	require_once(dirname(__FILE__) . '/Sources/Chat.php');
	require_once(dirname(__FILE__) . '/Sources/Chat-Subs.php');
	require_once(dirname(__FILE__) . '/Sources/Users.php');
	require_once(dirname(__FILE__) . '/Sources/Errors.php');
	
	// Connect to the database
	$smcFunc = array();
	loadDatabase();

	//Load modsettings array
	$modSettings = initModSettings();
	
	//SMF Cookie autentication!!!
	list ($member_id, $password) = initCookies();
	
	// Register a error handler
	set_error_handler('errorHandler');
	register_shutdown_function('shutdownHandler');
	
	//Load SMF's compatibility file for unsupported functions.
	if (@version_compare(PHP_VERSION, '5') == -1)
		require_once($sourcedir . '/Subs-Compat.php');
	
	//Load our theme
	list ($themeurl, $themedir, $thjs, $load_btime, $soundurl, $curtheme, $txt) = initTheme();
	
	//Load Optimizer if applicable
	if (defined('loadOpt'))
		loadOpt();

	//Do charset if needed!
	if (!empty($db_character_set))
		doCharset($db_character_set);
		
	//Is this enabled?
	if (!empty($modSettings['2sichat_disable']))
		die();

	//Do the load check if applicable
	if (!empty($modSettings['2sichat_load_chk']))
		doLoadCHK();
		
	$context['sa_utf8'] = (empty($modSettings['global_character_set']) ? 'ISO-8859-1' : $modSettings['global_character_set']) === 'UTF-8';
	
	//member data.
	loadUserData();
	
	getThemes();
	
	require_once(dirname(__FILE__) . '/Sources/Plugins.php');
	
	if(isset($filter_events['hook_load_file']))//Plugins loading files?.
		call_hook('hook_load_file',array(),false);
		
	$ChatActionArray = array(
		'body' => array('initchat','args' =>  array('body')),
		'head' => array('initchat','args' =>  array('head')),
		'heart' => array('doheart'),
		'typing' => array('typestatus'),
		'closechat' => array('closechat'),
	);
	
	if (isset($_REQUEST['action'])){
		
		if(isset($filter_events['hook_actions']))//Plugins modifying the chat action array?.
			call_hook('hook_actions', array(&$ChatActionArray), false);
			
		$context['chat_action'] = $_REQUEST['action'];
		
		if(!empty($ChatActionArray[$_REQUEST['action']]['args']))
			$ChatActionArray[$_REQUEST['action']][0]($ChatActionArray[$_REQUEST['action']]['args'][0]);
		else
			$ChatActionArray[$_REQUEST['action']][0]();
	}else{ 
		
		if(isset($filter_events['hook_non_actions']))//Plugins adding custom $_REQUEST?.
			call_hook('hook_non_actions', array(), false);
		
		if (!isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id)
			initChatSess();
		else if (isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id)
			savemsg();
		else if (isset($_REQUEST['chat_user_search']) && $member_id)
			chatSearch();
		else if (isset($_REQUEST['home']))
			chatHome();
		
	}
	if ($member_id && isset($_REQUEST['action']) && $_REQUEST['action'] == 'heart' && !empty($modSettings['2sichat_live_online']) || $member_id && !isset($_REQUEST['action']) && !empty($modSettings['2sichat_live_online'])) {
		liveOnline();
	}
	// If output function hasn't been declared lets do it.
	doOutput();
?>