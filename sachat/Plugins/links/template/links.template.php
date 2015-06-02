<?php
if (!defined('SMF'))
	die('No direct access...');
	
function template_display_link(&$data){
	global $context, $txt;
	
	if(!empty($context['gadgetslink'])) {
		$data.= '<br />
		<div class="extrasettings">'.$txt['bar_links'].':</div>';
			foreach ($context['gadgetslink'] as $link) {
				if($link['image']){
					$data.= '
						<a href="'.$link['url'].'" '.(!empty($link['newwin']) ? 'target="blank"' :'').'>
							<img src="'.$link['image'].'" width="18" height="18" alt="'.$link['title'].'" title="'.$link['title'].'"/> 	
						</a>&nbsp;';
				}
			}
		$data.= '<br />';
	}
	
	return $data;
}
?>