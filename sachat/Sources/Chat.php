<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('No direct access...');

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
?>