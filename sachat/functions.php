<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/

function doCharset($db_character_set) {
    global $smcFunc;

    if (!empty($db_character_set)) {
        $smcFunc['db_query']('set_character_set', 'SET NAMES ' . $db_character_set, array()
        );
    }
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

function usershowBar($member_id){
    global $smcFunc;
    
	$request = $smcFunc['db_query']('','
	     SELECT value
		 FROM {db_prefix}themes
		 WHERE variable = {string:show_cbar} AND id_member = {int:member}',
		 array(
		     'show_cbar' => 'show_cbar',
			 'member' => $member_id,
		 
		 )
	);
	$userBar = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	
	return $userBar['value'];
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
    global $boardurl, $time_bstart, $boarddir;

    if (isset($_REQUEST['theme']) && !strstr('..', $_REQUEST['theme']) && is_file('./themes/' . $_REQUEST['theme'] . '/template.php') && is_file('./themes/' . $_REQUEST['theme'] . '/style.css')) {
        $themeurl = $boardurl . '/sachat/themes/' . $_REQUEST['theme'];
        $themedir = $boarddir . '/sachat/themes/' . $_REQUEST['theme'];
        $thjs = 'theme=' . $_REQUEST['theme'] . '&';
        $load_btime = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_bstart)), 3);
    } else {
        $themeurl = $boardurl . '/sachat/themes/default';
        $themedir = $boarddir . '/sachat/themes/default';
        $thjs = '';
        $load_btime = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_bstart)), 3);
    }

	if(file_exists($themedir . '/template.php')){
		require_once($themedir . '/template.php');
	}else{
		require_once($boarddir . '/sachat/themes/master.template.php');
	}
	
    return array($themeurl, $themedir, $thjs, $load_btime);
}

function initLang($lang) {
    global $boarddir, $themedir;

    if (isset($lang) && is_file($themedir . '/languages/' . $lang . '.php')) {
        $langfile = $themedir . '/languages/' . $lang . '.php';
    } elseif (is_file($themedir . '/languages/english.php')) {
        $langfile = $themedir . '/languages/english.php';
    } else {
        $langfile = $boarddir . '/sachat/themes/default/languages/english.php';
    }

    return $langfile;
}

function initJs($jsType) {
    global $themedir;

    if (file_exists($themedir . '/js/' . $jsType . '.js.php')) {
        require_once($themedir . '/js/' . $jsType . '.js.php');
    } else {
        require_once($boarddir . '/sachat/themes/default/js/' . $jsType . '.js.php');
    }
}

function initLink() {

    global $smcFunc, $member_id, $context;

    if (($context['gadgetslink'] = cachegetData('gadgetslink', 3600)) === null) {
        $results = $smcFunc['db_query']('', '
		    SELECT *
		    FROM {db_prefix}2sichat_barlinks
		    ORDER BY ord', array()
        );

        if ($results) {
            while ($row = $smcFunc['db_fetch_assoc']($results)) {
                if ($row['vis'] != 0) {
                    if ($row['vis'] == 1 && $member_id || $row['vis'] == 2 && !$member_id || $row['vis'] == 3) {
                        $context['gadgetslink'][] = $row;
                    }
                }
            }
            $smcFunc['db_free_result']($results);

            cacheputData('gadgetslink', $context['gadgetslink'], 3600);
        }
    }
}

function initGadgets() {

    global $smcFunc, $context, $member_id;

    if (($context['gadgets'] = cachegetData('gadgets', 3600)) === null) {
        $results = $smcFunc['db_query']('', '
		    SELECT *
		    FROM {db_prefix}2sichat_gadgets
		    ORDER BY ord', array()
        );

        if ($results) {
            while ($row = $smcFunc['db_fetch_assoc']($results)) {
                if ($row['vis'] != 0) {
                    if ($row['vis'] == 1 && $member_id || $row['vis'] == 2 && !$member_id || $row['vis'] == 3) {
                        $context['gadgets'][] = $row;
                    }
                }
            }
            $smcFunc['db_free_result']($results);
            cacheputData('gadgets', $context['gadgets'], 3600);
        }
    }
}

function gadget() {

    global $smcFunc, $context;

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
	if ($context['gadget']['type'] == 0) {
		
		$context['gadget']['url'] = trim($context['gadget']['url']);
        $context['gadget']['url'] = trim($context['gadget']['url'], '<?php');
        $context['gadget']['url'] = trim($context['gadget']['url'], '?>');
        ob_start();
        $context['gadget']['url'] = eval($context['gadget']['url']);
        $context['gadget']['url'] = ob_get_contents();
        ob_end_clean();
    }
    //type = html
    if ($context['gadget']['type'] == 1) {
        
		$context['gadget']['url'] = $context['gadget']['url'];
		
    }

    if (isset($_REQUEST['src']) && $_REQUEST['src'] == 'true') {
        $context['HTML'] = gadgetObject_template();
    } else {
        $context['JSON']['DATA'] = gadget_template();
        $context['JSON']['GID'] = $context['gadget']['id'];
    }
}

function initChatSess() {

    global $smcFunc, $member_id, $buddy_id, $context;
	
	getBuddySession();
	CheckTyping();
    // Now on to the messages
    $results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE ({db_prefix}2sichat.to = {int:buddy_id} AND {db_prefix}2sichat.from = {int:member_id}) OR ({db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id})
		ORDER BY id ASC', 
		array(
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
		)
    );

    $lastID = 0;

    if ($results) {
        mread(); // Mark messages read since we are displaying them.
        while ($row = $smcFunc['db_fetch_assoc']($results)) {
            $row['msg'] = htmlspecialchars_decode(phaseMSG($row['msg']));
            $context['msgs'][] = $row;
            $lastID = $row['id'];
        }
        $smcFunc['db_free_result']($results);
    }
    $context['JSON']['DATA'] = chat_window_template();
    $context['JSON']['BID'] = $buddy_id;
    $context['JSON']['ID'] = $lastID;
}

function retmsgs() {

    global $smcFunc, $member_id, $buddy_id, $context, $modSettings;

    $reqTime = $_REQUEST['_'] - $modSettings['2sichat_cw_heart'];
	getBuddySession();
	CheckTyping();
	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE ({db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd = 0)
		ORDER BY id ASC',
		array(
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
			'reqTime' => $reqTime,
		)
    );

    $lastID = 0;

    if ($results) {
       mread(); // Mark messages read since we are displaying them.
        while ($row = $smcFunc['db_fetch_assoc']($results)) {
            $row['msg'] = phaseMSG($row['msg']);
            $context['msgs'][] = $row;
            $lastID = $row['id'];
        }
        $smcFunc['db_free_result']($results);
        $context['JSON']['DATA'] = chat_retmsg_template();
        $context['JSON']['ID'] = $lastID;
    }
}

function savemsg() {

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
		
		$smcFunc['db_insert']('', '{db_prefix}2sichat', 
		    array(
			    'to' => 'int',
			    'from' => 'int',
			    'msg' => 'string',
			    'sent' => 'string',
				'isrd' => 'int'
			), 
			array(
				$buddy_id, $member_id, htmlspecialchars(stripslashes($_REQUEST['msg']), ENT_QUOTES),date("Y-m-d H:i:s"),0
			), 
			array()
		);	
		
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}2sichat_typestaus
				WHERE member_id = {int:member_id}', 
				array(
					'member_id' => $member_id,
				)
			);
		

        $context['msgs'] = phaseMSG(htmlspecialchars(stripslashes($_REQUEST['msg']), ENT_QUOTES));

        if (defined('loadOpt')) {
            doOptDBexp();
        }
        $context['JSON']['DATA'] = chat_savemsg_template();
    }
}

function mread() {

    global $smcFunc, $member_id, $buddy_id;

    if (isset($_REQUEST['_'])) {$read = $_REQUEST['_'];}
    
	if (!isset($read)) {$read = 1;}

    // Mark messages read.
    $smcFunc['db_query']('', '
		UPDATE {db_prefix}2sichat
		SET {db_prefix}2sichat.rd = {float:read}
		WHERE {db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd = 0',
		array(
			'member_id' => $member_id,
			'buddy_id' => $buddy_id,
			'read' => $read,
		)
    );
    
	if (defined('loadOpt')) {doOptDBrec();}
}

function initCleanup() {

    global $smcFunc, $modSettings;

    $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}2sichat
		WHERE {db_prefix}2sichat.rd != 0 AND {db_prefix}2sichat.sent < {int:purge}', 
		array(
			'purge' => date("Ymd", strtotime('-' . $modSettings['2sichat_purge'] . ' days', strtotime(date("Ymd")))),
		)
    );
}

function closechat(){

	if(!empty($_SESSION['buddy_id'])){
		unset($_SESSION['buddy_id']);
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
			$smcFunc['db_free_result']($results);
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
    
	global $smcFunc, $modSettings, $context, $txt, $member_id;
	
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

function getBuddySession(){
	global $context;
	
	if(!empty($_SESSION['buddy_id'])){
		$buddy_settings = loadUserSettings($_SESSION['buddy_id']);
		$context['JSON']['buddySESSION'] = $buddy_settings['session'];	
	}
}


function doheart() {

    global $smcFunc, $member_id, $context;

    CheckActive();
	CheckTyping();
	getBuddySession();
	
    $results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat
		WHERE {db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.rd = 0', 
		array(
			'member_id' => $member_id,
        )
    );

    if ($results && $smcFunc['db_num_rows']($results) != 0) {
        $context['JSON']['ids'] = array();
        while ($row = $smcFunc['db_fetch_assoc']($results)) {
			if (!in_array($row['from'], $context['JSON']['ids'])) {
				$context['JSON']['ids'][] = $row['from'];
			}
			
      }
      $smcFunc['db_free_result']($results);
    } else {
        if (defined('loadOpt')) {
            doOptDBrec();
        }
        $context['JSON']['STATUS'] = 'NO RESULTS';
    }
}

function load_smiles() {

    global $smcFunc, $modSettings;

    $smiles = array();
    if (($smiles = cachegetData('smiless', 90)) == null) {
	   
	    $results = $smcFunc['db_query']('', '
		    SELECT code, filename
		    FROM {db_prefix}smileys', array()
        );

        while ($row = $smcFunc['db_fetch_assoc']($results)) {
            $smiles['code'][] = htmlspecialchars($row['code'], ENT_QUOTES);
            $smiles['file'][] = '<img src="' . $modSettings['smileys_url'] . '/' . $modSettings['smiley_sets_default'] . '/' . $row['filename'] . '">';
        }
        $smcFunc['db_free_result']($results);
	    cacheputData('smiless', $smiles, 90);
    }

    return $smiles;
}

function is_banned_check($member) {
    global $smcFunc, $modSettings;
	
	$user_info = loadUserSettings($member,true);

	$_SESSION['cban'] = array();
	
	// Only check the ban every so often. (to reduce load.)
	if (isset($_SESSION['cban']) || empty($modSettings['banLastUpdated']) || ($_SESSION['cban']['last_checked'] < $modSettings['banLastUpdated']) || $_SESSION['cban']['id_member'] != $user_info['id_member'] || $_SESSION['cban']['ip'] != $user_info['member_ip'] || $_SESSION['cban']['ip2'] != $user_info['member_ip2'] || (isset($user_info['email_address'], $_SESSION['cban']['email']) && $_SESSION['cban']['email'] != $user_info['email_address']))
	{
	    // Innocent until proven guilty.  (but we know you are! :P)
		$_SESSION['cban'] = array(
			'last_checked' => time(),
			'id_member' => $user_info['id_member'],
			'ip' => $user_info['member_ip'],
			'ip2' => $user_info['member_ip2'],
			'email' => $user_info['email_address'],
		);
		
	    $results = $smcFunc['db_query']('', '
	        SELECT name
		    FROM {db_prefix}ban_groups
		    WHERE name = {string:current_member}',
	        array(
	            'current_member' => $user_info['real_name'],
	        )
	    );
	    $bannedUser = $smcFunc['db_fetch_assoc']($results);
        $smcFunc['db_free_result']($results);
	
	    if($bannedUser)
	        die;// No point in going on if this user is banned
	}
}

function censorMSG($data) {

    $replace = '';
    $rop = '*';

    for ($i = 1; $i < strlen($data); $i++) {
        $replace .= $rop;
    }

    $data = substr_replace($data, $replace, 1);
    return $data;
}

/**
 * @return string
 */
function phaseMSG($data) {
    global $modSettings;

    if (!empty($modSettings['2sichat_simple_bbc']))
		$data = phaseBBC($data); 
	
	$data = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target=\"_blank\">$1</a>', $data);
	
	// Load up the smileys
    $smiles = load_smiles();
    $data = str_replace($smiles['code'], $smiles['file'], $data);

    if (!empty($modSettings['2sichat_censor_words']) && !empty($modSettings['2sichat_censor'])) {
        $badwords = explode('|', $modSettings['2sichat_censor_words']);

        for ($i = 0; $i < count($badwords); $i++) {
            $data = str_replace($badwords[$i], censorMSG($badwords[$i]), $data);
        }
    }

    return $data;
}

function phaseBBC($data) {

    // If no BBC just return the data, don't do anything extra
    if ((strpos($data, "[") === false || strpos($data, "]") === false)) {
        return $data;
    }

    $codes = array(
        '/(\[[b]\])(.+)(\[\/[b]\])/', // Bold
        '/(\[[u]\])(.+)(\[\/[u]\])/', // Image
        '/(\[[i]\])(.+)(\[\/[i]\])/', // Italic
        '/(\[color=)(.+)(\])(.+)(\[\/color\])/', // Color
        '/(\[url\]http)(.+)(\[\/url\])/', // Url no description, http
        '/(\[url\])(.+)(\[\/url\])/', // Url no description, no http
        '/(\[url=http)(.+)(\])(.+)(\[\/url\])/', // Url description, http
        '/(\[url=)(.+)(\])(.+)(\[\/url\])/', // Url description, no http
//		'/(\[img\]http)(.+)(\[\/img\])/',			// Image, http
//		'/(\[img\])(.+)(\[\/img\])/'				// Image, no http
    );
    $html = array(
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

function loadUserSettings($id, $check=false) {

    global $smcFunc, $modSettings;

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

    if(isset($modSettings['global_character_set']) && $modSettings['global_character_set'] != 'UTF-8'){
		if($check)
		{
			$temp['real_name'] = $temp['real_name'];
		}
		else{
			$temp['real_name'] = utf8_encode($temp['real_name']);
		}
	}else{
		$temp['real_name'] = $temp['real_name'];
	}

    //Lets do some fusion, Fuussiioooon Ahhhhh!!!!!
    $temp['groups'] = !empty($temp['additional_groups']) ? array_merge(array($temp['id_group'], $temp['id_post_group']), explode(',', $temp['additional_groups'])) : array($temp['id_group'], $temp['id_post_group']);

    $temp['avatar'] = fixAvatar(array(
        'avatar' => !empty($temp['avatar']) ? $temp['avatar'] : '',
        'id_attach' => $temp['id_attach'],
        'attachment_type' => $temp['attachment_type'],
        'filename' => $temp['filename'],
    ));

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
        $data = implode(',', $data);
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

    global $modSettings, $context;

    if (empty($modSettings['2sichat_dis_list'])) {
        $context['JSON']['ONLINE'] = genMemList('list');
        $context['JSON']['CONLINE'] = genMemcount('count');
    }
}

function fixAvatar($data) {
    global $modSettings, $themeurl, $boardurl;

    if (!$data['avatar'] && $data['id_attach']) {
        if ($data['attachment_type'] == 0) {
            $data['avatar'] = $boardurl . '/index.php?action=dlattach;attach=' . $data['id_attach'] . ';type=avatar';
        }
        if ($data['attachment_type'] == 1) {
            $data['avatar'] = (isset($modSettings['custom_avatar_enabled']) ? $modSettings['custom_avatar_url'] : $modSettings['avatar_url']) . '/' . $data['filename'];
        }
    } else if (!$data['avatar']) {
        $data['avatar'] = $themeurl . '/images/blankuser.png';
    }
    if (substr($data['avatar'], 0, 4) != 'http') {
        $data['avatar'] = $modSettings['avatar_url'] . '/' . $data['avatar'];
    } // Allot of junk to fix one avatar, SMF needs to get this right.

    return $data['avatar'];
}

function genMemList($type = 'list') {

    global $smcFunc, $member_id, $modSettings, $context;
	
    $results = $smcFunc['db_query']('', '
		SELECT m.buddy_list, m.id_member, m.member_name, m.real_name, o.session, m.avatar, IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
		FROM {db_prefix}members as m
		LEFT JOIN {db_prefix}log_online AS o ON o.id_member = m.id_member
		LEFT JOIN {db_prefix}attachments AS a ON a.id_member = m.id_member', array());
    $new_loaded_ids = array();
    while ($row = $smcFunc['db_fetch_assoc']($results)) {

        $row['avatar'] = fixAvatar(array(
            'avatar' => $row['avatar'],
            'id_attach' => $row['id_attach'],
            'attachment_type' => $row['attachment_type'],
            'filename' => $row['filename'],
        ));

        $new_loaded_ids[] = $row['id_member'];
        $row['options'] = array();
        $user_profile[$row['id_member']] = $row;
    }
    $smcFunc['db_free_result']($results);

    if (!empty($new_loaded_ids)) {

        $results = $smcFunc['db_query']('', '
		    SELECT id_member, variable, value
            FROM {db_prefix}themes
            WHERE id_member' . (count($new_loaded_ids) == 1 ? ' = {int:loaded_ids}' : ' IN ({array_int:loaded_ids})') . '
            AND variable IN ({array_string:opt})', array(
            'loaded_ids' => count($new_loaded_ids) == 1 ? $new_loaded_ids[0] : $new_loaded_ids,
            'opt' => array('show_cbar', 'show_cbar_buddys'),
                )
        );
        while ($row = $smcFunc['db_fetch_assoc']($results)) {
            $user_profile[$row['id_member']]['options'][$row['variable']] = $row['value'];
        }
        $smcFunc['db_free_result']($results);
    }

    for ($i = 0, $n = count($new_loaded_ids); $i < $n; $i++) {

        $mybuddies = explode(',', $user_profile[$member_id]['buddy_list']);
        $buddies = explode(',', $user_profile[$new_loaded_ids[$i]]['buddy_list']);

		if(!empty($_COOKIE[$modSettings['2sichat_cookie_name']."_chatSnoop"])){
		    if($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']){
		        $context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
			}
		}
		else{
            if (!empty($user_profile[$member_id]['options']['show_cbar_buddys']) && empty($user_profile[$new_loaded_ids[$i]]['options']['show_cbar'])) {

                if (in_array($member_id, $buddies) && in_array($new_loaded_ids[$i], $mybuddies) && $member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']) {

                    $context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
                }
            }
            if (empty($user_profile[$member_id]['options']['show_cbar_buddys']) && empty($user_profile[$new_loaded_ids[$i]]['options']['show_cbar']) && empty($user_profile[$new_loaded_ids[$i]]['options']['show_cbar_buddys'])) {

                if ($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']) {

                    $context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
                }
            }
	    }
    }
    if ($type == 'list') {
        $data = buddy_list_template();
        return $data;
    }
    if ($type == 'count') {
        return count(isset($context['friends']) ? $context['friends'] : null);
    }
}

function genMemcount() {

    return genMemList('count');
}

function loadDatabase() {

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

/**
 * @param string $key
 */
function cachegetData($key, $ttl = 120) {
    global $boardurl, $modSettings, $boarddir;

    if (empty($modSettings['2sichat_cache']))
        return;

    $key = md5($boardurl . filemtime($boarddir . '/sachat/functions.php')) . '-SACHAT-' . str_replace(':', '_', $key);
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

    $key = md5($boardurl . filemtime($boarddir . '/sachat/functions.php')) . '-SACHAT-' . str_replace(':', '_', $key);
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

    if (!file_exists($boarddir . '/sachat/cache')) {
        mkdir($boarddir . '/sachat/cache', 0755);
    }
    if (file_exists($boarddir . '/sachat/cache/db_exp_uid' . $member_id . '.txt')) {
        $DBcon_exp = unserialize(file_get_contents($boarddir . '/sachat/cache/db_exp_uid' . $member_id . '.txt'));
    }

    // If last connection to the DB is greater than the DB expire stamp die.
    // If no expire stamp die, usually means no messages availible anyways.
    if (isset($_SESSION['DBcon_stamp']) && isset($DBcon_exp) && $_SESSION['DBcon_stamp'] > $DBcon_exp || isset($_SESSION['DBcon_stamp']) && !isset($DBcon_exp)) {
        if (!isset($_REQUEST['msg']) && !isset($_REQUEST['gid']) && !isset($_REQUEST['cid']) && @$_REQUEST['action'] != 'closechat' && @$_REQUEST['action'] != 'typing' && @$_REQUEST['action'] != 'head' && @$_REQUEST['action'] != 'heart' && @$_REQUEST['action'] != 'body') {
            $context['JSON']['STATUS'] = 'IDLE';
            doOutput();
        }
    }
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

   /** ob_start();
    passthru('typeperf -sc 1 "\processor(_total)\% processor time"', $status);
    $content = ob_get_contents();
    ob_end_clean();
    if ($status === 0) {
        if (preg_match("/\,\"([0-9]+\.[0-9]+)\"/", $content, $load)) {
            $cpu[0] = $load[1];
        }
    }*/
	if(function_exists ("sys_getloadavg")){
		$load = sys_getloadavg();
		
		if ($load[0] && $load[0] > $modSettings['2sichat_max_load']) {
			if ($modSettings['2sichat_load_dis_chat']) {
				$context['JSON']['STATUS'] = 'AWAY';
				doOutput();
			}
			if ($modSettings['2sichat_load_dis_bar']) {
				$modSettings['2sichat_dis_bar'] = 1;
			}
			if ($modSettings['2sichat_load_dis_list']) {
				$modSettings['2sichat_dis_list'] = 1;
			}
		}
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

?>