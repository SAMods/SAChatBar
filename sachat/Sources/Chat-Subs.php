<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('No direct access...');
	
	function phaseBBC($string) {
		global $listeners, $txt;
		
		if ((strpos($string, "[") === false || strpos($string, "]") === false))
			return $string;
		
		if(isset($listeners['phaseBBC']))
			$string = hook('phaseBBC', $string);
			
		//'`\[('.$tags.')=?(.*?)\](.+?)\[/\1\]`'
		$tags = 'b|i|size|color|quote|url|img|glow|video'; 
		$pattern = '/\[('.$tags.')\=?([^]]+)?\](?:([^]]*)\[\/\1\])/';
        while (preg_match_all($pattern, $string, $matches)) foreach ($matches[0] as $key => $match) { 
		
            list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]); 
			
            switch ($tag) { 
				case 'b': 
					$replacement = '<strong>'.$innertext.'</strong>'; 
				break; 
                case 'i': 
					$replacement = '<em>'.$innertext.'</em>'; 
				break;
				case 'glow':
					$replacement = '<span style="text-shadow: '.$param.' 1px 1px 1px">'.$innertext.'</span>';
				break;
                case 'size': 
					$replacement = '<span style="font-size: '.$param.';">'.$innertext.'</span>'; 
				break; 
                case 'color': 
					$replacement = '<span style="color: '.$param.';">'.$innertext.'</span>'; 
				break; 
                case 'quote': 
					$replacement = '' .($param? '<cite>'.$txt['bar_msg_quote'].': '.$param.'</cite>' : '<cite>'.$txt['bar_msg_quote'].':</cite>').'<blockquote>'.$innertext.'</blockquote>';
				break; 
                case 'url':
					$replacement = '<a href="' . ($param ? $param : $innertext) . '" target=\"_blank\">' . ($param ? $innertext : ''.$txt['bar_msg_link'].'') . '</a>'; 
				break; 
				case 'img': 
                    $replacement = '<img src='.$innertext.' width="60"  height="60" />'; 
                break; 
                case 'video': 
                    $videourl = parse_url($innertext); 
                    parse_str($videourl['query'], $videoquery); 
                    if (strpos($videourl['host'], 'youtube.com') !== FALSE) 
						$replacement = '<embed wmode="opaque" allowscriptaccess="never" allowfullscreen="true" scale="scale" quality="high" width="180" height="120" style="display: block;" src="http://www.youtube.com/v/' . $videoquery['v'] . '?version=3&autohide=1" type="application/x-shockwave-flash">';
                break;
            } 
            $string = str_replace($match, $replacement, $string); 
        } 
			
        return $string;
	}
	
	function phaseMSG($data) {
		global $context, $txt, $listeners, $modSettings;
		
		
		if(isset($listeners['phaseMsg']))
			$data = hook('phaseMsg', $data);
		
		if ((strpos($data, '://') !== false || strpos($data, 'www.') !== false) && strpos($data, '[url') === false)
		{
			$data = strtr($data, array('&#039;' => '\'', '&nbsp;' => $context['sa_utf8'] ? "\xC2\xA0" : "\xA0", '&quot;' => '>">', '"' => '<"<', '&lt;' => '<lt<'));

			if (is_string($result = preg_replace(array(
				'~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
				'~(?<=[\s>(\'<]|^)(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i'
			),
			array(
				'<a href="$1" target=\"_blank\">'.$txt['bar_msg_link'].'</a>',
				'<a href="$1" target=\"_blank\">'.$txt['bar_msg_link'].'</a>'
			), $data)))
			$data = $result;

			$data = strtr($data, array('\'' => '&#039;', $context['sa_utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;', '>">' => '&quot;', '<"<' => '"', '<lt<' => '&lt;'));
		}
		if (!empty($modSettings['2sichat_simple_bbc']))
			$data = phaseBBC($data); 
		
		// Load up the smileys
		$smiles = load_smiles();
		$data = str_replace($smiles['code'], $smiles['file'], $data);

		if (!empty($modSettings['2sichat_censor_words']) && !empty($modSettings['2sichat_censor'])) {
			$badwords = explode('|', $modSettings['2sichat_censor_words']);

			for ($i = 0; $i < count($badwords); $i++) {
				$data = str_replace($badwords[$i], censorMSG($badwords[$i]), $data);
			}
		}
		
		return $data;
	}
	
	function censorMSG($data) {

		$replace = '';
		$rop = '*';

		for ($i = 1; $i < strlen($data); $i++) {
			$replace .= $rop;
		}

		$data = substr_replace($data, $replace, 1);
		return $data;
	}
	
	function load_smiles() {

		global $smcFunc, $listeners, $modSettings;

		$smiles = array();
			
		if (($smiles = cachegetData('smiless', 90)) == null) {
			
			if(isset($listeners['load_smiles']))
				$smiles =  hook('load_smiles', $smiles);
				
			$results = $smcFunc['db_query']('', '
				SELECT code, filename
				FROM {db_prefix}smileys', array()
			);

			while ($row = $smcFunc['db_fetch_assoc']($results)) {
				$smiles['code'][] = htmlspecialchars($row['code'], ENT_QUOTES);
				$smiles['file'][] = '<img src="' . $modSettings['smileys_url'] . '/' . $modSettings['smiley_sets_default'] . '/' . $row['filename'] . '">';
			}
			$smcFunc['db_free_result']($results);
				
			cacheputData('smiless', $smiles, 90);
		}

		return $smiles;
	}
	
	function closechat(){

		if(!empty($_SESSION['buddy_id'])){
			unset($_SESSION['buddy_id']);
		}
	}
	
	function mread($id) {

		global $smcFunc, $member_id, $buddy_id;

		if (isset($_REQUEST['_'])) {$read = $_REQUEST['_'];}
		
		if (!isset($read)) {$read = 1;}

		// Mark messages read.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}2sichat
			SET {db_prefix}2sichat.rd = {float:read}
			WHERE {db_prefix}2sichat.id = {int:ids} AND  {db_prefix}2sichat.to = {int:member_id} AND {db_prefix}2sichat.from = {int:buddy_id} AND {db_prefix}2sichat.rd = 0',
			array(
				'member_id' => $member_id,
				'buddy_id' => $buddy_id,
				'read' => $read,
				'ids' => $id,
			)
		);
		
		if (defined('loadOpt')) {doOptDBrec();}
	}
	
	/*if ($time > strtotime('-2 minutes'))
	{
		return 'Just now';
	}
	elseif ($time > strtotime('-15 minutes'))
	{
		return '' . floor((strtotime('now') - $time)/60) . 'minutes ago';
	}
	else*/
	function formatDateAgo($value){
		global $txt;
		
		$time = $value;
		$d = new \DateTime('@'.$value.'');

		$weekDays = $txt['bar_weekdays'];
		$months = $txt['bar_months'];

		if ($time > strtotime('today'))
		{
			return $txt['bar_display_today'].', ' . $d->format('G:i:a');
		}
		elseif ($time > strtotime('yesterday'))
		{
			return $txt['bar_display_yesterday'].', ' . $d->format('G:i:a');
		}
		elseif ($time > strtotime('this week'))
		{
			return $weekDays[$d->format('N') - 1] . ', ' . $d->format('G:i:a');
		}
		else
		{
			return $d->format('j') . ' ' . $months[$d->format('n') - 1] . ', ' . $d->format('G:i:a');
		}
	}
?>