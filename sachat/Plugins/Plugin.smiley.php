<?php
	if (!defined('SMF'))
		die('No direct access...');
	
	//Create a listener for this plugin.
	add_listener('load_smiles', 'testsmiley');
	
	function testsmiley($data){
		global $modSettings;
		
		$data['code']['pp]'] = 'pp]';
		$data['file']['pp]'] = '<img src="http://www.4smileys.com/smileys/animal-smileys/bee.gif" />';
		
		return $data;
	}
?>