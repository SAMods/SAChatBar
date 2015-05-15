<?php
	if (!defined('SMF'))
		die('No direct access...');
	
	//Create a listener for this plugin.
	add_listener('phaseBBC', 'testbbc');

	function testbbc($data){
		
		$tags = 'yt|youtube'; 
		$pattern = '/\[('.$tags.')\=?([^]]+)?\](?:([^]]*)\[\/\1\])/';
        while (preg_match_all($pattern, $data[0], $matches)) foreach ($matches[0] as $key => $match) { 
		
            list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]); 
			
            switch ($tag) { 
                case 'yt': 
                    $videourl = parse_url($innertext); 
                    parse_str($videourl['query'], $videoquery); 
                    if (strpos($videourl['host'], 'youtube.com') !== FALSE) 
						$replacement = '<embed wmode="opaque" allowscriptaccess="never" allowfullscreen="true" scale="scale" quality="high" width="180" height="120" style="display: block;" src="http://www.youtube.com/v/' . $videoquery['v'] . '?version=3&autohide=1" type="application/x-shockwave-flash">';
                break;
				case 'youtube': 
                    $videourl = parse_url($innertext); 
                    parse_str($videourl['query'], $videoquery); 
                    if (strpos($videourl['host'], 'youtube.com') !== FALSE) 
						$replacement = '<embed wmode="opaque" allowscriptaccess="never" allowfullscreen="true" scale="scale" quality="high" width="180" height="120" style="display: block;" src="http://www.youtube.com/v/' . $videoquery['v'] . '?version=3&autohide=1" type="application/x-shockwave-flash">';
                break;
            } 
            $data[0] = str_replace($match, $replacement, $data[0]); 
        } 
			
		return $data[0];
	}
?>