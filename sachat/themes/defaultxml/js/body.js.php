<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
function initchat() {

	global $member_id, $boardurl, $modSettings, $themeurl, $thjs, $context;

	if ($member_id) {
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", chat_bar_template()));
		$buddies = addslashes(preg_replace("/\r?\n?\t/m", "", genMemList('list')));
	}else{
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", guest_bar_template()));
	}
	
	$context['HTML'] = '	
		var $sachat = jQuery.noConflict();
	    
		var css=document.createElement("link");
		css.setAttribute("rel", "stylesheet");
		css.setAttribute("type", "text/css");
		css.setAttribute("href", "'.$themeurl.'/style.css");
		document.documentElement.getElementsByTagName("HEAD")[0].appendChild(css);
		
		var div = document.createElement(\'div\');
		div.setAttribute(\'id\',\'chat_containter\');
		div.setAttribute(\'dir\',\'ltr\');
		div.setAttribute(\'class\',\'chatBar_containter\');
		div.innerHTML = \''.$bar.'\';
		document.body.appendChild(div);
	';

	// Members only JavaScript
	if ($member_id) {
		$context['HTML'].= '
		    
			var div = document.createElement(\'div\');
			div.setAttribute(\'id\',\'friends\');
			div.setAttribute(\'dir\',\'ltr\');
			div.setAttribute(\'class\',\'buddybox\');
			div.style.display = \'none\';
    		div.innerHTML =\''.$buddies.'\';
			document.body.appendChild(div);
			
			//setup the basic html template structer for the chat boxes
			var div = document.createElement(\'div\');
			div.setAttribute(\'id\',\'ChatBoxtemplate\');
			div.setAttribute(\'dir\',\'ltr\');
			document.body.appendChild(div);
			
			var div = document.createElement(\'div\');
			div.setAttribute(\'class\',\'chatbox\');
			div.setAttribute(\'dir\',\'ltr\');
			div.setAttribute(\'style\',\'position: fixed\');
			ChatBoxtemplate.appendChild(div);
			
			var div = document.createElement(\'div\');
			div.setAttribute(\'class\',\'chatBoxWrap\');
			div.setAttribute(\'id\',\'chatBoxWrap\');
			document.body.appendChild(div);
			
			var div = document.createElement(\'div\');
			div.setAttribute(\'class\',\'chatBoxslider\');
			div.setAttribute(\'id\',\'chatBoxslider\');
			chatBoxWrap.appendChild(div);
			
			var div = document.createElement(\'div\');;
			div.setAttribute(\'id\',\'slideLeft\');
			div.innerHTML =\'<img src="'.$themeurl.'/images/arrow_right.png" />\';
			document.body.appendChild(div);
			
			var div = document.createElement(\'div\');;
			div.setAttribute(\'id\',\'slideRight\');
			div.innerHTML =\'<img src="'.$themeurl.'/images/arrow_left.png" />\';
			document.body.appendChild(div);
			
			$sachat(\'#slideLeft\').on(\'click\',function(){
				$sachat(\'.chatBoxslider .chatbox:visible:first\').addClass(\'chatoverFlowHide\');
				$sachat(\'.chatBoxslider .chatbox.chatoverFlow\').removeClass(\'chatoverFlow\');
				updateChatBoxPosition();
			});

			$sachat(\'#slideRight\').on(\'click\',function(){
				$sachat(\'.chatBoxslider .chatbox.chatoverFlowHide:last\').removeClass(\'chatoverFlowHide\');
				updateChatBoxPosition();
			});
     
			$sachat(window).resize(function(){
				updateChatBoxPosition();
				$sachat(\'.buddybox\').css({
					\'max-height\':$sachat(window).height()-50
				}) 
				$sachat(\'.extrabox\').css({
					\'max-height\':$sachat(window).height()-50
				}) 
			});
			
			$sachat(\'.buddybox\').css({
				\'max-height\':$sachat(window).height()-50
			}) 
			
			var doUpdate = function () {
				updatebar();
				setTimeout(doUpdate, 5000);
			};
			doUpdate();
			
			function setup_XMLHttpRequest(RequestType,RequestUrl) {
				var request = new XMLHttpRequest();
				request.open(RequestType, RequestUrl, true);
				return request;
			}
			
			function updatebar() {
				
				var request = setup_XMLHttpRequest(\'GET\',\''.$boardurl.'/sachat/index.php?'.$thjs.'action=heart\');

				request.onload = function() {
					if (request.status >= 200 && request.status < 400){
						// Success!
						resp = request.responseText;
						if(resp){
							data = JSON.parse(resp);
						}
						if (data != null && data.ids != null){
							data.ids.forEach(function(id,i){
								if (!document.getElementById(\'cmsg\'+id) && id != null && id != null || $sachat("#"+id).css(\'display\') == \'none\' && id != null) {
									chatTo(id);
								}else {
									if(document.getElementById(\'cmsg\'+id) && id != null && $sachat("#"+id).css(\'display\') != \'none\') {
										updatemsg(id);								
									}
								}
							});
						}
						dynamicUpdates();
						
					} else {
						// We reached our target server, but it returned an error

					}
				};

				request.onerror = function() {
				  // There was a connection error of some sort
				};

				request.send();
			}
			
			function dynamicUpdates(){
			
				if (data.buddySESSION != null) {
					var div = document.getElementById("session"+data.userTyping);
					if(div){div.innerHTML = \'<span class="green">*&nbsp;</span>\';}
				}
				if (data.buddySESSION == null) {
					var div = document.getElementById("session"+data.userTyping);
					if(div){div.innerHTML = \'<span class="red">*&nbsp;</span>\';}
				}
				if (data.buddySESSION!= null) {
					var div = document.getElementById("sent"+data.SENTMSGID);
					if(div){div.innerHTML = \'<br />\'+data.SENTMSGTIME+\'<br /><br />\';}
				}
				if (data.CONLINE != null) {
					var div = document.getElementById("cfriends");
					if(div){div.textContent = \'(\'+data.CONLINE+\')\';}
				}
				if ( data.ONLINE != null) {
					var div = document.getElementById("friends");
					if(div){div.innerHTML = data.ONLINE;}
				}
			}
			function chatTo(id) {
           
				if (!$sachat(\'#\'+id).html()){ 
					$sachat(\'.chatBoxslider\').prepend($sachat(\'#ChatBoxtemplate .chatbox\').clone().attr(\'id\',id)); 
				}
				else if (!$sachat(\'#\'+id).is(\':visible\') ){
					clone = $sachat(\'#\'+id).clone();
					$sachat(\'#\'+id).remove();
					if(!$sachat(\'.chatBoxslider .chatbox:visible:first\').html()){
						$sachat(\'.chatBoxslider\').prepend(clone.show());
					}else{
						$sachat(clone.show()).insertBefore(\'.chatBoxslider .chatbox:visible:first\');
					}	
				}
				
				var request = setup_XMLHttpRequest(\'GET\',\''.$boardurl.'/sachat/index.php?'.$thjs.'cid=\'+id);

				request.onload = function() {
					if (request.status >= 200 && request.status < 400){
						// Success!
						resp = request.responseText;
						
						if(resp){
							data = JSON.parse(resp);
						}

					    $sachat($sachat(\'#\'+id)).html(data.DATA);
						
						$sachat("#"+data.BID).css(\'bottom\', \'27px\');
						updateChatBoxPosition();
						$sachat("#"+id+" .chatboxcontent").scrollTop($sachat("#"+id+" .chatboxcontent")[0].scrollHeight);
						
						var myArray = [];
						myArray[0] = \''.$modSettings['2sichat_cookie_name'].'\';
						myArray[1] = \'msg_win\'+id;
						myArray[2] = id;
						$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+id, escape(myArray.join(\',\')));
						dynamicUpdates();
					} else {
						// We reached our target server, but it returned an error

					}
				};

				request.onerror = function() {
					// There was a connection error of some sort
				};

				request.send();
			}
			
			function xchat(id) {
				div = document.getElementById(\'\'+id);
				div.style.display = \'none\';
				updateChatBoxPosition();
				
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+id, null);	
				var request = setup_XMLHttpRequest(\'POST\',\''.$boardurl.'/sachat/index.php?action=closechat\');
				
				request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
				request.send();
			}
			
			function updatemsg(id){
				
				var request = setup_XMLHttpRequest(\'GET\',\''.$boardurl.'/sachat/index.php?'.$thjs.'update=\'+id);
				
				request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
				request.send();
				
				request.onreadystatechange = function() {//Call a function when the state changes.
					if(request.readyState == 4 && request.status == 200) {
						data = JSON.parse(request.responseText);
						var newdiv = document.createElement(\'div\');
						newdiv.setAttribute(\'dir\',\'ltr\');
						newdiv.innerHTML = data.DATA;
						document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).lastChild);
						$sachat("#"+id+" .chatboxcontent").scrollTop($sachat("#"+id+" .chatboxcontent")[0].scrollHeight);
					}
				}
			}
			
			function jsubmit(id){
		   
				var textbox = \'msg\'+id;
				var msg = document.getElementById(textbox).value;
				document.getElementById(textbox).value = \'\';
				
				var request = setup_XMLHttpRequest(\'POST\',\''.$boardurl.'/sachat/index.php?'.$thjs.'cid=\'+id+\'&msg=\'+encodeURIComponent(msg));
	
				request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
				request.send(JSON.stringify(data));
				
				request.onreadystatechange = function() {//Call a function when the state changes.
					if(request.readyState == 4 && request.status == 200) {
						data = JSON.parse(request.responseText);
						var newdiv = document.createElement(\'div\');
						newdiv.setAttribute(\'dir\',\'ltr\');
						newdiv.innerHTML = data.DATA;
						document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).lastChild);
						$sachat("#"+id+" .chatboxcontent").scrollTop($sachat("#"+id+" .chatboxcontent")[0].scrollHeight);
					}
				}
			}
			
			function updateChatBoxPosition(){
				
				var right=0;
				var slideLeft = false;
				var $chatboxes = $sachat(\'.chatBoxslider .chatbox:visible\');
				
				$chatboxes.each(function(){
					$sachat(this).css({
						\'right\':right
					});

					right += $sachat(this).width()+20;
			
					$sachat(\'.chatBoxslider\').css({
						\'width\':right
					});
			
					if ($sachat(this).offset().left- 20<0){
						$sachat(this).addClass(\'chatoverFlow\');
						slideLeft = true;
					}
					else{
						$sachat(this).removeClass(\'chatoverFlow\');
					}
				});
				
				if(slideLeft) {
					$sachat(\'#slideLeft\').show();
				}else{ 
					$sachat(\'#slideLeft\').hide();
				}
		
				if($sachat(\'.chatoverFlowHide\').html()) {
					$sachat(\'#slideRight\').show();
				}else{
					$sachat(\'#slideRight\').hide();
				}
			}
		';
	}
	$context['HTML'].= '
		doCookies();
		function doCookies() {
			document.cookie.split(\';\').forEach(function(cookie, i){			
				var c = $sachat.trim(cookie);
				name = c.split(\'=\')[0];
				var cookie = unescape($sachat.cookie(name));
				cSession = cookie.split(\',\');
										
				if(cSession[0] == \''.$modSettings['2sichat_cookie_name'].'\'){
					chatTo(cSession[1].substr(7));
				}
			});
		}
		function showhide(layer_ref) {
			if(document.getElementById(layer_ref).style.display == \'none\'){
                $sachat(document.getElementById(layer_ref)).fadeIn("fast");  
            }
            else{
				$sachat(document.getElementById(layer_ref)).fadeOut("slow");	
            }	
		}
	';
}
?>