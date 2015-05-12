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
		if ($context['gadget']['type'] == 1)
			$context['gadget']['url'] = $context['gadget']['url'];

		if (isset($_REQUEST['src']) && $_REQUEST['src'] == 'true') {
			$context['HTML'] = gadgetObject_template();
		} else {
			$context['JSON']['DATA'] = gadget_template();
			$context['JSON']['GID'] = $context['gadget']['id'];
		}
	}
?>