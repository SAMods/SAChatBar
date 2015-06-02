<?php
if (!defined('SMF'))
	die('No direct access...');
	
function gadget_action(&$subActions){
	global $modSettings;
	
	if(!empty($modSettings['sa_gadgets']))
		$subActions += array('gadget' => 'twosichatGadget');
}

function chat_admin_area(&$admin_areas){
	global $txt, $modSettings;
	
	if(!empty($modSettings['sa_gadgets']))
		$admin_areas['sachat']['areas']['plugsetting']['subsections']['gadget'] = array($txt['twosichatGadget']);
}

function twosichatGadget() {

	global $txt, $context, $smcFunc;
	
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_gadgets'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_gadgets_des'];

	$context['page_title'] = $txt['2sichat_admin'];
	loadTemplate('sachat');

	$context['html_headers'].= '
		<script type="text/javascript">
			function ShowGadgetLink(id){
				if (document.getElementById(id).style.display == \'none\') {
					document.getElementById(id).style.display = \'block\';
				} else {
					document.getElementById(id).style.display = \'none\';
				}
				document.getElementById(id).focus();
				document.getElementById(id).select();
			}
		</script>';

	if (isset($_REQUEST['delete'])) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat_gadgets
			WHERE id = {int:did}', array(
			'did' => (int) $_REQUEST['delete'],
					)
		);
		twosicleanCache();
		redirectexit('action=admin;area=plugsetting;sa=gadget');
	} else if (isset($_REQUEST['edit'])) {
		$context['sub_template'] = 'twosichatGadAdd';
		$context['gadget'] = array();
		if ($_REQUEST['edit'] != '') {
			$request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_gadgets
				WHERE id = {int:did}', array(
					'did' => (int) $_REQUEST['edit'],
				)
			);
			$context['gadget'] = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
		}
	} elseif (isset($_REQUEST['save'])) {
		if (isset($_REQUEST['mod'])) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_gadgets
				SET title = {string:title}, url = {string:url}, width = {string:width}, height = {string:height}, ord = {string:ord}, vis = {string:vis}, type = {string:type}
				WHERE id = {int:did}', array('did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'width' => $_POST['width'], 'height' => $_POST['height'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'], 'type' => $_POST['type'])
			);
		} else {
			$smcFunc['db_insert']('', '{db_prefix}2sichat_gadgets', array('title' => 'string', 'url' => 'string', 'width' => 'string', 'height' => 'string', 'ord' => 'string', 'vis' => 'string', 'type' => 'string'), array($_POST['title'], $_POST['url'], $_POST['width'], $_POST['height'], $_POST['ord'], $_POST['vis'], $_POST['type']), array());
		}
		twosicleanCache();
		redirectexit('action=admin;area=plugsetting;sa=gadget');
		
	} else {
		twosigadDis();
	}
}

function twosigadDis() {

	global $txt, $context, $smcFunc;

	$context['sub_template'] = 'twosichatGadgets';
	$context['gadgets'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_gadgets
		ORDER BY ord', array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$context['gadgets'][] = array(
			'id' => $row['id'],
			'title' => $row['title'],
			'ord' => $row['ord'],
			'vis' => $row['vis'],
			'type' => $row['type']
		);
	}
	$smcFunc['db_free_result']($request);
}
?>