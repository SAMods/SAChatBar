<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('No direct access...');
	
	function chatHome(){
		global $context;
		$context['HTML'] = chat_test_template();
	}
	
	function genMemcount() {
		return genMemList('count');	
	}

	function liveOnline() {

		global $modSettings, $context;

		if (empty($modSettings['2sichat_dis_list'])) {
			$context['JSON']['ONLINE'] = genMemList('list');
			$context['JSON']['CONLINE'] = genMemcount('count');
		}
	}
	
	function doheart() {
		global $member_id;
		if(!empty($member_id) && is_int($member_id)){
			CheckActive();
			CheckTyping();
			getBuddySession();
			newMsgPrivate();
		
		}
	}	
	
	function allowedTodo($permission){
		global $modSettings, $user_settings;
		
		if (empty($permission))
			return true;

		if (empty($user_settings))
			return false;

		if (!empty($user_settings['is_admin']))
			return true;
			
		if (!empty($user_settings['is_mod']))
			return true;
			
		if(empty($modSettings['2sichat_permissions']) && $permission != '2sichat_bar_adminmode')
			return true;
			
		if (!is_array($permission) && in_array($permission, $user_settings['permissions']))
			return true;
		// Search for any of a list of permissions.

		elseif (is_array($permission) && count(array_intersect($permission, $user_settings['permissions'])) != 0)
			return true;
		// You aren't allowed, by default.
		else
			return false;
	}

	function loadPermissionsData($G){
		global $smcFunc,$modSettings, $user_settings;
		
		if (empty($user_settings['permissions']))
		{		
			// Get the general permissions.
			$request = $smcFunc['db_query']('', '
				SELECT permission, add_deny
				FROM {db_prefix}permissions
				WHERE id_group IN ({array_int:member_groups})',
				array(
					'member_groups' => $G,
				)
			);
			$removals = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (empty($row['add_deny']))
					$removals[] = $row['permission'];
				else
					$user_settings['permissions'][] = $row['permission'];
			}
			$smcFunc['db_free_result']($request);
		}
		if (!empty($modSettings['permission_enable_deny']))
			$user_settings['permissions'] = array_diff($user_settings['permissions'], $removals);
	}
	
	function getThemes(){
		global $boarddir, $dirArray, $indexCount;
		
		$myDirectory = opendir($boarddir . '/sachat/themes/');
		
		while ($entryName = readdir($myDirectory)) {
			$dirArray[] = $entryName;
		}
		
		closedir($myDirectory);
		$indexCount = count($dirArray);
		sort($dirArray);
	}
	
	function fixAvatar($data) {
		global $modSettings, $boardurl;

		if (!$data['avatar'] && $data['id_attach']) {
			if ($data['attachment_type'] == 0) {
				$data['avatar'] = $boardurl . '/index.php?action=dlattach;attach=' . $data['id_attach'] . ';type=avatar';
			}
			if ($data['attachment_type'] == 1) {
				$data['avatar'] = (isset($modSettings['custom_avatar_enabled']) ? $modSettings['custom_avatar_url'] : $modSettings['avatar_url']) . '/' . $data['filename'];
			}
		} else if (!$data['avatar']) {
			$data['avatar'] = LoadImage('blankuser.png');
		}
		if (substr($data['avatar'], 0, 4) != 'http') {
			$data['avatar'] = $modSettings['avatar_url'] . '/' . $data['avatar'];
		} // Allot of junk to fix one avatar, SMF needs to get this right.

		return $data['avatar'];
	}
	
	function doCharset($db_character_set) {
		global $smcFunc;

		if (!empty($db_character_set))
			$smcFunc['db_query']('set_character_set', 'SET NAMES ' . $db_character_set, array());
	}

	function initModSettings() {
		global $smcFunc, $modSettings;

		if (($modSettings = cachegetData('modSettings', 90)) == null) {
			$results = $smcFunc['db_query']('', '
				SELECT variable, value
				FROM {db_prefix}settings', array()
			);
			$modSettings = array();
			while ($row = $smcFunc['db_fetch_row']($results)) {
				$modSettings[$row[0]] = $row[1];
			}
			$smcFunc['db_free_result']($results);
			cacheputData('modSettings', $modSettings, 90);
		}
		return $modSettings;
	}

	function initCookies() {
		global $cookiename;

		if (isset($_COOKIE[$cookiename]) && preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~i', $_COOKIE[$cookiename]) == 1) {
			list ($member_id, $password) = @unserialize($_COOKIE[$cookiename]);
			$member_id = !empty($member_id) && strlen($password) > 0 ? (int) $member_id : 0;

			return array($member_id, $password);
		} elseif (isset($_COOKIE[$cookiename])) {
			list ($member_id, $password) = @unserialize(stripslashes($_COOKIE[$cookiename]));
			$member_id = !empty($member_id) && strlen($password) > 0 ? (int) $member_id : 0;

			return array($member_id, $password);
		}
	}

	function initTheme() {
		global $boardurl, $modSettings, $time_bstart, $boarddir;
		
		if(isset($_REQUEST['theme']))
			$curtheme = $_REQUEST['theme'];
		elseif (!isset($_REQUEST['theme']) && isset($_COOKIE[$modSettings['2sichat_cookie_name'].'_Theme']))
			$curtheme = $_COOKIE[$modSettings['2sichat_cookie_name'].'_Theme'];
		else
			$curtheme = 'default';
			
		if ($curtheme && is_file('./themes/' . $curtheme . '/css/style.css')){
			$themeurl = $boardurl . '/sachat/themes/' . $curtheme;
			$themedir = $boarddir . '/sachat/themes/' . $curtheme;
			$thjs = 'theme=' . $curtheme . '&';
			$load_btime = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_bstart)), 3);
		} else {
			$themeurl = $boardurl . '/sachat/themes/default';
			$themedir = $boarddir . '/sachat/themes/default';
			$thjs = '';
			$load_btime = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_bstart)), 3);
		}

		//Load our language strings
		if (isset($lang) && is_file($themedir . '/languages/' . $lang . '.php'))
			$langfile = $themedir . '/languages/' . $lang . '.php';
		elseif (is_file($themedir . '/languages/english.php'))
			$langfile = $themedir . '/languages/english.php';
		else
			$langfile = $boarddir . '/sachat/themes/default/languages/english.php';
			
		require_once($langfile);
		
		if(is_file($themeurl.'/sounds'))
			$soundurl = $themeurl.'/sounds';
		else
			$soundurl = $boardurl . '/sachat/themes/default/sounds';
		
		if(file_exists($themedir . '/template.php'))
			require_once($themedir . '/template.php');
		else
			require_once($boarddir . '/sachat/themes/default/template.php');
		
		return array($themeurl, $themedir, $thjs, $load_btime, $soundurl, $curtheme, $txt);
	}
	
	function LoadLanguage($file){
		global $lang, $boarddir;
		
		if (isset($file) && isset($lang) && is_file($boarddir . '/sachat/Plugins/' .$file.'.'.$lang. '.php'))
			$langfile = $boarddir . '/sachat/Plugins/' .$file.'.'.$lang. '.php';
		elseif (is_file($boarddir . '/sachat/Plugins/' .$file.'.english.php'))
			$langfile = $boarddir . '/sachat/Plugins/' .$file.'.english.php';
		else
			logError('','','Unable to load plugin language file','DEBUG');
		
		if(!empty($langfile))
			require_once($langfile);
	}
	
	function LoadImage($icon){
		global $boardurl, $themeurl;
		
		if (is_file($themeurl . '/images/'.$icon)) {
			$pic = $themeurl . '/images/'.$icon;
		}else{
			$pic = $boardurl . '/sachat/themes/default/images/'.$icon;
		}
		
		return $pic;
	}
	
	function initchat($jsType) {
		global $boarddir, $filter_events, $context, $themedir;
		
		header('Content-Type: application/x-javascript; text/javascript');
		
		if (file_exists($themedir . '/js/' . $jsType . '.js.php'))
			require_once($themedir . '/js/' . $jsType . '.js.php');
		else
			require_once($boarddir . '/sachat/themes/default/js/' . $jsType . '.js.php');
		
		if($jsType == 'head')
			$context['HTML'] = ' ';
		
		if ($jsType == 'body'){
			
			if(isset($filter_events['hook_initialize']))//Plugins loading functions?.
				call_hook('hook_initialize', array(), false);
				
			initchatjs();	
			initCleanup();
		}
	}

	function initCleanup() {

		global $smcFunc, $filter_events, $modSettings;

		if(isset($filter_events['hook_cleanup']))//Plugins doing a cleanup.
			call_hook('hook_cleanup',array(),false);
			
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat
			WHERE {db_prefix}2sichat.rd != 0 AND {db_prefix}2sichat.sent < {int:purge}', 
			array(
				'purge' => date("Ymd", strtotime('-' . $modSettings['2sichat_purge'] . ' days', strtotime(date("Ymd")))),
			)
		);
	}
	
	function getBuddySession(){
		global $context;
		
		if(!empty($_SESSION['buddy_id'])){
			$buddy_settings = loadUserSettings($_SESSION['buddy_id']);
			$context['JSON']['buddySESSION'] = $buddy_settings['session'];	
		}
	}
	
	function loadDatabase() {

		global $db_persist, $db_connection, $db_server, $db_user, $db_passwd, $db_type, $db_name, $db_time, $db_count, $sourcedir, $db_prefix;
		
		$start = microtime(true);
		if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php')) {
			$db_type = 'mysql';
		}
		
		require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');

		if (empty($db_connection)) {
			$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('persist' => $db_persist, 'dont_select_db' => SMF == 'SSI'));
			//$db_count = !isset($db_count) ? 1 : $db_count + 1;
			$db_time = round(array_sum(explode(' ', microtime(true))) - array_sum(explode(' ', $start)), 3);
		}

		if (!$db_connection) {
			db_fatal_error();
		}
	}

	/**
	 * @param string $key
	 */
	function cachegetData($key, $ttl = 120) {
		global $boardurl, $modSettings, $boarddir;

		if (empty($modSettings['2sichat_cache']))
			return;

		$key = md5($boardurl . filemtime($boarddir . '/sachat/Sources/Subs.php')) . '-SACHAT-' . str_replace(':', '_', $key);
		$cachedir = $boarddir . '/sachat/cache';

		if (file_exists($cachedir . '/data_' . $key . '.php') && filesize($cachedir . '/data_' . $key . '.php') > 10) {
			require($cachedir . '/data_' . $key . '.php');
			if (!empty($expired) && isset($value)) {
				@unlink($cachedir . '/data_' . $key . '.php');
				unset($value);
			}
		}

		if (empty($value))
			return null;
		else
			return @unserialize($value);
	}

	/**
	 * @param string $key
	 */
	function cacheputData($key, $value, $ttl = 120) {
		global $boardurl, $modSettings, $boarddir;

		if (empty($modSettings['2sichat_cache']))
			return;

		$key = md5($boardurl . filemtime($boarddir . '/sachat/Sources/Subs.php')) . '-SACHAT-' . str_replace(':', '_', $key);
		$value = $value === null ? null : serialize($value);
		$cachedir = $boarddir . '/sachat/cache';

		if (file_exists($cachedir . '/data_' . $key . '.php') && $value === null)
			@unlink($cachedir . '/data_' . $key . '.php');
		else {

			//setup the php cache file and make it pretty ish :P
			$cache_data = '<' . '?' . 'php 
		if (' . (time() + $ttl) . ' < time()) 
			$expired = true; 
		else{
			$expired = false; 
			$value = \'' . addcslashes($value, '\\\'') . '\';
		}
	' . '?' . '>';

			$fh = @fopen($cachedir . '/data_' . $key . '.php', 'w');
			if ($fh) {
				set_file_buffer($fh, 0);
				flock($fh, LOCK_EX);
				$cache_bytes = fwrite($fh, $cache_data);
				flock($fh, LOCK_UN);
				fclose($fh);

				if ($cache_bytes != strlen($cache_data))
					@unlink($cachedir . '/data_' . $key . '.php');
			}
		}
	}

	function loadOpt() {
		global $member_id, $boarddir, $context;

		if (!file_exists($boarddir . '/sachat/cache'))
			mkdir($boarddir . '/sachat/cache', 0755);
			
		if (file_exists($boarddir . '/sachat/cache/db_exp_uid' . $member_id . '.txt'))
			$DBcon_exp = unserialize(file_get_contents($boarddir . '/sachat/cache/db_exp_uid' . $member_id . '.txt'));
			
		// If last connection to the DB is greater than the DB expire stamp die.
		// If no expire stamp die, usually means no messages availible anyways.
		if (isset($_SESSION['DBcon_stamp']) && isset($DBcon_exp) && $_SESSION['DBcon_stamp'] > $DBcon_exp || isset($_SESSION['DBcon_stamp']) && !isset($DBcon_exp)) {
			if (!isset($context['chat_action']) && !isset($_REQUEST['chat_user_search']) && !isset($_REQUEST['home']) && !isset($_REQUEST['msg']) && !isset($_REQUEST['update']) && !isset($_REQUEST['gid']) && !isset($_REQUEST['cid']) && !isset($_REQUEST['gcid']) && !isset($_REQUEST['action'])) {
				$context['JSON']['STATUS'] = 'IDLE';
				doOutput();
			}
		}
	}

	function doOutput() {

		global $context;
			
		if (isset($context['HTML'])) {
			echo $context['HTML'];
		} else if (isset($context['JSON']) && !isset($context['HTML'])) {
		   header('Content-type: application/json');
			echo doJSON($context['JSON']); 
		} else if (!isset($context['JSON']) && !isset($context['HTML'])) {
			$context['JSON']['STATUS'] = 'NO DATA';
			header('Content-type: application/json');
			echo doJSON($context['JSON']);
		}
		// Nothing after here.
		// Do we really need to go on?
		// Nope
		die();
	}
	
	function doOptDBexp() {

		global $buddy_id, $boarddir, $modSettings;

		// Add a timestamp when the DB connection will expire.
		$add = $modSettings['2sichat_cw_heart'] / 100;
		$expire = strtotime('+' . $add . ' seconds', strtotime(date("Ymdhis")));

		if (function_exists('file_put_contents')) {
			file_put_contents($boarddir . '/sachat/cache/db_exp_uid' . $buddy_id . '.txt', serialize($expire));
		} else {
			$cache = fopen($boarddir . '/sachat/cache/db_exp_uid' . $buddy_id . '.txt', 'w+');
			fwrite($cache, serialize($expire));
			fclose($cache);
		}
	}

	function doOptDBrec() {

		// Last recorded connection with the DB, excluding HBs
		$_SESSION['DBcon_stamp'] = strtotime(date("Ymdhis"));
	}

	function doLoadCHK() {

		global $modSettings, $context;

		if(function_exists ("sys_getloadavg")){
			$load = sys_getloadavg();
			if(!empty($modSettings['2sichat_max_load'])){
				if ($load[0] && $load[0] > $modSettings['2sichat_max_load']) {
					if (!empty($modSettings['2sichat_load_dis_chat'])) {
						$context['JSON']['STATUS'] = 'AWAY';
						doOutput();
					}
					if (!empty($modSettings['2sichat_load_dis_bar'])) {
						$modSettings['2sichat_dis_bar'] = 1;
					}
					if (!empty($modSettings['2sichat_load_dis_list'])) {
						$modSettings['2sichat_dis_list'] = 1;
					}
				}
			}
		}
	}
	
	function typestatus() {

		global $smcFunc, $modSettings, $member_id;

		if (!empty($_POST['typing']) && !empty($modSettings['2sichat_live_type'])) 
		{
			if ($member_id) 
			{
				if (empty($_POST['untype'])) 
				{
					$typing_insert = time();
				}
				else 
				{
					$typing_insert = '0';
				}
				
				$request = $smcFunc['db_query']('', '
					SELECT member_id
					FROM {db_prefix}2sichat_typestaus
					WHERE member_id = {int:member_id}',
					array(
						'member_id' => $member_id,
					)
				);

				$temp = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);
				
				if(empty($temp['member_id'])){
					$smcFunc['db_insert']('', '{db_prefix}2sichat_typestaus', 
						array(
							'member_id' => 'int',
							'status' => 'string',
						), 
						array($member_id, $typing_insert),
						array()
					);
				}else{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}2sichat_typestaus
						SET status = {string:one}
						WHERE member_id = {int:member_id}', 
						array(
							'member_id' => $member_id,
							'one' => $typing_insert,
						)
					);
				}
			}
		}
	}

	function CheckTyping(){
		
		global $smcFunc, $modSettings, $context, $txt;
		
		if(!empty($_SESSION['buddy_id']) && !empty($modSettings['2sichat_live_type'])){
			
			$request = $smcFunc['db_query']('', '
				SELECT status, member_id
				FROM {db_prefix}2sichat_typestaus
				WHERE member_id = {int:member_id}',
				array(
					'member_id' =>  $_SESSION['buddy_id'],
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				
				$timer = $row['status'] + 60;
							
				if ($row['status'] == "0" || time() > $timer ) 
				{
					// If the user is not typing
					$context['JSON']['userTyping'] = $row['member_id'];
					$context['JSON']['userTypingSay'] = null;

				} 
				else 
				{
					// If the user is typing
					$context['JSON']['userTyping'] = $row['member_id'];
					$context['JSON']['userTypingSay'] = ' '.$txt['bar_isTyping'];
				}
			}
			$smcFunc['db_free_result']($request);
		}
	}
	
	function CheckActive(){
		
		global $smcFunc, $member_id, $modSettings, $txt, $context;
		
		if(!empty($_SESSION['buddy_id']) && !empty($modSettings['2sichat_e_last3min'])){
		
			$results = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat
				WHERE {db_prefix}2sichat.from = {int:member_id}
				ORDER BY id DESC
				LIMIT 1', 
				array( 
					'member_id' => $_SESSION['buddy_id'],
				)
			);

			if ($results && $smcFunc['db_num_rows']($results) != 0) {
				while ($row = $smcFunc['db_fetch_assoc']($results)) {
				
					$now = time()-strtotime($row['sent']);
					$row['sent'] = date('g:iA M dS', strtotime($row['sent']));
					$message = $txt['bar_sent_at'].' '.$row['sent'];
					$timeout = !empty($modSettings['2sichat_e_last3minv']) ? $modSettings['2sichat_e_last3minv'] : 180;
					if ($now > $timeout) {

						$context['JSON']['SENTMSGTIME'] = $message;
						$context['JSON']['SENTMSGID'] = $row['id'];
					
						$smcFunc['db_query']('', '
							UPDATE {db_prefix}2sichat
							SET {db_prefix}2sichat.inactive = {int:one}
							WHERE {db_prefix}2sichat.id = {int:idmsg} AND {db_prefix}2sichat.from = {int:from} AND {db_prefix}2sichat.to = {int:member_id}', 
							array(
								'from' => $_SESSION['buddy_id'],
								'member_id' => $member_id,
								'one' => 1,
								'idmsg' => $row['id'],
							)
						);
					}
				}
			}
			$smcFunc['db_free_result']($results);
		}
	}
	
	function doJSON($data) {

		if (function_exists('json_encode')) {
			return json_encode($data);
		}

		if (is_null($data))
			return 'null';
		if ($data === false)
			return 'false';
		if ($data === true)
			return 'true';
		if (is_scalar($data)) {
			if (is_float($data)) {
				return floatval(str_replace(",", ".", strval($data)));
			}

			if (is_string($data)) {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $data) . '"';
			} else {
				return $data;
			}
		}
		$isList = true;
		for ($i = 0, reset($data); $i < count($data); $i++, next($data)) {
			if (key($data) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($data as $v) {
				$result[] = doJSON($v);
			}
			return '[' . join(',', $result) . ']';
		} else {
			foreach ($data as $k => $v) {
				$result[] = doJSON($k) . ':' . doJSON($v);
			}
			return '{' . join(',', $result) . '}';
		}
	}

?>