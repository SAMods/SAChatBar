<?php
if (!defined('SMF'))
	die('No direct access...');

function soc_config(&$config_vars){
	global $modSettings;
	if (!empty($modSettings['sa_soc_links'])){
		$config_vars += array(
			'2sichat_ico_myspace' => array('check', '2sichat_ico_myspace'),
			'2sichat_ico_gplus' => array('check','2sichat_ico_gplus'),
			'2sichat_ico_twit' => array('check','2sichat_ico_twit'),
			'2sichat_ico_fb' => array('check','2sichat_ico_fb'),
			'2sichat_ico_adthis' => array('check','2sichat_ico_adthis'),	
		);
	}
}
?>