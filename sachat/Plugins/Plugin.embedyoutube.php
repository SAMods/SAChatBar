<?php
	if (!defined('SMF'))
		die('No direct access...');
	
	//Create a listener for this plugin.
	add_listener('phaseMsg', 'embedYoutube');
	
	function embedYoutube($data){
		
		if ((strpos($data[0], 'www.youtube.com/watch') !== false) && strpos($data[0], '[url') === false && strpos($data[0], '[video') === false)
		{
			$videourl = parse_url($data[0]); 
            parse_str($videourl['query'], $videoquery); 
			if (isset($videourl['host']) && strpos($videourl['host'], 'youtube.com') !== FALSE) 
				$data[0] = '<embed wmode="opaque" allowscriptaccess="never" allowfullscreen="true" scale="scale" quality="high" width="180" height="120" style="display: block;" src="http://www.youtube.com/v/' . $videoquery['v'] . '?version=3&autohide=1" type="application/x-shockwave-flash">';
		}
		return $data[0];
	}
?>