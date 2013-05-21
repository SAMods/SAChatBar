<?php
/*
	Since it has been wanted here it is, the chat template.
	Remember you can change the content of the divs but be aware
	that changing or removing ids or class attributes can have negitive
	effects on the chat.
*/
function chat_window_template() { //Main chat window, not the bar, the window you chat to your friends with, duh :P

	global $user_settings, $buddy_settings, $boardurl, $context, $themeurl;

	// The main chat window
	$data ='
			<div id="top_container" onMouseOver="javascript:this.style.cursor=\'move\';">
				<div id="top_cont_x">
					<a href="javascript:void(0)" onclick="javascript:xchat(\''.$buddy_settings['id_member'].'\'); return false;" onMouseOver="document.rollover'.$buddy_settings['id_member'].'.src=image2.src" onMouseOut="document.rollover'.$buddy_settings['id_member'].'.src=image1.src">
						<img name="rollover'.$buddy_settings['id_member'].'" src="'.$themeurl.'/images/x_inactive.png" border="0" alt="X" />
					</a>
					<a href="javascript:void(0)" onclick="javascript:minchat(\''.$buddy_settings['id_member'].'\',\''.$buddy_settings['real_name'].'\'); return false;">
					&nbsp;<img name="rollover'.$buddy_settings['id_member'].'" src="'.$themeurl.'/images/minimize.png" border="0" alt="-" />
					</a>
				</div>
				<div id="top_cont_avatar">
					<img align="left" width="40px" height="40px" src="'.$buddy_settings['avatar'].'" /><br />&nbsp;<span class="'.($buddy_settings['session']?'green':'red').'">*&nbsp;</span><span class="white">'.$buddy_settings['real_name'].'</span>
				</div>
			</div>
			<div id="mid_container">
				<form id="mid_cont_form" action="javascript:void(0)" onsubmit="javascript:jsubmit(\''.$buddy_settings['id_member'].'\');" method="post">
					<input type="text" name="msg'.$buddy_settings['id_member'].'" id="msg'.$buddy_settings['id_member'].'" style="width: 80%;" maxlength="255" />
					<input type="button" onclick="javascript:jsubmit(\''.$buddy_settings['id_member'].'\'); return false;" value="Send" />
				</form>
			</div>
			<div class="bottop_container"></div>
			<div class="msg_container">
				<div id="cmsg'.$buddy_settings['id_member'].'" class="msg_container2">';
	// Messages from previous chat session that have not been deleted yet, lets show them. :D
	if(!empty($context['msgs'])) {
		foreach ($context['msgs'] as $message) {
			if ($message['from'] == $user_settings['id_member']) { // Messages sent from me.
				$data .='
					<div class="msg_container3">
						<img width="20px" height="20px" src="'.$user_settings['avatar'].'" />
						<strong>'.$user_settings['real_name'].': </strong>
						'.$message['msg'].'
					</div>';
			} else { // Messages sent by my buddy
				$data .='
					<div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
						<img width="20px" height="20px" src="'.$buddy_settings['avatar'].'" />
						<strong>'.$buddy_settings['real_name'].': </strong>
						'.$message['msg'].'
					</div>';
			}
		 }
	}
	$data .='
				</div>
			</div>
			<div class="bot_container">
			</div>
			';
	return $data;
}

function chat_retmsg_template() { //When you recieve a message

	global $buddy_settings, $context;

	// This is where someone sends you a message when your online.
    if(!empty($context['msgs'])) {
		foreach ($context['msgs'] as $message) {
			$data ='
				<div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
					<img width="20px" height="20px" src="'.$buddy_settings['avatar'].'" />
					<strong>'.$buddy_settings['real_name'].': </strong>
					'.$message['msg'].'
				</div>';
		}
	return $data;
	}
}

function chat_savemsg_template() { //When you send a message

	global $user_settings, $context;

	// This is the html response when you send a message.
	$data ='<div class="msg_container3"">
					<img width="20px" height="20px" src="'.$user_settings['avatar'].'" />
					<strong>'.$user_settings['real_name'].': </strong>
					'.$context['msgs'].'
				</div>';
	return $data;
}

function chat_bar_template() { //Chat bar template for logged in users, not guest.

	global $boardurl, $themeurl, $modSettings, $context, $OnCount, $txt;

	$data= '
	    '.(empty($modSettings['2sichat_dis_list']) ? ' <div style="float: right; padding-right: 30px; padding-top: 1px;">':'
			 <div style="float: right; padding-right: 30px; padding-top: 3px;">').'';
			
			$data .= '<a class="white" href="javascript:void(0)" onclick="javascript:showhide(\'extra\');">
			<img id="extraimg" src="'.$themeurl.'/images/control_eject_blue.png" width="17" height="17" alt="Extra" border="0">
		    </a>';

				$data.= ''.(empty($modSettings['2sichat_dis_list']) ? ' &nbsp;<a href="javascript:void(0)" onclick="javascript:chatSnd();">
				<img id="chat_Snd" src="'.(!empty($_COOKIE["chatSnd"]) ? $themeurl.'/images/mute2.png':$themeurl.'/images/mute1.png').'" />
				</a>':'').'</div>';
		
		$data.= '
		<div class="langCont">
			<div id="2siTranslate"></div>
		</div>
		'.(!empty($modSettings['2sichat_dis_list']) ? '':'
		<a class="white" href="javascript:void(0)" onclick="javascript:showhide(\'friends\');">
			<img src="'.$themeurl.'/images/balloon.png" alt="{}" border="0"><strong>'.$txt['whos_on'].' (<span id="cfriends">'.$OnCount.'</span>)</strong>
		</a> ');
         
		$data .='&nbsp;&nbsp;<span id="minchats"></span>';
		
	return $data;
}

function chat_extra_template() { 

	global $txt, $member_id, $scripturl, $options, $modSettings, $context, $themeurl;

	$data ='
		<div id="extra_top">
			<div id="extra_x">
				<a href="javascript:void(0)" onclick="javascript:showhide(\'extra\');" onMouseOver="document.fx.src=image2.src" onMouseOut="document.fx.src=image1.src">
					<img name="fx" src="'.$themeurl.'/images/x_inactive.png" border="0" alt="X" />
				</a>
			</div>
		</div>
		<div id="extra_bottom">';
		if (!empty($member_id)) {
          $data.= '<br /><a href="?action=profile;area=theme;u='.$member_id.'#chatbar"><strong>'.$txt['bar_setings'].'</strong></a>';
		 
		  if(!empty($options['show_cbar_buddys'])){
 		  $data.= '<br /><a href="?action=profile;area=lists;u='.$member_id.'"><strong>'.$txt['bil'].'</strong></a>';
		  }
		  
		  $data.= '<hr />';
		  }
			$data.= '<strong>'.$txt['bar_social'].'</strong><br /><br />
			'.($modSettings['2sichat_ico_adthis'] ? '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-503f263237ff99da">
			<img src="'.$themeurl.'/images/add-this.png" width="17" height="17" alt="Bookmark and Share" style="border:0"/> <strong>'.$txt['addthis'].'</strong></a><br />':'').'
			'.($modSettings['2sichat_ico_gplus'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'gplus\');">
				<img src="'.$themeurl.'/images/gplus.png" width="17" height="17" alt="'.$txt['facebook'].'" border="0"> <strong>'.$txt['gplus1'].'</strong></a><br />':'').'
			'.($modSettings['2sichat_ico_myspace'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'myspace\');">
				<img src="'.$themeurl.'/images/myspace.png" width="17" height="17" alt="'.$txt['myspace'].'" border="0"> <strong>'.$txt['myspace1'].'</strong></a><br />':'').'
			'.($modSettings['2sichat_ico_twit'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'twitter\');">
				<img src="'.$themeurl.'/images/twitter.png" width="17" height="17" alt="'.$txt['twitter'].'" border="0"> <strong>'.$txt['twitter1'].'</strong></a><br />':'').'
			'.($modSettings['2sichat_ico_fb'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'facebook\');">
				<img src="'.$themeurl.'/images/facebook.png" width="17" height="17" alt="'.$txt['facebook'].'" border="0"> <strong>'.$txt['facebook1'].'</strong></a><br />':'').'<hr />';
		
		     
			 
			 if(!empty($context['gadgetslink'])) {
             $data.= '<strong>'.$txt['bar_links'].'</strong><br /><br />';
			foreach ($context['gadgetslink'] as $link) {
			if($link['image']){
				$data.= '
			<a href="'.$link['url'].'" '.(!empty($link['newwin']) ? 'target="blank"' :'').'><img src="'.$link['image'].'" alt="'.$link['title'].'" /> <strong>'.$link['title'].'</strong></a><br />';
			  }
			}

			}
			if(!empty($context['gadgets'])) {
		 $data.= '<hr /><strong>'.$txt['bar_gadgets'] .'</strong><br /><br />';
			foreach ($context['gadgets'] as $gadget) {
				$data.= '
				
			<a href="javascript:void(0)" onclick="javascript:openGadget(\''.$gadget['id'].'\');"><strong>'.$gadget['title'].'</strong></a><br />';
		
		  }
		}
	
	$data.='
			<br /><br />
		</div>';
	return $data;
}

function buddy_list_template() { //The buddy list.

	global $context, $user_settings, $themeurl;

	$data ='
		<div id="friends_top">
			<div id="friends_x">
				<a href="javascript:void(0)" onclick="javascript:showhide(\'friends\');" onMouseOver="document.fx.src=image2.src" onMouseOut="document.fx.src=image1.src">
					<img name="fx" src="'.$themeurl.'/images/x_inactive.png" border="0" alt="X" />
				</a>
			</div>
		</div>
		<div id="friends_bottom">';

     if(!empty($context['friends'])) {
		foreach ($context['friends'] as $buddy) {
			$data.= '
				<a class="'.($buddy['session']?'green':'red').'" href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');showhide(\'friends\');return false;">
				<img width="20px" height="20px" src="'.$user_settings['avatar'].'" />&nbsp;<strong>'.$buddy['real_name'].'</strong>&nbsp;<span class="'.($buddy['session']?'green':'red').'">*</span>
				</a><br />';
		}
	}
	$data.="
			<br /><br />
		</div>";
	return $data;
}

function guest_bar_template() { //Well guest can't access everything.

	global $boardurl, $themeurl, $modSettings, $txt, $context;

			$data = '
			<div style="float: right; padding-right: 30px; padding-top: 3px;">
			<a class="white" href="javascript:void(0)" onclick="javascript:showhide(\'extra\');">
			<img id="extraimg" src="'.$themeurl.'/images/control_eject_blue.png" width="17" height="17" alt="Extra" border="0">
		    </a></div>';
	    $data.= '
		<div class="langCont">
			<div id="2siTranslate"></div>
		</div>
		<div style="float: left; padding-left: 5px; padding-top: 3px;">
		<span class="white">
		'.$txt['guest_msg'].'
		</span>';
	    
		$data .='</div>';

	return $data;
}

function gadget_template() {
	
	global $boardurl, $themeurl, $context;

	$data ='
			<div id="top_container" style="position:relative;top:7px;right:2px;" onMouseOver="javascript:this.style.cursor=\'move\';">
				<div id="top_cont_x">
					<a href="javascript:void(0)" onclick="javascript:closeGadget(\''.$context['gadget']['id'].'\'); return false;" onMouseOver="document.rolloverGad'.$context['gadget']['id'].'.src=image2.src" onMouseOut="document.rolloverGad'.$context['gadget']['id'].'.src=image1.src">
						<img name="rolloverGad'.$context['gadget']['id'].'" src="'.$themeurl.'/images/x_inactive.png" border="0" alt="X" />
					</a>
				</div>
				<div id="top_cont_gadget">'.$context['gadget']['title'].'</div>
			</div>
			<object id="gadget'.$context['gadget']['id'].'" type="text/html" data="'.(substr($context['gadget']['url'], 0, 4) == 'http' ? $context['gadget']['url']:$boardurl.'/sachat/index.php?gid='.$context['gadget']['id'].'&src=true').'" width="'.$context['gadget']['width'].'" height="'.$context['gadget']['height'].'" style"overflow:hidden;" hspace="0" vspace="0"></object>';
	return $data;
}

function gadgetObject_template() {

	global $context;

	$data ='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<title>'.$context['gadget']['title'].'</title>
	</head>
	<body style="background-color: #f5f5f5;padding:1;margin:1;">
	'.$context['gadget']['url'].'
	</body>
</html>';
	return $data;
}
?>