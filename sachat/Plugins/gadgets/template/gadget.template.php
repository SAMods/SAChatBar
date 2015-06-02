<?php
if (!defined('SMF'))
	die('No direct access...');
	
function load_gadgets_js(&$data){
	global $context,$themeurl,$boardurl,$thjs,$modSettings;
	
	$data .='
	
	$sachat(\'head\').append(\'<link rel="stylesheet" id="stylechange" href="'.$boardurl.'/sachat/Plugins/gadgets/template/gadget.css" type="text/css" />\');
	
	function selectmouse(selector,handle){
		$sachat(\'.\'+selector).draggable({
			handle: \'.\'+handle,
			opacity: 0.35,
			drag: function(event, ui) {
							
				newX = ui.offset.left;
				newY = ui.offset.top;
							   
				$sachat(this).css(\'left\', newX);
				$sachat(this).css(\'top\', newY);
								
				zdex = (zdex+1);
				$sachat(this).css(\'zIndex\', zdex);

				cgobj = $sachat(this).attr(\'id\');
				gadid = cgobj.substr(6);
				gadFix = cgobj.substr(0, 6);
									
				if (gadFix == \'Gadget\') {
					var myArray = [];
					myArray[0] = \''.$modSettings['2sichat_cookie_name'].'_gadget\';
					myArray[1] = cgobj;
					myArray[2] = gadid;
					myArray[3] = newX;
					myArray[4] = newY;
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_gadget\'+gadid, escape(myArray.join(\',\')));
				}
				else{
					var myArray = [];
					myArray[0] = \''.$modSettings['2sichat_cookie_name'].'\';
					myArray[1] = \'msg_win\'+cgobj;
					myArray[2] = cgobj;
					myArray[3] = newX;
					myArray[4] = newY;
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+cgobj, escape(myArray.join(\',\')));
				}
			}
		});  
	}
	function closeGadget(id) {
		$sachat(\'#Gadget\'+id).remove();
		$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_gadget\'+id, null);
	}
	
	function openGadget(id) {
		if (document.getElementById(\'Gadget\'+id) == undefined) {

			zdex = (zdex+1);
					  
			var div = $sachat("<div />").attr("id","Gadget"+id).attr("dir","ltr").attr("class","gadget_win").attr("style","position: fixed; zIndex: " +zdex+ ";").appendTo($sachat("body"));
					  
			$sachat.ajax({
				url: \''.$boardurl.'/sachat/index.php\',
				data: \''.$thjs.'gid=\'+id,
				dataType: "json",
				cache: false,
				success: function(data){
					if (data.DATA != null){  
						$sachat(div).html(data.DATA);
					} 
					if (data != null && data.CONLINE != null) {
						$sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
					}
					if (data != null && data.ONLINE != null) {
						$sachat("#sa_friends").html(data.ONLINE);
					} 
				}
			});

			$sachat(window).load(selectmouse(\'gadget_win\',\'gadgetboxhead\'));
					
			if (cSession == undefined) {
				var myArray = [];
				myArray[0] = \''.$modSettings['2sichat_cookie_name'].'_gadget\';
				myArray[1] = \'Gadget\'+id;
				myArray[2] = id;
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_gadget\'+id, escape(myArray.join(\',\')));	
			}else{
				var myArray = [];
					myArray[0] = \''.$modSettings['2sichat_cookie_name'].'_gadget\';
					myArray[1] = \'Gadget\'+id;
					myArray[2] = id;
					myArray[3] = cSession[3];
					myArray[4] = cSession[4];
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_gadget\'+id, escape(myArray.join(\',\')));
				}
			}
		}
		
		function dogadCookies() {
			
			$sachat.each(document.cookie.split(\';\'), function(i, cookie) {
						
			var c = $sachat.trim(cookie);
			name = c.split(\'=\')[0];
			var cookie = unescape($sachat.cookie(name));
			cSession = cookie.split(\',\');
						
			if(cSession[0] == \''.$modSettings['2sichat_cookie_name'].'_gadget\'){
				openGadget(cSession[1].substr(6));
				document.getElementById(cSession[1]).style.left = cSession[3]+\'px\';
				document.getElementById(cSession[1]).style.top = cSession[4]+\'px\';
			}
				
			});
		}dogadCookies();
	';
	
	return $data;
}

function template_display_gad(&$data){
	global $context, $txt;

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