<?php
if (!defined('SMF'))
	die('No direct access...');

function initLink() {

	global $smcFunc, $member_id, $context;

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
	}
}
?>