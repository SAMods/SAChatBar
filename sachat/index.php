<?php
/*
	This is the chat backend. It should only load chat junk.
	It should be allot better than loading all that other junk.
	
	functions performed
		- Load the database
		- Verify user against cookie
		- Load all user information needed
		- Display output for the chat
		
	Copyright 2010 SI Community.

*/
	define('SMF', 1);

	// Experimental Optimizer
	define('loadOpt', 1);
	
    session_start();
	session_cache_limiter('nocache');

	// Lets go head and load the settings here.
	require_once(str_replace('//','/',dirname(__FILE__).'/').'../Settings.php');

	// Load SMF's compatibility file for unsupported functions.
	if (@version_compare(PHP_VERSION, '5') == -1) {
		require_once($sourcedir . '/Subs-Compat.php');
	}
	
	// Load the theme
	if (isset($_REQUEST['theme']) && !strstr('..', $_REQUEST['theme']) && is_file('./themes/'.$_REQUEST['theme'].'/template.php') && is_file('./themes/'.$_REQUEST['theme'].'/style.css')) {
		$themeurl = $boardurl.'/sachat/themes/'.$_REQUEST['theme'];
		$themedir = $boarddir.'/sachat/themes/'.$_REQUEST['theme'];
		$thjs = 'theme='.$_REQUEST['theme'].'&';
		require_once($themedir.'/template.php');
	} 
	/*elseif(empty($_GET['gid'])) {
		$themeurl = $boardurl.'/sachat/themes/defualt';
		$themedir = $boarddir.'/sachat/themes/default';
		require_once($themedir.'/template.php');
	}*/
	else{
	    $themeurl = $boardurl.'/sachat/themes/default';
		$themedir = $boarddir.'/sachat/themes/default';
		require_once($themedir.'/template.php');
	}
	
     // Load language
     if (isset($language) && is_file($themedir.'/languages/'.$language.'.php')) {
		require_once ($themedir.'/languages/'.$language.'.php');
	} else if (is_file($themedir.'/languages/english.php')){
		require_once ($themedir.'/languages/english.php');
	} else {
		require_once ($boarddir.'/sachat/themes/default/languages/english.php');
	}
	
	// SMF Cookie autentication!!!
	if (isset($_COOKIE[$cookiename]) && preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$cookiename]) === 1) {
		$data = @unserialize($_COOKIE[$cookiename]);
	} else if (isset($_COOKIE[$cookiename])) {
		$data = @unserialize(stripslashes($_COOKIE[$cookiename]));
	}
	
	if(!empty($data)){
	    $member_id = $data[0];
	    $password = $data[1];
	}

	// Load Optimizer if applicable
	if (defined('loadOpt')){loadOpt();}

	// Connect to the database
	$smcFunc = array();
	loadDatabase();

	if (!empty($db_character_set)) {
		$smcFunc['db_query']('set_character_set', 'SET NAMES ' . $db_character_set,array());
	}	
	
	if($member_id){
	$results = $smcFunc['db_query']('', '
		SELECT variable, value
		FROM {db_prefix}themes
		WHERE id_member = {string:mem}',
		array(
		    'mem' => $member_id,
		)
	);
	$options = array();
	while ($row = $smcFunc['db_fetch_row']($results)) {
		$options[$row[0]] = $row[1];
	}
	$smcFunc['db_free_result']($results);
	}
	
	$results = $smcFunc['db_query']('', '
		SELECT variable, value
		FROM {db_prefix}settings',
		array()
	);
	$modSettings = array();
	while ($row = $smcFunc['db_fetch_row']($results)) {
		$modSettings[$row[0]] = $row[1];
	}
	$smcFunc['db_free_result']($results);

	if (!empty($modSettings['2sichat_disable'])) {
		die();
	}
	if (!empty($modSettings['2sichat_load_chk'])) {
		doLoadCHK();
	}
	
	
	// If it is a member lets load some data.
	if ($member_id != 0){
		
		$OnCount = genMemcount();
		
          $user_settings = loadUserSettings($member_id);
		if (!empty($modSettings['2sichat_permissions'])) {
			$permission = loadPermissions($user_settings['groups']);
		}
		// Load $buddy_settigns if we are chatting.
		if (isset($_REQUEST['cid']) || isset($_REQUEST['update'])) {
			if (isset($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) {
				$buddy_id = $_REQUEST['cid'];
			} else if (is_numeric($_REQUEST['update'])){
				$buddy_id = $_REQUEST['update'];
			} else {
				die(); // Something fishy about a non numeric buddy id.
			}
			if ($buddy_id) {
				$buddy_settings = loadUserSettings($buddy_id);
			}
		 }
	} else if (!empty($modSettings['2sichat_permissions'])) {
     	$permission = loadPermissions(-1); // -1 is guest.
	}
	
	//Lets see if 2-SI Chat is enabled for this group.
	if (!empty($modSettings['2sichat_permissions']) && !$permission['2sichat_access'] && !$permission['is_admin'] && !$permission['is_mod']) {
		$context['JSON']['STATUS'] = 'NO ACCESS'; // Sorry but you don't have access
		doOutput();
	} else if (!empty($modSettings['2sichat_permissions']) && !$permission['is_admin'] && !$permission['is_mod']) {
		// Lets just hook into the modSettings
		if (!$permission['2sichat_chat']) {
			$modSettings['2sichat_dis_list'] = 1;
			$modSettings['2sichat_dis_chat'] = 1;
		}
		if ($permission['2sichat_bar']) {
			$modSettings['2sichat_dis_bar'] = 1;
		}
	}
	// Lets validate the password, anyone can put a number in a cookie, lets see if the password checks out.
	if (isset($user_settings) && strlen($password) != 40 || isset($user_settings) && sha1($user_settings['passwd'].$user_settings['password_salt']) != $password) {
		$context['JSON']['STATUS'] = 'AUTH FAILED';
		doOutput();
	} else {
		$context['JSON']['STATUS'] = 'ACTIVE';
	}
	
	// Check actions
	if (isset($_REQUEST['action'])) {
	
		// If we are loading the main javascript lets get it ready based on the user
     	if ($_REQUEST['action'] == 'body') {
			initJs('body');
			initLink();
			initGadgets();
			initchat();
			initCleanup();
		}
		if ($_REQUEST['action'] == 'heart' && $member_id) {
			doheart();
		}
		if ($_REQUEST['action'] == 'head') {
			initJs('head');
			$context['HTML'] = ' ';
		}
	} else {
		//No action defined so lets assume they are using the chat.
		if(!isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id) {
			// Must be starting out.
			initChatSess();
		} else if (isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id) {
			savemsg();
		} else if (isset($_REQUEST['update']) && $member_id) {
               retmsgs();
		} else if (isset($_REQUEST['gid'])) {
			gadget();
		}
 	}
 	if ($member_id && isset($_REQUEST['action']) && $_REQUEST['action'] == 'heart' && !empty($modSettings['2sichat_live_online']) || $member_id && !isset($_REQUEST['action']) && !empty($modSettings['2sichat_live_online'])) {
		liveOnline();
	}
	// If output function hasn't been declared lets do it.
	doOutput();
	
function initJs($jsType){
    global $themedir;
    
	if(file_exists ($themedir.'/'.$jsType.'.js.php')){
	    require_once($themedir.'/'.$jsType.'.js.php');
	}
	else{
	    require_once($jsType.'.js.php');
    }
}

function initLink(){

	global $smcFunc, $context, $member_id;

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_barlinks
		ORDER BY ord',
		array()
	);

	if ($results){
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			if ($row['vis'] != 0 ) {
				if ($row['vis'] == 1 && $member_id || $row['vis'] == 2 && !$member_id || $row['vis'] == 3) {
					$context['gadgetslink'][] = $row;
				}
			}
		}
		$smcFunc['db_free_result']($results);
	}
}
function initGadgets(){

	global $smcFunc, $context, $member_id;

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_gadgets
		ORDER BY ord',
		array()
	);

	if ($results){
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			if ($row['vis'] != 0 ) {
				if ($row['vis'] == 1 && $member_id || $row['vis'] == 2 && !$member_id || $row['vis'] == 3) {
					$context['gadgets'][] = $row;
				}
			}
		}
		$smcFunc['db_free_result']($results);
	}
}

function gadget(){

	global $smcFunc, $sourcedir, $modSettings, $boarddir, $context;
	
	if(empty($modSettings['2sichat_theme']))
	    $modSettings['2sichat_theme'] = 'default';
		
  //  require_once($boarddir.'/sachat/themes/'.$modSettings['2sichat_theme'].'/template.php');
	require_once($sourcedir.'/Subs.php');
	
	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_gadgets
		WHERE {db_prefix}2sichat_gadgets.id = {int:gid}
		LIMIT 1',
		array(
			'gid' => $_REQUEST['gid'],
		)
	);

	$context['gadget'] = $smcFunc['db_fetch_assoc']($results);
     $smcFunc['db_free_result']($results);
	 
	//type = php
	if($context['gadget']['type'] == 0){
	    $context['gadget']['url'] = trim($context['gadget']['url']);
		$context['gadget']['url'] = trim($context['gadget']['url'], '<?php');
		$context['gadget']['url'] = trim($context['gadget']['url'], '?>');
        ob_start();
	    $context['gadget']['url'] = eval($context['gadget']['url']);
		$context['gadget']['url'] = ob_get_contents();
        ob_end_clean();
	}
	//type = html
	if($context['gadget']['type'] == 1){
	    $context['gadget']['url'] = $context['gadget']['url'];
	}
	//type = bbc
	if($context['gadget']['type'] == 2){
	    $context['gadget']['url'] = parse_bbc($context['gadget']['url']);
	}

	
    if (isset($_REQUEST['src']) && $_REQUEST['src'] == 'true') {
		$context['HTML'] = gadgetObject_template();
	} else {
		$context['JSON']['DATA'] = gadget_template();
		$context['JSON']['GID'] = $context['gadget']['id'];
	}

}

function initChatSess(){
	
	global $smcFunc, $member_id, $buddy_id, $context;

	// Now on to the messages
	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE ({db_prefix}2sichat.to = {int:buddy_id} AND {db_prefix}2sichat.from = {int:member_id}) OR ({db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id})
		ORDER BY id DESC',
		array(
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
		)
	);

	$lastID = 0;

	if ($results){
		mread(); // Mark messages read since we are displaying them.
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$row['msg'] = phaseMSG($row['msg']);
          	$context['msgs'][] = $row;
          	$lastID = $row['id'];
		}
		$smcFunc['db_free_result']($results);
	}
	$context['JSON']['DATA'] = chat_window_template();
	$context['JSON']['BID'] = $buddy_id;	
	$context['JSON']['ID'] = $lastID;
}

function retmsgs(){

	global $smcFunc, $member_id, $buddy_id, $context, $modSettings;

	$reqTime = $_REQUEST['_'] - $modSettings['2sichat_cw_heart'];

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE ({db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd = 0) OR ({db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd > {float:reqTime})
		ORDER BY id DESC',
		array(
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
			'reqTime' => $reqTime,
		)
	);

	$lastID = 0;
	
	if ($results){
		mread(); // Mark messages read since we are displaying them.
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$row['msg'] = phaseMSG($row['msg']);
         		$context['msgs'][] = $row;
         		$lastID = $row['id'];
		}
		$smcFunc['db_free_result']($results);
		$context['JSON']['DATA'] = chat_retmsg_template();
		// Muahhahaha, now finally get rid of that stupid session junk. We got JSON.
		$context['JSON']['ID'] = $lastID;
	}
}

function savemsg(){

	global $smcFunc, $member_id, $user_settings, $context, $buddy_id, $buddy_settings, $modSettings;

	// SMF ignore list, excluding group 1 & 2, which is the admin and global mod, they get to talk to who ever, ignore or not :P
	if (!in_array(1, $user_settings['groups']) && !in_array(2, $user_settings['groups'])) {
		if (strpos($buddy_settings['pm_ignore_list'], ',') && in_array($member_id, explode(',', $buddy_settings['pm_ignore_list'])) || !strpos($buddy_settings['pm_ignore_list'], ',') && $member_id == $buddy_settings['pm_ignore_list']) {
			$context['JSON']['STATUS'] = 'IGNORE';
			doOutput();
		}
	}

	// See if they have permission, maybe one day will have a message sent back.
	if (!empty($modSettings['2sichat_dis_chat'])) {
		$context['JSON']['STATUS'] = 'NO CHAT ACCESS';
		doOutput();
	}
	
     if (str_replace(' ', '', $_REQUEST['msg']) != '') {
		$smcFunc['db_insert']('',
			'{db_prefix}2sichat',
			array('to' => 'int', 'from' => 'int', 'msg' => 'string', 'sent' => 'string'),
			array($buddy_id, $member_id, htmlspecialchars(stripslashes($_REQUEST['msg']), ENT_QUOTES), date("YmdHis")),
			array()
		);

		$context['msgs'] = phaseMSG(htmlspecialchars(stripslashes($_REQUEST['msg']), ENT_QUOTES));

          if (defined('loadOpt')){
			doOptDBexp();
		}
		$context['JSON']['DATA'] = chat_savemsg_template();
	}
}

function mread(){

	global $smcFunc, $member_id, $buddy_id;

	if (isset($_REQUEST['_'])) {
		$read = $_REQUEST['_'];
	}
	if (!isset($read)) {
		$read = 1;
	}

	// Mark messages read.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}2sichat
		SET {db_prefix}2sichat.rd = {float:read}
		WHERE {db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd = 0',
		array (
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
			'read' => $read,
		)
	);
	if (defined('loadOpt')){
		doOptDBrec();
	}
}

function initCleanup(){

	global $smcFunc, $modSettings;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}2sichat
		WHERE {db_prefix}2sichat.rd != 0 AND {db_prefix}2sichat.sent < {int:purge}',
		array(
			'purge' => date("Ymd", strtotime('-'.$modSettings['2sichat_purge'].' days', strtotime(date("Ymd")))),
		)
	);
}

function doheart() {

    	global $smcFunc, $member_id, $context;

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE {db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.rd = 0',
		array(
			'member_id' => $member_id,
		)
	);

	if ($results && $smcFunc['db_num_rows']($results) != 0){
		$context['JSON']['ids'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			if (!in_array($row['from'], $context['JSON']['ids'])) {
				$context['JSON']['ids'][] = $row['from'];
			}
		}
		$smcFunc['db_free_result']($results);
	} else {
		if (defined('loadOpt')){
			doOptDBrec();
		}
		$context['JSON']['STATUS'] = 'NO RESULTS';
	}
}

function load_smiles(){

    	global $smcFunc, $modSettings;

	$smiles = array();
	$results = $smcFunc['db_query']('', '
		SELECT code, filename
		FROM {db_prefix}smileys',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($results)){
		$smiles['code'][] = htmlspecialchars($row['code'], ENT_QUOTES);
		$smiles['file'][] = '<img src="'.$modSettings['smileys_url'].'/'.$modSettings['smiley_sets_default'].'/'.$row['filename'].'">';
	}
	$smcFunc['db_free_result']($results);

	return $smiles;
}

function phaseMSG($data){

	global $modSettings;
	if (!empty($modSettings['2sichat_simple_bbc'])) {
		$data = phaseBBC($data);
		$data = preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $data);
	} else {
		$data = preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", $data);
	}

	// Load up the smileys
	$smiles = load_smiles();
	$data = str_replace($smiles['code'], $smiles['file'], $data);

	return $data;
}
function phaseBBC($data){

	// If no BBC just return the data, don't do anything extra
	if ((strpos($data, "[") === false || strpos($data, "]") === false)) {
		return $data;
	}

	$codes = array (
		'/(\[[b]\])(.+)(\[\/[b]\])/',				// Bold
		'/(\[[u]\])(.+)(\[\/[u]\])/',				// Image
		'/(\[[i]\])(.+)(\[\/[i]\])/',				// Italic
		'/(\[color=)(.+)(\])(.+)(\[\/color\])/',	// Color
		'/(\[url\]http)(.+)(\[\/url\])/',			// Url no description, http
		'/(\[url\])(.+)(\[\/url\])/',				// Url no description, no http
		'/(\[url=http)(.+)(\])(.+)(\[\/url\])/',	// Url description, http
		'/(\[url=)(.+)(\])(.+)(\[\/url\])/',		// Url description, no http
//		'/(\[img\]http)(.+)(\[\/img\])/',			// Image, http
//		'/(\[img\])(.+)(\[\/img\])/'				// Image, no http
	);
	$html = array (
		'<b>\\2</b>',
		'<u>\\2</u>',
		'<i>\\2</i>',
		'<span style="color:\\2">\\4</span>',
		'<a href="\\2">\\2</a>',
		'<a href="http://\\2">\\2</a>',
		'<a href="\\2">\\4</a>',
		'<a href="http://\\2">\\4</a>',
//		'<img src="\\2" />',
//		'<img src="http://\\2" />'
	);
	$data = preg_replace($codes, $html, $data);
	return $data;
}

function loadUserSettings($id) {

	global $smcFunc, $modSettings, $themeurl, $boardurl;

	// Load $user_settings
	$results = $smcFunc['db_query']('', '
		SELECT m.*, o.session, IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
		FROM {db_prefix}members AS m
		LEFT JOIN {db_prefix}log_online AS o ON o.id_member = m.id_member
		LEFT JOIN {db_prefix}attachments AS a ON a.id_member = {int:member_id}
		WHERE m.id_member = {int:member_id}
		LIMIT 1',
		array(
			'member_id' => $id,
		)
	);
	$temp = $smcFunc['db_fetch_assoc']($results);
	$smcFunc['db_free_result']($results);

	//Lets do some fusion, Fuussiioooon Ahhhhh!!!!!
	if ($temp['additional_groups']) {
		$temp['groups'] = array_merge(array($temp['id_group'], $temp['id_post_group']),explode(',', $temp['additional_groups']));
	} else {
		$temp['groups'] = array($temp['id_group'], $temp['id_post_group']);
	}

	// Fix avatar
	if (!$temp['avatar'] && $temp['id_attach']) {
		if ($temp['attachment_type'] == 0) {
			$temp['avatar'] = $boardurl.'/index.php?action=dlattach;attach='.$temp['id_attach'].';type=avatar';
		}
		if ($temp['attachment_type'] == 1) {
			$temp['avatar'] = (isset($modSettings['custom_avatar_enabled'])?$modSettings['custom_avatar_url']:$modSettings['avatar_url']).'/'.$temp['filename'];
		}
	} else if (!$temp['avatar']) {
		$temp['avatar'] = $themeurl.'/images/blankuser.png';
	}

	if (substr($temp['avatar'], 0, 4) != 'http') {
		$temp['avatar'] = $modSettings['avatar_url'].'/'.$temp['avatar'];
	} // Allot of junk to fix one avatar, SMF needs to get this right.
	return $temp;
}

function loadPermissions($data) {
	
	global $smcFunc, $context;
	
	$temp = array();
	if (is_array($data)) {
		if (in_array(1, $data)) {
			$temp['is_admin'] = 1;
		} else if (in_array(2, $data)) {
			$temp['is_mod'] = 1;
		}
		$data = implode(',',$data);
	} else {
		if ($data == 1) {
          	$temp['is_admin'] = 1;
		} else if ($data == 2) {
			$temp['is_mod'] = 1;
		}
	}

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}permissions
		WHERE FIND_IN_SET(id_group, {string:groups})',
		array(
			'groups' => $data,
		)
	);

	while ($row = $smcFunc['db_fetch_row']($results)) {
		$temp[$row[1]] = $row[2];
	}
	$smcFunc['db_free_result']($results);
	return $temp;
}

function liveOnline() {
	
	global $modSettings, $options, $context;

	if (empty($modSettings['2sichat_dis_list']))
		$context['JSON']['ONLINE'] = genMemList();
}

function genMemList() {

	global $options, $smcFunc, $user_settings, $member_id, $context;

	if (!empty($options['show_cbar_buddys']) && $options['show_cbar_buddys'] == 1){
	    $query = 'WHERE FIND_IN_SET({int:member_id}, m.buddy_list) AND t2.value = 1';
	}else{
	    $query = 'WHERE NOT FIND_IN_SET({int:member_id}, m.pm_ignore_list) AND t2.value != 1 AND m.show_online = 1 OR FIND_IN_SET({int:member_id}, m.buddy_list) AND m.show_online = 0';
    }
	
	$results = $smcFunc['db_query']('', '
		SELECT m.buddy_list, m.id_member, m.member_name, m.real_name, t1.value AS show_cbar, t2.value AS show_cbar_buddys, o.session
		FROM {db_prefix}members AS m
		LEFT JOIN {db_prefix}log_online AS o ON o.id_member = m.id_member
		LEFT JOIN {db_prefix}themes AS t1 ON (t1.variable = {string:name1} AND t1.id_member = m.id_member)
		LEFT JOIN {db_prefix}themes AS t2 ON (t2.variable = {string:name2} AND t2.id_member = m.id_member)
		'.$query.'
		ORDER BY m.real_name DESC',
		array(
			'member_id' => $member_id,
			'name1' => 'show_cbar',
			'name2' => 'show_cbar_buddys',
		)
	);
    
	$buddies = explode(',', $user_settings['buddy_list']);
	
	if ($results){
	
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			
			if (in_array($row['id_member'], $buddies) || $row['show_cbar_buddys'] == 1) {
				
				if ($row['show_cbar'] == 1)
				    continue;
	          	    
			    $context['friends'][] = $row;
			}
		    elseif (isset($row['session']) && $row['id_member'] != $member_id){
				    
				if ($row['show_cbar'] == 1)
				    continue;
	          	    
				$context['friends'][] = $row;
			}
		}
		$smcFunc['db_free_result']($results);
	}
	$data = buddy_list_template();
	return $data;
}

function genMemcount() {

	global $smcFunc, $puppys, $member_id, $options, $context;
	
	if (!empty($options['show_cbar_buddys']) && $options['show_cbar_buddys'] == 1)
	    $query = 'WHERE FIND_IN_SET({int:member_id}, m.buddy_list) AND o.id_member != {int:member_id} AND t1.value != 1 AND t2.value = 1 AND m.show_online = 1';
	else
	    $query = 'WHERE NOT FIND_IN_SET({int:member_id}, m.buddy_list) AND o.id_member != {int:member_id} AND t1.value != 1 AND t2.value != 1 AND m.show_online = 1';

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
	    FROM {db_prefix}log_online AS o
		LEFT JOIN {db_prefix}members AS m ON m.id_member = o.id_member
		LEFT JOIN {db_prefix}themes AS t1 ON (t1.variable = {string:name1} AND t1.id_member = m.id_member)
		LEFT JOIN {db_prefix}themes AS t2 ON (t2.variable = {string:name2} AND t2.id_member = m.id_member)
		'.$query.'',
		array(
			'member_id' => $member_id,
			'name1' => 'show_cbar',
			'name2' => 'show_cbar_buddys',
				
		)
	);

	list ($puppys) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	
	return $puppys;
}

function loadDatabase(){

	global $db_persist, $db_connection, $db_server, $db_user, $db_passwd, $db_type, $db_name, $sourcedir, $db_prefix;

     if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php')) {
		$db_type = 'mysql';
	}

	require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');

	if (empty($db_connection)) {
		$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('persist' => $db_persist, 'dont_select_db' => SMF == 'SSI'));
	}

	if (!$db_connection) {
		db_fatal_error();
	}
}

function loadOpt() {

	global $member_id, $boarddir, $context;

	if(!file_exists($boarddir.'/sachat/cache')){mkdir($boarddir.'/sachat/cache', 0755);}
	if(file_exists($boarddir.'/sachat/cache/db_exp_uid'.$member_id.'.txt')){
		$DBcon_exp = unserialize(file_get_contents($boarddir.'/sachat/cache/db_exp_uid'.$member_id.'.txt'));
	}

	// If last connection to the DB is greater than the DB expire stamp die.
	// If no expire stamp die, usually means no messages availible anyways.
	if (isset($_SESSION['DBcon_stamp']) && isset($DBcon_exp) && $_SESSION['DBcon_stamp'] > $DBcon_exp || isset($_SESSION['DBcon_stamp']) && !isset($DBcon_exp)) {
		if (!isset($_REQUEST['msg']) && !isset($_REQUEST['gid']) && !isset($_REQUEST['cid']) && @$_REQUEST['action'] != 'head' && @$_REQUEST['action'] != 'body') {
			$context['JSON']['STATUS'] = 'IDLE';
			doOutput();
		}
	}
}

function doOptDBexp() {

	global $buddy_id, $boarddir, $modSettings;

	// Add a timestamp when the DB connection will expire.
	$add = $modSettings['2sichat_cw_heart'] / 100;
	$expire = strtotime('+'.$add.' seconds', strtotime(date("Ymdhis")));

	if (function_exists('file_put_contents')) {
	     file_put_contents($boarddir.'/sachat/cache/db_exp_uid'.$buddy_id.'.txt', serialize($expire));
	} else {
		$cache = fopen($boarddir.'/sachat/cache/db_exp_uid'.$buddy_id.'.txt','w+');
		fwrite($cache,serialize($expire));
		fclose($cache);
	}	
}

function doOptDBrec() {

	// Last recorded connection with the DB, excluding HBs
	$_SESSION['DBcon_stamp'] = strtotime(date("Ymdhis"));
}

function doLoadCHK() {

	global $modSettings, $themeurl, $txt, $context;

	if (function_exists('sys_getloadavg')) {
		$cpu = sys_getloadavg();
	} else if (!function_exists('sys_getloadavg') && !stristr(php_os, 'WIN')) {
		if (file_exists('/proc/loadavg')) {
			$cpu = explode(chr(32),file_get_contents('/proc/loadavg'));
		} else {
			$cpu = array_map("trim",explode(",",substr(strrchr(shell_exec("uptime"),":"),1)));
		}
	} else if (stristr(php_os, 'WIN')) {
		ob_start();
		passthru('typeperf -sc 1 "\processor(_total)\% processor time"',$status);
		$content = ob_get_contents();
		ob_end_clean();
		if ($status === 0) {
			if (preg_match("/\,\"([0-9]+\.[0-9]+)\"/",$content,$load)) {
				$cpu[0] = $load[1];
				$cpu[1] = $load[1];
				$cpu[2] = $load[1];
			}
		}
	}

	if ($cpu[0] && $cpu[0] > $modSettings['2sichat_max_load'] || $cpu[1] && $cpu[1] > $modSettings['2sichat_max_load'] || $cpu[2] && $cpu[2] > $modSettings['2sichat_max_load']) {
     	if ($modSettings['2sichat_load_dis_chat']) {
			$context['JSON']['STATUS'] = 'AWAY';
			doOutput();
		}
     	if ($modSettings['2sichat_load_dis_bar']) {$modSettings['2sichat_dis_bar'] = 1;}
     	if ($modSettings['2sichat_load_dis_list']) {$modSettings['2sichat_dis_list'] = 1;}
	}
}

function doJSON($data) {

	if(function_exists('json_encode')) {
		return json_encode($data);
	}

	if (is_null($data)) return 'null';
	if ($data === false) return 'false';
	if ($data === true) return 'true';
	if (is_scalar($data)) {
		if (is_float($data)) {
			return floatval(str_replace(",", ".", strval($data)));
		}

		if (is_string($data)) {
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"'.str_replace($jsonReplaces[0], $jsonReplaces[1], $data).'"';
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
		return '['.join(',', $result).']';
	} else {
		foreach ($data as $k => $v) {
			$result[] = doJSON($k).':'.doJSON($v);
		}
		return '{'.join(',', $result).'}';
	}
}

function doOutput() {

	global $context;

	if (isset($context['HTML'])) {
		echo $context['HTML'];
	} else if (isset($context['JSON']) && !isset($context['HTML'])) {
		header('Content-type: application/json');
		echo doJSON($context['JSON']); // PHP "Here's my array."
		// JavaScript "Well thank you PHP, I can use this data in here."
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
?>