<?php
function doCharset($db_character_set){
    global $smcFunc;
	
    if (!empty($db_character_set)) {
	    $smcFunc['db_query']('set_character_set', 
	        'SET NAMES ' . $db_character_set,
		    array()
	    );
	}	
}	
function initModSettings(){
    global $smcFunc, $modSettings;
	
	if (($modSettings = cachegetData('modSettings', 90)) == null)
	{
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
        cacheputData('modSettings', $modSettings, 90); 
	}
	return $modSettings;
}

function initCookies(){
    global $cookiename;
	
    if (isset($_COOKIE[$cookiename]) && preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~i', $_COOKIE[$cookiename]) == 1)
	{
		list ($member_id, $password) = @unserialize($_COOKIE[$cookiename]);
		$member_id = !empty($member_id) && strlen($password) > 0 ? (int) $member_id : 0;
		
		return array($member_id, $password);
	}
	elseif (isset($_COOKIE[$cookiename]))
	{
		list ($member_id, $password) = @unserialize(stripslashes($_COOKIE[$cookiename]));
		$member_id = !empty($member_id) && strlen($password) > 0 ? (int) $member_id : 0;
		
		return array($member_id, $password);
	}
}

function initTheme(){
    global $boardurl, $time_start, $boarddir;
	
    if (isset($_REQUEST['theme']) && !strstr('..', $_REQUEST['theme']) && is_file('./themes/'.$_REQUEST['theme'].'/template.php') && is_file('./themes/'.$_REQUEST['theme'].'/style.css')) {
		$themeurl = $boardurl.'/sachat/themes/'.$_REQUEST['theme'];
		$themedir = $boarddir.'/sachat/themes/'.$_REQUEST['theme'];
		$thjs = 'theme='.$_REQUEST['theme'].'&';
		$load_time = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 3);
	} 
	else{
	    $themeurl = $boardurl.'/sachat/themes/default';
		$themedir = $boarddir.'/sachat/themes/default';
		$thjs = '';
		$load_time = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 3);
	}
	
	return array($themeurl, $themedir, $thjs, $load_time);
}

function initLang($lang){
    global $boarddir, $themedir;
    
	if (isset($lang) && is_file($themedir.'/languages/'.$lang.'.php')) {
		$langfile = $themedir.'/languages/'.$lang.'.php';
	} 
	elseif (is_file($themedir.'/languages/english.php')){
		$langfile = $themedir.'/languages/english.php';
	} 
	else {
		$langfile = $boarddir.'/sachat/themes/default/languages/english.php';
	}
	
	return $langfile;
}
	
function initJs($jsType){
    global $themedir;
    
	if(file_exists ($themedir.'/js/'.$jsType.'.js.php')){
	    require_once($themedir.'/js/'.$jsType.'.js.php');
	}
	else{
	    require_once('js/'.$jsType.'.js.php');
    }
}

function initLink(){

	global $smcFunc, $context, $member_id;

    if (($context['gadgetslink'] = cachegetData('gadgetslink', 3600)) === null)
	{
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
	
	        cacheputData('gadgetslink',  $context['gadgetslink'], 3600);
	    }
	}
}
function initGadgets(){

	global $smcFunc, $context, $member_id;

	if (($context['gadgets'] = cachegetData('gadgets', 3600)) === null)
	{
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
		    cacheputData('gadgets',  $context['gadgets'], 3600);
	    }
	}
}

function gadget(){

	global $smcFunc, $sourcedir, $modSettings, $boarddir, $context;
	
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

function censorMSG($data){
	
	$replace = '';
	$rop = '*';
    
	for ($i=1;$i<strlen($data);$i++){ 
        $replace .= $rop; 
    } 
	
    $data = substr_replace($data,  $replace,  1); 
    return $data; 
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
    
	if(!empty($modSettings['2sichat_censor_words']) && !empty($modSettings['2sichat_censor'])){
	    $badwords = explode('|', $modSettings['2sichat_censor_words']);
	
	    for ($i=0; $i<count($badwords); $i++) { 
            $data = str_replace($badwords[$i],  censorMSG($badwords[$i]),  $data); 
        } 
	}
	
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
    
	$temp['real_name'] = utf8_encode($temp['real_name']);

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

function genMemList($type='list') {

	global $smcFunc, $member_id, $user_settings, $context;

	$user_settings = loadUserSettings($member_id);
	
    $results = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members',
		array());
	
	while ($row = $smcFunc['db_fetch_assoc']($results)) {
		
		$context['friendsFetch'][$row['id_member']] = loadUserSettings($row['id_member']);	
		
		if(!isset($context['friendsFetch'][$row['id_member']]['session']) || $context['friendsFetch'][$row['id_member']]['id_member'] == $user_settings['id_member'])
            continue;
			
		$request = $smcFunc['db_query']('', '
			SELECT id_member, variable, value
            FROM {db_prefix}themes
            WHERE id_member IN ({array_int:members}) OR id_member = ({int:member})
            AND variable IN ({array_string:opt})',
		    array(
		        'members' => array_keys($context['friendsFetch']),
			    'member' => $user_settings['id_member'],
			    'opt' =>  array('show_cbar', 'show_cbar_buddys'),
			)
		);
		
		while ($row1 = $smcFunc['db_fetch_assoc']($request))
		    $context['friendsFetch'][$row1['id_member']][$row1['variable']] = $row1['value'];
	    $smcFunc['db_free_result']($request);
		
        if(!empty($context['friendsFetch'][$row['id_member']]['show_cbar']))
            continue;
			
        $buddies = explode(',', $context['friendsFetch'][$row['id_member']]['buddy_list']);
		$mybuddies = explode(',', $user_settings['buddy_list']);	
		
	    if(empty($context['friendsFetch'][$user_settings['id_member']]['show_cbar_buddys']) && empty($context['friendsFetch'][$row['id_member']]['show_cbar_buddys'])){
			    
			$context['friends'][$row['id_member']] = $context['friendsFetch'][$row['id_member']];
	    }
        elseif(!empty($context['friendsFetch'][$user_settings['id_member']]['show_cbar_buddys']) && in_array($user_settings['id_member'],$buddies) && in_array($row['id_member'],$mybuddies)){
			    
	        $context['friends'][$row['id_member']] = $context['friendsFetch'][$row['id_member']];
		}
    }
	$smcFunc['db_free_result']($results);
		
    if($type=='list'){
	    $data = buddy_list_template();
	    return $data;
    }
    else{//must be counting
		return count(isset($context['friends']) ? $context['friends'] : null);
    }
}

function genMemcount() {

	return genMemList('count');
}

function loadDatabase(){

	global $db_persist, $db_connection, $db_server, $db_user, $db_passwd, $db_type, $db_name, $db_count, $sourcedir, $db_prefix;

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

function cachegetData($key, $ttl = 120){
    global $boardurl, $modSettings, $boarddir;
    
	if (empty($modSettings['2sichat_cache']))
		return;
		
	$key = md5($boardurl . filemtime($boarddir.'/sachat/functions.php')) . '-SACHAT-' .str_replace(':','_', $key);
	$cachedir = $boarddir.'/sachat/cache';
	
	if (file_exists($cachedir . '/data_' . $key . '.php') && filesize($cachedir . '/data_' . $key . '.php') > 10)
	{
		require($cachedir . '/data_' . $key . '.php');
		if (!empty($expired) && isset($value))
		{
			@unlink($cachedir . '/data_' . $key . '.php');
			unset($value);
		}
	}

	if (empty($value))
		return null;
	else
		return @unserialize($value);
}

function cacheputData($key, $value, $ttl = 120){
	global $boardurl, $modSettings, $boarddir;
	
	if (empty($modSettings['2sichat_cache']))
		return;
		
	$key = md5($boardurl . filemtime($boarddir.'/sachat/functions.php')) . '-SACHAT-' . str_replace(':','_', $key);
	$value = $value === null ? null : serialize($value);
	$cachedir = $boarddir.'/sachat/cache';
	
	if ($value === null)
		@unlink($cachedir . '/data_' . $key . '.php');
	else
	{
		
		//setup the php cache file and make it pretty ish :P
		$cache_data = '<' . '?' . 'php 
	if (' . (time() + $ttl) . ' < time()) 
		$expired = true; 
	else{
		$expired = false; 
		$value = \'' . addcslashes($value, '\\\'') . '\';
	}
' .'?' . '>';

		$fh = @fopen($cachedir . '/data_' . $key . '.php', 'w');
		if ($fh)
		{
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

	if(!file_exists($boarddir.'/sachat/cache')){mkdir($boarddir.'/sachat/cache', 0755);}
	if(file_exists($boarddir.'/sachat/cache/db_exp_uid'.$member_id.'.txt')){
		$DBcon_exp = unserialize(file_get_contents($boarddir.'/sachat/cache/db_exp_uid'.$member_id.'.txt'));
	}

	// If last connection to the DB is greater than the DB expire stamp die.
	// If no expire stamp die, usually means no messages availible anyways.
	if (isset($_SESSION['DBcon_stamp']) && isset($DBcon_exp) && $_SESSION['DBcon_stamp'] > $DBcon_exp || isset($_SESSION['DBcon_stamp']) && !isset($DBcon_exp)) {
		if (!isset($_REQUEST['msg']) && !isset($_REQUEST['gid']) && !isset($_REQUEST['cid']) && @$_REQUEST['action'] != 'head' && @$_REQUEST['action'] != 'heart' && @$_REQUEST['action'] != 'body') {
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

	ob_start();
	passthru('typeperf -sc 1 "\processor(_total)\% processor time"',$status);
	$content = ob_get_contents();
	ob_end_clean();
	if ($status === 0) {
		if (preg_match("/\,\"([0-9]+\.[0-9]+)\"/",$content,$load)) {
			$cpu[0] = $load[1];
		}
	}
	
	if ($cpu[0] && $cpu[0] > $modSettings['2sichat_max_load']) {
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