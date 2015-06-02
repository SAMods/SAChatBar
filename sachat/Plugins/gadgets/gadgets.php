<?php
if (!defined('SMF'))
	die('No direct access...');

function dogadgets(){
	global $modSettings;
	
	if (isset($_REQUEST['gid']) && !empty($modSettings['sa_gadgets']))
		gadget();
}

function initGadgets() {

	global $smcFunc, $context, $modSettings, $boarddir,$member_id;

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'body' && !empty($modSettings['sa_gadgets'])){
		
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