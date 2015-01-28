<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('No direct access...');
		
	function chatSearch(){
	
		global $smcFunc, $context;
		
		if(isset($_POST['keywords'])){
			$keywords = htmlspecialchars(stripslashes($_POST['keywords']), ENT_QUOTES);
			
			$searchquery = "m.real_name LIKE '%$keywords%' ";
			
			$results = $smcFunc['db_query']('', '
				SELECT m.id_member, m.member_name, m.real_name, m.avatar, o.session, IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
				FROM {db_prefix}members as m
				LEFT JOIN {db_prefix}attachments AS a ON a.id_member = m.id_member
				LEFT JOIN {db_prefix}log_online AS o ON o.id_member = m.id_member
				WHERE ({raw:searchquery})',
				array(
					'searchquery' => $searchquery,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($results)) {
				
				$row['avatar'] = fixAvatar(array(
					'avatar' => $row['avatar'],
					'id_attach' => $row['id_attach'],
					'attachment_type' => $row['attachment_type'],
					'filename' => $row['filename'],
				));
				$context['search_friends'][] = $row;
			}
			$smcFunc['db_free_result']($results);
		}
		
		$context['JSON']['DATA'] = buddy_search_list_template();
	}
	
	function loadUserData(){
		global $member_id, $buddy_id, $user_settings, $buddy_settings, $context, $password, $modSettings;
		
		// If it is a member lets load some data.
		if ($member_id != 0) {

			$user_settings = loadUserSettings($member_id);
			$user_settings['is_admin'] = in_array(1, $user_settings['groups']);
			$user_settings['is_mod'] = in_array(2, $user_settings['groups']);
			is_banned_check($member_id);
				
			if (!empty($modSettings['2sichat_permissions']))
				loadPermissionsData($user_settings['groups']);
			
			// Load $buddy_settigns if we are chatting.
			if (isset($_REQUEST['cid'])) {
				if (isset($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) {
					$buddy_id = $_REQUEST['cid'];
					$_SESSION['buddy_id'] = $buddy_id;
				} else {
					die(); // Something fishy about a non numeric buddy id.
				}
			}
			
			if(!empty($_SESSION['buddy_id'])){
				$context['JSON']['userTyping'] = $_SESSION['buddy_id'];
				$buddy_settings = loadUserSettings($_SESSION['buddy_id']);
				$context['JSON']['NAME'] = $buddy_settings['real_name'];	
			}
		
		} else if (!empty($modSettings['2sichat_permissions']))
			loadPermissionsData(array(-1));
		
		//Lets see if 2-SI Chat is enabled for this group.
		if (!empty($modSettings['2sichat_permissions']) && !allowedTodo('2sichat_access')) {
			$context['JSON']['STATUS'] = 'NO ACCESS'; // Sorry but you don't have access
			doOutput();
		} else if (!empty($modSettings['2sichat_permissions']) && empty($user_settings['is_admin']) && empty($user_settings['is_mod'])) {
			// Lets just hook into the modSettings
			if (!allowedTodo('2sichat_chat')) {
				$modSettings['2sichat_dis_list'] = 1;
				$modSettings['2sichat_dis_chat'] = 1;
			}
			if (allowedTodo('2sichat_bar')) {
				$modSettings['2sichat_dis_bar'] = 1;
			}
		}
		// Lets validate the password, anyone can put a number in a cookie, lets see if the password checks out.
		if($member_id){
			if (isset($user_settings['passwd']) && strlen($password) != 40 || isset($user_settings['passwd']) && sha1($user_settings['passwd'] . $user_settings['password_salt']) == $password) {
				$context['JSON']['STATUS'] = 'ACTIVE';
			} else if (isset($user_settings['passwd']) &&  hash('sha512', $user_settings['passwd'] . $user_settings['password_salt']) == $password) {//SMF 2.1 compat code
				$context['JSON']['STATUS'] = 'ACTIVE';
			}else {
				$context['JSON']['STATUS'] = 'AUTH FAILED';
				doOutput();
			}
		}
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

		for ($i = 0, $n = count($new_loaded_ids); $i < $n; $i++) {

			$mybuddies = explode(',', $user_profile[$member_id]['buddy_list']);
			$buddies = explode(',', $user_profile[$new_loaded_ids[$i]]['buddy_list']);

			if(!empty($_COOKIE[$modSettings['2sichat_cookie_name']."_chatSnoop"])){
				if($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']){
					$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
				}
			}
			else{
				if (!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_buddys'])) {
					if (in_array($member_id, $buddies) && in_array($new_loaded_ids[$i], $mybuddies) && $member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']) {
						
						$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
					}
				}
				if (empty($_COOKIE[$modSettings['2sichat_cookie_name']."_buddys"])) {

					if ($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']) {

						$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
					}
				}
			}
			$context['online_count'] = count(isset($context['friends']) ? $context['friends'] : null);
		}
		if ($type == 'list') {
			$data = buddy_list_template();
			return $data;
		}
		if ($type == 'count') {
			return count(isset($context['friends']) ? $context['friends'] : null);
		}
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
				$temp['real_name'] = $temp['real_name'];
			else
				$temp['real_name'] = utf8_encode($temp['real_name']);
				
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
?>