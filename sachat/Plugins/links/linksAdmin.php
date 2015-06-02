<?php
if (!defined('SMF'))
	die('No direct access...');
	
function link_admin_area(&$admin_areas){
	global $txt, $modSettings;
	
	if(!empty($modSettings['sa_links']))
		$admin_areas['sachat']['areas']['plugsetting']['subsections']['link'] = array($txt['2sichat_linksd2']);
}

function links_action(&$subActions){
	global $modSettings;
	
	if(!empty($modSettings['sa_links']))
		$subActions += array('link' => 'twosichatLinks');
}
	
function twosichatlinks() {

	global $txt, $context, $smcFunc;

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_linksd'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_linksd1'];

	$context['page_title'] = $txt['2sichat_admin'];
	loadTemplate('sachat');

	if (isset($_REQUEST['delete'])) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat_barlinks
			WHERE id = {int:did}', 
			array(
				'did' => (int) $_REQUEST['delete'],
			)
		);
		twosicleanCache();
		redirectexit('action=admin;area=plugsetting;sa=link');
	} else if (isset($_REQUEST['edit'])) {
		$context['sub_template'] = 'twosichatLinkAdd';
		$context['gadget'] = array();
		if ($_REQUEST['edit'] != '') {
			$request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_barlinks
				WHERE id = {int:did}', array(
					'did' => (int) $_REQUEST['edit'],
				)
			);
			$context['gadget'] = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
		}
	} elseif (isset($_REQUEST['save'])) {
		if (isset($_REQUEST['mod'])) {
			$_POST['newwin'] = isset($_POST['newwin']) ? 1 : 0;
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_barlinks
				SET newwin = {string:newwin}, title = {string:title}, url = {string:url}, image = {string:image}, ord = {string:ord}, vis = {string:vis}
				WHERE id = {int:did}', array('newwin' => $_POST['newwin'], 'did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'image' => $_POST['image'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'])
			);
		} else {
			$_POST['newwin'] = isset($_POST['newwin']) ? 1 : 0;
			$smcFunc['db_insert']('', '{db_prefix}2sichat_barlinks', array('newwin' => 'string', 'title' => 'string', 'url' => 'string', 'image' => 'string', 'ord' => 'string', 'vis' => 'string'), array($_POST['newwin'], $_POST['title'], $_POST['url'], $_POST['image'], $_POST['ord'], $_POST['vis']), array()
			);
		}
		twosicleanCache();
		redirectexit('action=admin;area=plugsetting;sa=link');
	} else {
		twosigadLink();
	}
}

function twosigadLink() {

	global $txt, $context, $smcFunc;

	$context['sub_template'] = 'twosichatLinks';
	$context['gadgets'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_barlinks
		ORDER BY ord', array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$context['gadgets'][] = array(
			'id' => $row['id'],
			'title' => $row['title'],
			'url' => $row['url'],
			'image' => $row['image'],
			'ord' => $row['ord'],
			'vis' => $row['vis'],
			'newwin' => $row['newwin']
		);
	}
	$smcFunc['db_free_result']($request);
}
?>