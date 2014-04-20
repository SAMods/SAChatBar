<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
/*
	Since it has been wanted here it is, the chat template.
	Remember you can change the content of the divs but be aware
	that changing or removing ids or class attributes can have negitive
	effects on the chat.
*/
function chat_window_template() { //Main chat window, not the bar, the window you chat to your friends with, duh :P

	global $user_settings, $buddy_settings, $modSettings, $txt, $context;

	// The main chat window
	$data = '
	<div id="ch'.$buddy_settings['id_member'].'" class="chatboxhead">
	    <div class="chatboxtitle"><span id="session'.$buddy_settings['id_member'].'"></span>&nbsp;'.$buddy_settings['real_name'].'<span id="typeon'.$buddy_settings['id_member'].'"></span></div>
		    <div class="chatboxoptions">
			    <a href="javascript:void(0)" onclick="javascript:upDownchat(\''.$buddy_settings['id_member'].'\',\''.$buddy_settings['real_name'].'\')"><span id="slideup'.$buddy_settings['id_member'].'" class="slideup">&#x25B2;</span><span id="slidedown'.$buddy_settings['id_member'].'" class="slidedown">&#x25BC;</span></a> 
			    <a href="javascript:void(0)" onclick="javascript:xchat(\''.$buddy_settings['id_member'].'\')">X</a>
			</div>
			<br clear="all"/>
	</div>
	<div class="chatboxcontent" id="cmsg'.$buddy_settings['id_member'].'">';

	// Messages from previous chat session that have not been deleted yet, lets show them. :D
	if(!empty($context['msgs'])) {
		foreach ($context['msgs'] as $message) {
			if ($message['from'] == $user_settings['id_member']) { // Messages sent from me.	
				$data .=' 
				    <strong>'.$user_settings['real_name'].'</strong>
				    <div class="chatboxmsg_optionright">
				       <img width="15px" height="15px" src="'.$user_settings['avatar'].'" />
				    </div>
					<br clear="all"/>	
					<div class="chatboxmsg_container">	
						'.$message['msg'].'
					</div>';	
			} 
			else 
			{ // Messages sent by my buddy
				$data .='
				'.(!empty($modSettings['2sichat_e_last3min']) ? ''.(!empty($message['inactive']) ? '<div class="chatboxtime"><br />'.$txt['bar_sent_at'].' '.date('g:iA M dS', strtotime($message['sent'])).'</div><br />':'').'' : '').'
				    <strong>'.$buddy_settings['real_name'].' </strong>
				    <div class="chatboxmsg_optionright">
				        <img width="15px" height="15px" src="'.$buddy_settings['avatar'].'" />
				    </div>
					<br clear="all"/>
				   <div class="chatboxmsg_container_rec">
				       <div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
					       '.$message['msg'].'
					   </div></div>';	   
			}
			$data .='';
		 }	 
	}
	$data .='
	    </div>
	    <div class="chatboxinput" id="bddy'.$buddy_settings['id_member'].'">
	        <form id="mid_cont_form" action="javascript:void(0)" onsubmit="javascript:jsubmit(\''.$buddy_settings['id_member'].'\');" method="post">
	           <input type="text" name="msg'.$buddy_settings['id_member'].'" id="msg'.$buddy_settings['id_member'].'" style="width: 75%;" maxlength="255" />
			    <input type="button" onclick="javascript:jsubmit(\''.$buddy_settings['id_member'].'\'); return false;" value="'.$txt['bar_submitt_form'].'" />
		    </form>
	   </div>';
	
	return $data;
}

function chat_retmsg_template() { //When you recieve a message

	global $buddy_settings, $context;

	// This is where someone sends you a message when your online.
    if(!empty($context['msgs'])) {
		foreach ($context['msgs'] as $message) {
			$data =' <div class="chatboxtime" id="sent'.$message['id'].'"></div>
			    <strong>'.$buddy_settings['real_name'].' </strong>
			    <div class="chatboxmsg_optionright">
				    <img width="15px" height="15px" src="'.$buddy_settings['avatar'].'" />
				</div>
				<br clear="all"/>
			    <div class="chatboxmsg_container_rec">
				    <div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
					    '.$message['msg'].'
				    </div>
			   </div>';
	    }
	    return $data;
	}
}

function chat_savemsg_template() { //When you send a message

	global $user_settings, $context;

	// This is the html response when you send a message.
	$data ='
	    <strong>'.$user_settings['real_name'].' </strong>
	    <div class="chatboxmsg_optionright">
	        <img width="15px" height="15px" src="'.$user_settings['avatar'].'" />
		</div>
		<br clear="all"/>
	    <div class="chatboxmsg_container">
		    '.$context['msgs'].'
		</div>';
		
	return $data;
}

function chat_bar_template() { //Chat bar template for logged in users, not guest.

	global $debug_load, $themeurl, $modSettings, $load_btime, $txt, $db_count;

	$data= '
	    '.(empty($modSettings['2sichat_dis_list']) ? ' <div class="chatBar_content">':'<div class="chatBar_content_other">').'';
			
	$data .= '
	    <a href="javascript:void(0)" onclick="javascript:updatebar(true);">
		    <img id="test" src="'.$themeurl.'/images/arrow_refresh.png" width="17" height="17" alt="" border="0">
		</a>&nbsp;
			
		<a href="javascript:void(0)" onclick="javascript:showhide(\'extra\');">
			<img id="extraimg" src="'.$themeurl.'/images/control_eject_blue.png" width="17" height="17" alt="" border="0">
		</a>';
        
		if(empty($modSettings['2sichat_dis_list'])){
		   
		   $data .=' &nbsp;<a href="javascript:void(0)" onclick="javascript:chatSnd();">';
		   
		   if(!empty($_COOKIE[$modSettings['2sichat_cookie_name']."_chatSnd"])){
		   
		       $data .= '<img id="chat_Snd" src='.$themeurl.'/images/mute2.png />';
		   
		   }
		   else{
		   
		       $data .= '<img id="chat_Snd" src='.$themeurl.'/images/mute1.png />';
		   }
		   
		   $data .= '</a>';
		   
		}
		
	$data .= '
	    </div>';
				
	$data.= '
	    <div class="langCont">
		    <div id="2siTranslate"></div>
		</div>
		
		'.(!empty($modSettings['2sichat_dis_list']) ? '': '
		    <a href="javascript:void(0)" onclick="javascript:showhide(\'friends\');">
			    <img src="'.$themeurl.'/images/balloon.png" alt="{}" border="0"><strong>'.$txt['whos_on'].' <span id="cfriends"></span></strong>
		    </a>
		');
	
	$data .='
	    &nbsp;&nbsp;<span id="minchats"></span>';
		 
	if($debug_load){
        $data .='&nbsp;&nbsp;<span style="color: #f00;">Bar loaded in, '.$load_btime.' seconds with '.$db_count.' queries</span>';
	}

	return $data;
}

function chat_extra_template() { 

	global $txt, $member_id, $modSettings, $context, $themeurl;
     
	 $data = ' 
	     <div class="extraboxhead">
	         <div class="extraboxtitle"></div>
		         <div class="extraboxoptions">
			         <a href="javascript:void(0)" onclick="javascript:showhide(\'extra\')">
					     X
					 </a>
			     </div>
			     <br clear="all"/>
	     </div>';
			
	 $data .= '
	     <div class="extraboxcontent">';	
		     if (!empty($member_id)) {
                 $data.= '<br /><a href="?action=profile;area=theme;u='.$member_id.'#chatbar"><strong>'.$txt['bar_setings'].'</strong></a>';
 		         $data.= '<br /><a href="?action=profile;area=lists;u='.$member_id.'"><strong>'.$txt['bil'].'</strong></a>';
		         $data.= '<hr />';
		     }
				if (!empty($modSettings['2sichat_ico_adthis']) || !empty($modSettings['2sichat_ico_gplus']) || !empty($modSettings['2sichat_ico_myspace']) || !empty($modSettings['2sichat_ico_twit']) || !empty($modSettings['2sichat_ico_fb'])) {
				    $ssocial=$txt['bar_social'];
				}
				else{
				 $ssocial= '';
				}
		     $data.= '
				  '.($ssocial ? '<strong>'.$ssocial.'</strong><br /><br />' :'').'
			    
			     '.($modSettings['2sichat_ico_adthis'] ? '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-503f263237ff99da">
			     <img src="'.$themeurl.'/images/add-this.png" width="17" height="17" alt="Bookmark and Share" style="border:0"/> <strong>'.$txt['addthis'].'</strong></a><br />':'').'
			     '.($modSettings['2sichat_ico_gplus'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'gplus\');">
		         <img src="'.$themeurl.'/images/gplus.png" width="17" height="17" alt="'.$txt['facebook'].'" border="0"> <strong>'.$txt['gplus1'].'</strong></a><br />':'').'
			     '.($modSettings['2sichat_ico_myspace'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'myspace\');">
			     <img src="'.$themeurl.'/images/myspace.png" width="17" height="17" alt="'.$txt['myspace'].'" border="0"> <strong>'.$txt['myspace1'].'</strong></a><br />':'').'
			     '.($modSettings['2sichat_ico_twit'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'twitter\');">
			     <img src="'.$themeurl.'/images/twitter.png" width="17" height="17" alt="'.$txt['twitter'].'" border="0"> <strong>'.$txt['twitter1'].'</strong></a><br />':'').'
			     '.($modSettings['2sichat_ico_fb'] ? '<a href="javascript:void(0)" onclick="javascript:getSocial(\'facebook\');">
			     <img src="'.$themeurl.'/images/facebook.png" width="17" height="17" alt="'.$txt['facebook'].'" border="0"> <strong>'.$txt['facebook1'].'</strong></a><br />':'').'
		    '.($ssocial ? '<hr />' :'').' ';
				
			if(!empty($context['gadgetslink'])) {
                $data.= '
			        <strong>'.$txt['bar_links'].'</strong><br /><br />';
			            foreach ($context['gadgetslink'] as $link) {
			                if($link['image']){
				                $data.= '
			                        <a href="'.$link['url'].'" '.(!empty($link['newwin']) ? 'target="blank"' :'').'><img src="'.$link['image'].'" alt="'.$link['title'].'" /> 
									    <strong>'.$link['title'].'</strong>
									</a><br />';
			                }
			            }
			 }
				
			 if(!empty($context['gadgets'])) {
		         $data.= '
				     <hr /><strong>'.$txt['bar_gadgets'] .'</strong><br /><br />';
			             foreach ($context['gadgets'] as $gadget) {
				             $data.= '
			                     <a href="javascript:void(0)" onclick="javascript:openGadget(\''.$gadget['id'].'\');showhide(\'extra\');return false;">
							         <strong>'.$gadget['title'].'</strong>
								 </a><br />';
		                 }
		    }
			
	  /*$data .= '
	      <div class="extraboxbottom">
		      Powerd by SA Chat &copy; 2010 - 2014 <a href="https://www.facebook.com/devsamods">SA Mods</a>
		  </div>';*/
			  
	 $data .= '
	          </div>';
		
	return $data;
}

function buddy_list_template() { //The buddy list.

	global $context, $txt, $admin, $modSettings;

	$data = ' 
	     <div class="buddyboxhead">
	         <div class="buddyboxtitle"></div>
		         <div class="buddyboxoptions">
			         <a href="javascript:void(0)" onclick="javascript:showhide(\'friends\')">
					     X
					 </a>
			     </div>
			     <br clear="all"/>
	     </div>';
			
	 $data .= '
	     <div class="buddyboxcontent">';
		     
			 if(!empty($admin['is_admin'])){		    
				$data .='<input type="button" onclick="javascript:snooper(); return false;" value="'.$txt['bar_admin_snoop'].'" />'; 
				
				if(!empty($_COOKIE[$modSettings['2sichat_cookie_name']."_chatSnoop"])){
				    $data .= ''.$txt['bar_admin_snoop_on'].'<hr />';
				}
				else{
				    $data .= ''.$txt['bar_admin_snoop_off'].'<hr />';
				}
			 }
			  
			 if(!empty($context['friends'])) {
				foreach ($context['friends'] as $buddy) {
			         $data.= '
				         <a  href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');showhide(\'friends\');return false;">
				             <img width="20px" height="20px" src="'.$buddy['avatar'].'" />&nbsp;<strong>'.$buddy['real_name'].'</strong>&nbsp;<span class="'.($buddy['session']?'green':'red').'">*</span>
				         </a><br />';
				 }
			}
			
	    $data .= '
	        </div>';
			  
	return $data;
}

function guest_bar_template() { //Well guest can't access everything.

	global $load_btime, $debug_load, $db_count, $themeurl, $txt;

    $data = '
	    <div class="chatBar_content_right">
		    <a href="javascript:void(0)" onclick="javascript:showhide(\'extra\');">
			    <img id="extraimg" src="'.$themeurl.'/images/control_eject_blue.png" width="17" height="17" alt="" border="0">
		    </a>
		</div>';
	    
	$data.= '
	    <div class="langCont">
		    <div id="2siTranslate"></div>
		</div>
		
		<div class="chatBar_content_left">
		
		'.$txt['guest_msg'].'';
		
    if($debug_load){
        $data .='&nbsp;&nbsp;<span style="color: #f00;">Bar loaded in, '.$load_btime.' seconds with '.$db_count.' queries</span>';
	}
	
	$data .='</div>';

	return $data;
}

function gadget_template() {
	
	global $boardurl, $context;

	$data ='
	    <div class="gadgetboxhead">
	        <div class="gadgetboxtitle">'.$context['gadget']['title'].'</div>
		        <div class="gadgetboxoptions">
			        <a href="javascript:void(0)" onclick="javascript:closeGadget(\''.$context['gadget']['id'].'\'); return false;">
                        X
					</a>
			</div>
			<br clear="all"/>
	    </div>';
		
	$data .='
	    <div class="gadgetboxcontent">
		    <object id="gadget'.$context['gadget']['id'].'" type="text/html" data="'.(substr($context['gadget']['url'], 0, 4) == 'http' ? $context['gadget']['url']:$boardurl.'/sachat/index.php?gid='.$context['gadget']['id'].'&src=true').'" width="'.$context['gadget']['width'].'" height="'.$context['gadget']['height'].'" style"overflow:hidden;" hspace="0" vspace="0"></object>
		<div>';
	
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
	<body >
	'.$context['gadget']['url'].'
	</body>
</html>';
	return $data;
}
?>