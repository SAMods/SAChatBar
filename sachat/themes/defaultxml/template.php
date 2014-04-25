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
				'.(!empty($message['inactive']) ? '<div class="chatboxtime"><br />'.$txt['bar_sent_at'].' '.date('g:iA M dS', strtotime($message['sent'])).'</div><br />':'').'
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

	global $debug_load, $themeurl, $modSettings, $user_info, $load_btime, $txt, $db_count;

	$data= '<div class="chatBar_content"></div>';
	
     $data .='
		<a href="javascript:void(0)" onclick="javascript:showhide(\'friends\');">
			<img src="'.$themeurl.'/images/balloon.png" alt="{}" border="0"><strong>'.$txt['whos_on'].' <span id="cfriends"></span></strong>
		</a>
	';
			
	if($debug_load){
        $data .='&nbsp;&nbsp;<span style="color: #f00;">Bar loaded in, '.$load_btime.' seconds with '.$db_count.' queries</span>';
	}

	return $data;
}

function guest_bar_template() { //Chat bar template for logged in users, not guest.

	global $debug_load, $themeurl, $modSettings, $load_btime, $txt, $db_count;

	$data= '<div class="chatBar_content">Please Login or register</div>';
				
	if($debug_load){
        $data .='&nbsp;&nbsp;<span style="color: #f00;">Bar loaded in, '.$load_btime.' seconds with '.$db_count.' queries</span>';
	}

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
?>