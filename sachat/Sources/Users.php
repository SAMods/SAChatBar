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
		global $member_id, $buddy_id, $user_settings, $IsMobile, $buddy_settings, $context, $password, $modSettings;
		
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
		
		$useragent=$_SERVER['HTTP_USER_AGENT'];

		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			$IsMobile = 1;
		else
			$IsMobile = 0;

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
			SELECT m.buddy_list, m.id_member, m.member_name, m.real_name, o.session, m.show_online, m.avatar, IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
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

			/*if(!empty($_COOKIE[$modSettings['2sichat_cookie_name']."_chatSnoop"])){
				if($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session']){
					$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
				}
			}
			else{*/
				if (!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_buddys'])) {
					if (in_array($member_id, $buddies) && in_array($new_loaded_ids[$i], $mybuddies) && $member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['show_online'] && $user_profile[$new_loaded_ids[$i]]['session']) {
						
						$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
					}
				}
				if (empty($_COOKIE[$modSettings['2sichat_cookie_name']."_buddys"])) {

					if ($member_id != $new_loaded_ids[$i] && $user_profile[$new_loaded_ids[$i]]['session'] && $user_profile[$new_loaded_ids[$i]]['show_online']) {

						$context['friends'][$new_loaded_ids[$i]] = !empty($user_profile) ? $user_profile[$new_loaded_ids[$i]] : array();
					}
				}
			/*}*/
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