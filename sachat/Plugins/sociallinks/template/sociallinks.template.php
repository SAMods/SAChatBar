<?php
if (!defined('SMF'))
	die('No direct access...');

function load_soc_js(&$data){
	
	$data .='
		$sachat.getScript(\'http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-503f263237ff99da\');
			
		function getSocial (social) {
			if (social == \'myspace\') {
				pupUP("http://www.myspace.com/Modules/PostTo/Pages/default.aspx?c="+window.location+"&t="+document.documentElement.getElementsByTagName("TITLE")[0].innerHTML);
			}
			if (social == \'twitter\') {
				pupUP("http://twitter.com/home?status="+document.documentElement.getElementsByTagName("TITLE")[0].innerHTML+" @ "+window.location);
			}
			if (social == \'facebook\') {
				pupUP("http://www.facebook.com/sharer.php?t="+document.documentElement.getElementsByTagName("TITLE")[0].innerHTML+"&u="+window.location);
			}
			if (social == \'gplus\') {
				pupUP("https://plusone.google.com/_/+1/confirm?hl=en-US&url="+window.location);
			}
		}
		function pupUP(url) {
			newwindow=window.open(url,\'pupUP\',\'height=400,width=550,top=200,left=200,toolbar=0,location=0,directories=0,status=0,menubar=0,statusbar=0\');
			if (window.focus) {newwindow.focus()}
			return false;
		}
	';
	return $data;
}

function template_display_soc_link(&$data){
	
	global $txt, $modSettings;
	
	if (!empty($modSettings['2sichat_ico_adthis']) || !empty($modSettings['2sichat_ico_gplus']) || !empty($modSettings['2sichat_ico_myspace']) || !empty($modSettings['2sichat_ico_twit']) || !empty($modSettings['2sichat_ico_fb']))
		$ssocial=$txt['bar_social'];
	else
		$ssocial= '';
					
	$soc = '
		'.($ssocial ? '<br /><div class="extrasettings">'.$ssocial.':</div>' :'').'
					
		'.($modSettings['2sichat_ico_adthis'] ? '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-503f263237ff99da">
		<img src="'.LoadImage('add-this.png').'" width="20" height="20" alt="" style="border:0"/></a>&nbsp;':'').'
		'.($modSettings['2sichat_ico_gplus'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'gplus\');">
		<img src="'.LoadImage('gplus.png').'" width="20" height="20" alt="'.$txt['gplus'].'" title="'.$txt['gplus'].'" border="0"></a>&nbsp;':'').'
		'.($modSettings['2sichat_ico_myspace'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'myspace\');">
		<img src="'.LoadImage('myspace.png').'" width="20" height="20" alt="'.$txt['myspace'].'" title="'.$txt['myspace'].'" border="0"></a>&nbsp;':'').'
		'.($modSettings['2sichat_ico_twit'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'twitter\');">
		<img src="'.LoadImage('twitter.png').'" width="20" height="20" alt="'.$txt['twitter'].'" title="'.$txt['twitter'].'" border="0"></a>&nbsp;':'').'
		'.($modSettings['2sichat_ico_fb'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'facebook\');">
		<img src="'.LoadImage('facebook.png').'" width="20" height="20" alt="'.$txt['facebook'].'"  title="'.$txt['facebook'].'" border="0"></a><br />':'').'
	';
	
	$data = str_replace('</select><br />','</select><br />'.$soc,$data);
}
?>