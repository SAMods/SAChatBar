<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	
	function chat_test_template(){
			
		global $dirArray, $txt, $cookiename, $modSettings, $indexCount;

		if (empty($_POST['satesttheme']))
			$_POST['satesttheme'] = 'default';
	
		$data = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
		<html xmlns="http://www.w3.org/1999/xhtml"> 
			<head> 
				<style type="text/css">
					a:link, a:visited{color: #346;text-decoration: none;}
					a:hover{text-decoration: underline;cursor: pointer;}
					body{font: 78%/130% "Verdana", "Arial", "Helvetica", sans-serif;margin: 0;padding: 0px 0;}
					#container { background: #ffffff; width: 100%; line-height: 150%; margin: 0; }
					#header,#footer { color: white; background-color: #a1b2c5; border: 1px solid #000000; clear: both; padding: .5em; }
					#leftbar { background: #ffffff; float: left; width: 180px; margin: 0; padding: 1em; }
					#leftbar a { color: #000000; text-decoration: underline; }
					#content { margin-left: 190px; padding: 1em; }
				</style>
				<title>'.$txt['2sichat_testpage'] .'</title> 
				<script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=head&amp;theme=' . $_POST['satesttheme'] . '"></script>
			</head>
		<body>';
		
		$data .= '
			<script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=body&amp;theme=' . $_POST['satesttheme'] . '"></script>	
			
			<div id="container">
				<div id="header">
					<h1 class="header">'.$txt['2sichat_testpage'].'</h1>
				</div>
				<div id="leftbar">
					<strong>Useful Links</strong>
						<br /><a href="https://github.com/SAMods/SAChatBar/issues?state=open">'.$txt['2sichat_testpage8'].'</a>
						<br /><a href="https://github.com/SAMods/SAChatBar/wiki">'.$txt['2sichat_testpage9'].'</a>
						<br /><a href="http://www.simplemachines.org/community/index.php?topic=391961.0">'.$txt['2sichat_testpage10'].'</a>
						<br /><a href="http://custom.simplemachines.org/mods/index.php?mod=2534">'.$txt['2sichat_testpage11'].'</a>
						<br /><br />
				</div>

				<div id="content">
					<h2>'.$txt['2sichat_testpage1'].'</h2>
					<p>'.$txt['2sichat_testpage2'].'</p>';

					$data .= '
						<form action="http://' . $_SERVER['HTTP_HOST'] . '' . $_SERVER['PHP_SELF'] . '?home" method="post">
							<strong>'.$txt['2sichat_testpage7'].':</strong>
							<select name="satesttheme">';
								for ($index = 0; $index < $indexCount; $index++) {
									$themeSelect = $_POST['satesttheme'] == $dirArray[$index] ? 'selected="selected"' : '';
									if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php") { // don't list hidden files
										$data .= '  <option value="' . $dirArray[$index] . '" '.$themeSelect.'>' . $dirArray[$index] . '</option>';
									}
								}
				
					$data .= '
						</select> 
						<input type="submit" value="'.$txt['2sichat_testpage3'].'" />
					</form>';
					$data.= '
						<h2>'.$txt['2sichat_testpage4'].'</h2>
					
						<p><strong>'.$txt['2sichat_testpage5'].'</strong></p>
						<textarea name="sahead" cols="60" rows="3" readonly="readonly"><script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=head&amp;theme=' . $_POST['satesttheme'] . '"></script></textarea>
					
						<p><strong>'.$txt['2sichat_testpage6'].'</strong></p>
						<textarea name="sabody" cols="60" rows="3" readonly="readonly"><script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=body&amp;theme=' . $_POST['satesttheme'] . '"></script></textarea><br />';
						
						foreach($_COOKIE as $name => $value) {
						
							if(strstr($name, $modSettings['2sichat_cookie_name'])){
							
								$data .='<br />'.$name.' => '.$value.'';
							
							}
						
						}
					$data .= '
				</div>';

			$data .= '
			</div>
		</body> 
		</html>';
		
		return $data;
	}
	
	function chat_window_template() { //Main chat window, not the bar, the window you chat to your friends with, duh :P

		global $user_settings, $buddy_settings, $modSettings, $txt, $context;

		// The main chat window
		$data = '
		<div id="ch'.$buddy_settings['id_member'].'" class="chatboxhead">
			<div class="chatboxtitle">
				<span id="session'.$buddy_settings['id_member'].'"></span>&nbsp;
				<span id="new'.$buddy_settings['id_member'].'" class="chatboxmsg_new"></span>&nbsp;
				
				<span class="chatboxtitlehead">
					<a href="javascript:void(0)" onclick="javascript:upDownchat(\''.$buddy_settings['id_member'].'\')">'.$buddy_settings['real_name'].'</a>
				</span>
				<span id="typeon'.$buddy_settings['id_member'].'"></span>
			<br clear="all"/></div>
				<div class="chatboxoptions">';
					$data.= '
						<a href="javascript:void(0)" onclick="javascript:xchat(\''.$buddy_settings['id_member'].'\')">X</a>';
					
				$data.= '</div>
				<br clear="all"/>
		</div>';
		
		$data .='<div class="chatboxcontent" id="cmsg'.$buddy_settings['id_member'].'">';

		// Messages from previous chat session that have not been deleted yet, lets show them. :D
		if(!empty($context['msgs'])) {
			foreach ($context['msgs'] as $message) {
				
					if ($message['from'] == $user_settings['id_member']) { // Messages sent from me.	
					
						$data .=' 
							<div title="'.formatDateAgo(strtotime($message['sent'])).'" class="chat_bubble_you">	
								'.$message['msg'].'
							</div><br clear="all"/>';
					} 
					else 
					{ // Messages sent by my buddy
						$data .=''.(!empty($modSettings['2sichat_e_last3min']) ? ''.(!empty($message['inactive']) ? '<div class="chatboxtime"><br />'.$txt['bar_sent_at'].' '.formatDateAgo(strtotime($message['sent'])).'</div><br />':'').'' : '').'';	
						
						$data .='  <div title="'.formatDateAgo(strtotime($message['sent'])).'" class="chat_bubble_msg">
							<div class="chatboxmsg_avatar ">
								<img width="30px" height="30px" title="'.$buddy_settings['real_name'].'" alt="'.$buddy_settings['real_name'].'" src="'.$buddy_settings['avatar'].'" />
							</div>
							<div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
								'.$message['msg'].'
							</div>
						</div><br clear="all"/>';
					}
				
				$data .='<br />';
			 }	 
		}
		$data .='
			</div>
			<div class="chatboxinput" id="bddy'.$buddy_settings['id_member'].'">
				<form id="bdy'.$buddy_settings['id_member'].'" action="javascript:void(0)" onsubmit="javascript:jsubmit(\''.$buddy_settings['id_member'].'\');" method="post">
				   <input type="text" name="msg'.$buddy_settings['id_member'].'" id="msg'.$buddy_settings['id_member'].'" />
				</form>
		   </div>';
		
		return $data;
	}

	function chat_update_template() { //Main chat window, not the bar, the window you chat to your friends with, duh :P

		global $user_settings, $data, $buddy_settings, $modSettings, $txt, $context;

		// Messages from previous chat session that have not been deleted yet, lets show them. :D
		if(!empty($context['msgs'])) {
			foreach ($context['msgs'] as $message) {
					if ($message['from'] == $user_settings['id_member']) { // Messages sent from me.	
						$data .=' 
							<div title="'.formatDateAgo(strtotime($message['sent'])).'" class="chat_bubble_you">	
								'.$message['msg'].'
							</div><br clear="all"/>';
					} 
					else 
					{ // Messages sent by my buddy
						$data .=''.(!empty($modSettings['2sichat_e_last3min']) ? ''.(!empty($message['inactive']) ? '<div class="chatboxtime"><br />'.$txt['bar_sent_at'].' '.formatDateAgo(strtotime($message['sent'])).'</div><br />':'').'' : '').'';	
						
						$data .='  <div title="'.formatDateAgo(strtotime($message['sent'])).'" class="chat_bubble_msg">
							<div class="chatboxmsg_avatar ">
								<img width="30px" height="30px" title="'.$buddy_settings['real_name'].'" alt="'.$buddy_settings['real_name'].'" src="'.$buddy_settings['avatar'].'" />
							</div>
							<div id="u'.$buddy_settings['id_member'].'i'.$message['id'].'">
								'.$message['msg'].'
							</div>
						</div><br clear="all"/>';
						
					}
				$data .='<br />';
			 }	 
		}
		
		return $data;
	}
	
	function chat_info_template() { //When you send a message

		global $context;

		$data = '<br clear="all"/>
			<div class="group_chatboxmsg_container_online">'.$context['msgs'].'</div><br clear="all"/>';
			
		return $data;
	}
	
	function chat_savemsg_template() { //When you send a message

		global $context;

		// This is the html response when you send a message.
		$data ='
			<br clear="all"/>
			<div title="'.formatDateAgo(time()).'" class="chat_bubble_you">
				'.$context['msgs'].'
			</div><br clear="all"/>';
			
		return $data;
	}
	
	function chat_bar_template() { //Chat bar template for logged in users, not guest.

		global $modSettings, $txt;
		$data = '
			<div id="chattools_containter" class="chat_tools_containter">
				<div class="chatBar_content">
					<img id="opencog" src="'.LoadImage('cog.png').'" alt="'.$txt['bar_tools1'] .'" title="'.$txt['bar_tools1'] .'" width="17" height="17" alt="" border="0">
				</div>
			</div>';
		$data .= '
			<div id="chatcollapse_containter" class="chat_collapse_containter">
				<div class="chatBar_content">
					<img id="hideimg" src="'.LoadImage('world_on.png').'" alt="'.$txt['bar_hideChat'].'" title="'.$txt['bar_hideChat'].'" width="17" height="17" alt="" border="0">
				</div>
			</div>';
		$data .= '
			'.(!empty($modSettings['2sichat_dis_list']) ? '': '
					<div id="chatonhover" class="chaton_containter">
						<img src="'.LoadImage('balloon.png').'" alt="{}" border="0"><strong>'.$txt['whos_on'].' <span id="cfriends"></span></strong>
					</div>
			');
		
		return $data;
	}

	function chat_extra_template() { 

		global $txt, $member_id, $dirArray, $indexCount, $modSettings, $curtheme, $context;
		
		$data = ' 
			<div class="extraboxhead">
				'.$txt['bar_tools1'] .'
			</div>';
				
		$data .= '
			<div class="extraboxcontent">';	
				if (!empty($member_id)) {
					if(!empty($modSettings['enable_buddylist'])){
						$data .= '<div class="extrasettings">'.$txt['bar_tools2'].'</div>';
						$data.= '<a href="?action=profile;area=lists;u='.$member_id.'">'.$txt['bil'].'</a>';
					}
					
					$val =  !empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_buddys']) ? 'checked="true"' : '';
					$data .='<br />'.$txt['bar_tools3'].'
						<div class="checkboxOne">
							<input type="checkbox" class="show_buddys" id="showbuddys" '.$val.' />
							<label for="showbuddys"></label>
						</div>';
					
					$data.= '<br />';
					
					$val =  !empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_chatSnd']) ? 'checked="checked"' : '';
					$data .=''.$txt['bar_tools4'].'
						<div class="checkboxOne">
							<input type="checkbox" class="_chatSnd" id="_chatSnd" '.$val.' />
							<label for="_chatSnd"></label>
						</div>';
					
					$data.= '<br />';
					
					$val =  !empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_list_keep']) ? 'checked="checked"' : '';
					$data .=''.$txt['bar_tools5'].'
						<div class="checkboxOne">
							<input type="checkbox" class="list_keep_open" id="listkeepopen" '.$val.' />
							<label for="listkeepopen"></label>
						</div>';
					
					$data.= '<br />';
					
					
					if($indexCount != 3 && empty($_REQUEST['theme'])){
						$data .= '<br /><div class="extratheme">'.$txt['bar_tools6'].'</div><select class="theme-change">';
						
						for ($index = 0; $index < $indexCount; $index++) {
							$Select = $curtheme == $dirArray[$index] ? 'selected="selected"' : '';
							if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php") { // don't list hidden files
								$data .= '  <option value="' . $dirArray[$index] . '" '.$Select.'>' . ucfirst($dirArray[$index]) . '</option>';
							}
						}
						
						$data .= '</select><br />';
					}	
					
				}
				if (!empty($modSettings['2sichat_ico_adthis']) || !empty($modSettings['2sichat_ico_gplus']) || !empty($modSettings['2sichat_ico_myspace']) || !empty($modSettings['2sichat_ico_twit']) || !empty($modSettings['2sichat_ico_fb']))
						$ssocial=$txt['bar_social'];
				else
					$ssocial= '';
					
				$data.= '
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
					<img src="'.LoadImage('facebook.png').'" width="20" height="20" alt="'.$txt['facebook'].'"  title="'.$txt['facebook'].'" border="0"></a><br />':'').'';
					
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
							}$data.= '<br />';
				}
					
				if(!empty($context['gadgets'])) {
					$data.= '<br />
						<div class="extrasettings">'.$txt['bar_gadgets'] .':</div>';
							foreach ($context['gadgets'] as $gadget) {
								$data.= '
									<a href="javascript:void(0)" onclick="javascript:openGadget(\''.$gadget['id'].'\');showhide(\'extra\');return false;">
										'.$gadget['title'].'
									</a><br />';
							}
				}
				
		  $data .= '
			  <div class="extraboxbottom">
				  Powered by SA Chat &copy; 2010 - 2015 <a href="http://samods.github.io/SAChatBar/">SA Mods</a>
			  </div>';
				  
		 $data .= '
				  </div>';
			
		return $data;
	}

	function buddy_search_list_template() {
		global $context, $modSettings;
		
		$data = ''; 
		if(!empty($context['search_friends'])) {
				 
			foreach ($context['search_friends'] as $buddy) {
						
				$data .= '
					<div class="buddyboxuname">	
						<div class="buddyboxuavatar">
							<a  href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');'.(!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_list_keep']) ? '' :'showhide(\'friends\');').'return false;">
								<img width="25px" height="25px" alt="'.$buddy['real_name'].'" title="'.$buddy['real_name'].'" src="'.$buddy['avatar'].'" />
							</a>
						</div>
						<div class="buddyboxuright">
							'.($buddy['session']?'<img id="extraimg" src="'.LoadImage('bullet_green.png').'" width="17" height="17" alt="" border="0">':
							'<img id="extraimg" src="'.LoadImage('bullet_red.png').'" width="17" height="17" alt="" border="0">').'
						</div>';
							
						$data .='
							<a  href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');'.(!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_list_keep']) ? '' :'showhide(\'friends\');').'return false;">
								'.$buddy['real_name'].'
							</a>
					</div><br clear="all"/>';			
			} 
		}else{
				
			$data .= 'No users found.';
		}
				  
		return $data;
	}
	function buddy_list_template() {

		global $context, $txt, $member_id, $modSettings;

		$data = ' 
			 <div id="chead" class="buddyboxhead">
				 <img src="'.LoadImage('balloon.png').'" alt="{}" border="0">'.$txt['whos_on'].'&nbsp;('.$context['online_count'].')
					 <br clear="all" />
			 </div>';
				
		$data .= '
			<div class="buddyboxcontent">';
				 
				$data .= '<div id="bddy_box">';
				if(!empty($context['friends'])) {
				 
					foreach ($context['friends'] as $buddy) {
						
						$data.= '
						<div class="buddyboxuname">	
							<div class="buddyboxuavatar">
								<a  href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');'.(!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_list_keep']) ? '' :'showhide(\'friends\');').'return false;">
									<img width="25px" height="25px" alt="'.$buddy['real_name'].'" title="'.$buddy['real_name'].'" src="'.$buddy['avatar'].'" />
								</a>
							</div>
							<div class="buddyboxuright">
								 '.($buddy['session']?'<img id="extraimg" src="'.LoadImage('bullet_green.png').'" width="17" height="17" alt="" border="0">':
								'<img id="extraimg" src="'.LoadImage('bullet_red.png').'" width="17" height="17" alt="" border="0">').'
							</div>';
							$data .='
								<a  href="javascript:void(0)" onclick="javascript:chatTo(\''.$buddy['id_member'].'\');'.(!empty($_COOKIE[$modSettings['2sichat_cookie_name'].'_list_keep']) ? '' :'showhide(\'friends\');').'return false;">
									'.$buddy['real_name'].'
								</a>
						</div><br clear="all"/>';
								
					} 
				}else{
				
					$data .= '<div class="buddyboxnousers">'.$txt['bar_buddys_none'].'</div>';
				}
				$data .= '</div>
					</div>';

				$data .= '<div class="buddyboxcontentinput"><input type="text" id="sasearch" name="sasearch" placeholder="'.$txt['bar_buddys_search'].'" /></div>';
			
				  
		return $data;
	}

	function guest_bar_template() { //Well guest can't access everything.

		global $txt;

		$data = '<img src="'.LoadImage('balloon.png').'" alt="{}" border="0">'.$txt['guest_msg'].'';
			
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
			</div>';
		
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
			<span style="color:black;">
				'.$context['gadget']['url'].'
			</span>
		</body>
	</html>';
		return $data;
	}
?>