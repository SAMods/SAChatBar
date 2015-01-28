<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('No direct access...');
	
	function initgChatSess() {

		global $chatSet, $smcFunc, $context;

		if($_REQUEST['gcid'] != 'Global')
			$chatSet = loadUserSettings($_REQUEST['gcid']);

		$results = $smcFunc['db_query']('', '
			SELECT g.*, m.real_name
			FROM {db_prefix}2sichat_gchat AS g
			LEFT JOIN {db_prefix}members AS m ON m.id_member = g.from
			WHERE g.room = {string:room}
			ORDER BY g.id ASC', 
			array('room' => $_REQUEST['gcid'])
		);
		
		$GlastID = 0;
		if ($results) {
			while ($row = $smcFunc['db_fetch_assoc']($results)) {
				$row['msg'] = htmlspecialchars_decode(phaseMSG($row['msg']));
				if(!empty($row['from'])){
					$row['avatar'] = loadUserSettings($row['from'],false);
				}
				$row['sent'] =  formatDateAgo($row['sent']);
				$context['msgs'][] = $row;
				$GlastID = $row['id'];
			}
		}
		$smcFunc['db_free_result']($results);
		$context['JSON']['LID'] = $GlastID;
		
		if($_REQUEST['gupdate'] == 'true'){
			$context['JSON']['DATA'] = Gchat_update_template();
		}else{
			$context['JSON']['DATA'] = Gchat_window_template();
		}	
	}

	function savemsggc() {

		global $smcFunc, $member_id, $txt, $context, $modSettings;

		// See if they have permission, maybe one day will have a message sent back.
		if (!empty($modSettings['2sichat_dis_chat'])) {
			$context['JSON']['STATUS'] = 'NO CHAT ACCESS';
			doOutput();
		}
		
		if ($_REQUEST['gmsg']==='/who') {
			$context['onlineGroupList'] = GetOnlineListG($_REQUEST['gcid']);
			$context['onlineGroupListCount'] = GetOnlineListG($_REQUEST['gcid'],true);
			$context['msgs'] = $context['onlineGroupListCount'].' '.$txt['bar_display_online'].'<br />';
			if($context['onlineGroupList']){
				foreach($context['onlineGroupList'] as $online){
					$context['msgs'] .= $online['real_name'].'<br />';
				}
			}
			$context['JSON']['fDATA'] = gchat_info_template();
		}
		elseif ($_REQUEST['gmsg']==='/clear') {
			if($_REQUEST['gcid'] == $member_id || allowedTodo('2sichat_bar_adminmode')){
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}2sichat_gchat
					WHERE room = {string:room}', 
					array(
						'room' => $_REQUEST['gcid'],
					)
				);
				$context['msgs'] = $txt['bar_remove_note'];
				$context['JSON']['fDATA'] = gchat_info_template();
			}else{
				$smcFunc['db_insert']('', '{db_prefix}2sichat_gchat', 
					array(
						'from' => 'int',
						'msg' => 'string',
						'room' => 'string',
						'rd' => 'int',
						'sent' => 'string',
					), 
					array(
						$member_id, htmlspecialchars(stripslashes(strip_tags($_REQUEST['gmsg'],'<a>'))),$_REQUEST['gcid'],0,time()
					), 
					array()
				);	
				$context['msgs'] = phaseMSG(htmlspecialchars(stripslashes(strip_tags($_REQUEST['gmsg'],'<a>')), ENT_QUOTES));
				$context['JSON']['fDATA'] = gchat_savemsg_template();
			}
		}
		elseif ($_REQUEST['gmsg']==='/invite') {
			$context['msgs'] = $txt['bar_invite_note1'];
			$context['JSON']['fDATA'] = gchat_info_template();
		}
		elseif (strpos($_REQUEST['gmsg'],'/invite') !== false) {
			if (preg_match_all('@^(?:/invite)?([^/]+)@i', $_REQUEST['gmsg'], $matches) && $_REQUEST['gcid'] == $member_id || allowedTodo('2sichat_bar_adminmode')){
				foreach ($matches as $val) {
					
					$results = $smcFunc['db_query']('', '
						SELECT id_member, real_name
						FROM {db_prefix}members
						WHERE real_name = {string:namereal}
						', 
						array(
							'namereal' =>  preg_replace('/\s+/', '', $val[0]),
						)
					);

					$context['member_data'] = $smcFunc['db_fetch_assoc']($results);
					$smcFunc['db_free_result']($results);
					
					if($context['member_data']['real_name']){
						
						$context['invite_msg'] = $txt['bar_group_chat_invite_to1'] . $txt['bar_group_chat_invite_to2'].' <a href="javascript:void(0)" onclick="javascript:gchat(\''.$_REQUEST['gcid'].'\');return false;">'.$txt['bar_group_chat_invite_to3'].'</a> '.$txt['bar_group_chat_invite_to4'];
						
						$smcFunc['db_insert']('', '{db_prefix}2sichat', 
							array(
								'to' => 'int',
								'from' => 'int',
								'msg' => 'string',
								'sent' => 'string',
								'isrd' => 'int'
							), 
							array(
								$context['member_data']['id_member'], $member_id, stripslashes(strip_tags($context['invite_msg'],'<a>')), date("Y-m-d H:i:s"),0
							), 
							array()
						);	
						$context['msgs'] = ''.$txt['bar_invite_note2'].' '.preg_replace('/\s+/', '', $val[0]);//fix
						$context['JSON']['fDATA'] = gchat_info_template();
					}else{
						$context['msgs'] = ''.$txt['bar_invite_note4'] .' ['.preg_replace('/\s+/', '', $val[0]).'] '.$txt['bar_invite_note3'].'';
						$context['JSON']['fDATA'] = gchat_info_template();
					}
				}
			}else{
				$context['msgs'] = $txt['bar_invite_note5'];
				$context['JSON']['fDATA'] = gchat_info_template();
			}
		}
		else{
			if (str_replace(' ', '', $_REQUEST['gmsg']) != '') {
				
				$smcFunc['db_insert']('', '{db_prefix}2sichat_gchat', 
					array(
						'from' => 'int',
						'msg' => 'string',
						'room' => 'string',
						'rd' => 'int',
						'sent' => 'string',
					), 
					array(
						$member_id, htmlspecialchars(stripslashes(strip_tags($_REQUEST['gmsg'],'<a>'))),$_REQUEST['gcid'],0,time()
					), 
					array()
				);	
				
				$context['msgs'] = phaseMSG(htmlspecialchars(stripslashes(strip_tags($_REQUEST['gmsg'],'<a>')), ENT_QUOTES));
				$context['JSON']['fDATA'] = gchat_savemsg_template();
			}
		}
		if (defined('loadOpt'))
			doOptDBexp();
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
			while ($row = $smcFunc['db_fetch_assoc']($results)) {
				$row['msg'] = htmlspecialchars_decode(phaseMSG($row['msg']));
				$context['msgs'][] = $row;
				$lastID = $row['id'];
				mread($row['id']);
			}
			$smcFunc['db_free_result']($results);
		}
		if($_REQUEST['update'] == 'true'){
			$context['JSON']['DATA'] = chat_update_template();
		}else{
			$context['JSON']['DATA'] = chat_window_template();
		}
		
	   // $context['JSON']['DATA'] = chat_window_template();
		$context['JSON']['BID'] = $buddy_id;
		$context['JSON']['ID'] = $lastID;
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
		//$context['JSON']['NAME'] = $buddy_settings['real_name'];
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
					$buddy_id, $member_id, htmlspecialchars(stripslashes(strip_tags($_REQUEST['msg'],'<a>')), ENT_QUOTES),date("Y-m-d H:i:s"),0
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
			
			$context['msgs'] = phaseMSG(htmlspecialchars(stripslashes(strip_tags($_REQUEST['msg'],'<a>')), ENT_QUOTES));

			if (defined('loadOpt'))
				doOptDBexp();
				
			$context['JSON']['DATA'] = chat_savemsg_template();
		}
	}
	
	function newMsgPrivate() {
		
		global $smcFunc, $member_id, $context;
		 
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
					$context['JSON']['ids'][$row['id']] = $row['from'];
				
			}
			$smcFunc['db_free_result']($results);
		}   
		else {
			if (defined('loadOpt')) {
				doOptDBrec();
			}
			$context['JSON']['STATUS'] = 'NO RESULTS';
		}
	}

	function newMsgGroup() {
		
		global $smcFunc, $modSettings, $member_id, $context;
		 
		if(!empty($modSettings['2sichat_groupeChatGlobal']) || !empty($modSettings['2sichat_groupeChat'])){
			$results = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_gchat
				WHERE {db_prefix}2sichat_gchat.rd = 0 AND {db_prefix}2sichat_gchat.from != {int:member_id}', 
				array(
					'member_id' => $member_id,
				)
			);
			
			if ($results && $smcFunc['db_num_rows']($results) != 0) {
				$context['JSON']['gids'] = array();
				while ($row = $smcFunc['db_fetch_assoc']($results)) {
					$context['JSON']['gids'][$row['id']] = $row['room'];
					$GlastID = $row['id'];
				}
				$context['JSON']['LID'] = $GlastID;
				$smcFunc['db_free_result']($results);
			} 
			else {
				if (defined('loadOpt')) {
					doOptDBrec();
				}
				$context['JSON']['STATUS'] = 'NO RESULTS';
			}
		}
	}
?>