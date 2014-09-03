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
		var HeartbeatCount = 0;
		var minHeartbeat = '.$modSettings['2sichat_mn_heart'].';
		var maxHeartbeat = '.$modSettings['2sichat_mn_heartmin'].';
		var HeartbeatTime = minHeartbeat;
		var itemsfound = 0;
			
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
			chat_containter.appendChild(div);
				
			var div = document.createElement(\'div\');;
			div.setAttribute(\'id\',\'slideRight\');
			div.innerHTML =\'<img src="'.$themeurl.'/images/arrow_left.png" />\';
			chat_containter.appendChild(div);
			
			var isVisible = function(obj){
				var style;

				if (obj == document) return true;

				if (!obj) return false;
				if (!obj.parentNode) return false;
				if (obj.style) {
					if (obj.style.display == \'none\') return false;
					if (obj.style.visibility == \'hidden\') return false;
				}

				//Try the computed style in a standard way
				if (window.getComputedStyle) {
					style = window.getComputedStyle(obj, "")
					if (style.display == \'none\') return false;
					if (style.visibility == \'hidden\') return false;
				} else {
					//Or get the computed style using IE\'s silly proprietary way
					style = obj.currentStyle;
					if (style) {
						if (style[\'display\'] == \'none\') return false;
						if (style[\'visibility\'] == \'hidden\') return false;
				   }
				}
				return isVisible(obj.parentNode);
			};
			
			var addEvent = function(elem, type, eventHandle) {
				if (elem == null || typeof(elem) == \'undefined\') return;
				
				if ( elem.addEventListener ) {
					elem.addEventListener( type, eventHandle, false );
				} else if ( elem.attachEvent ) {
					elem.attachEvent( "on" + type, eventHandle );
				} else {
					elem["on"+type]=eventHandle;
				}
			};
     
			addEvent(window, "resize", resizeCallBack);
			addEvent(slideRight, "click", slideRightCallBack);
			addEvent(slideLeft, "click", slideLeftCallBack);
			
			function resizeCallBack(){
				updateChatBoxPosition();
				$sachat(\'.buddybox\').css({
					\'max-height\':$sachat(window).height()-50
				}) 
			}
			
			$sachat(\'.buddybox\').css({
				\'max-height\':$sachat(window).height()-50
			}) 
			
			function slideLeftCallBack(){
				
				el = document.querySelectorAll(\'.chatBoxslider .chatbox\');
				Array.prototype.forEach.call(el, function(el, i){
					if(el.offsetWidth > 0 && el.offsetHeight > 0){
						el.classList.add(\'chatoverFlowHide\');
					}
					if(el.classList.contains(\'chatoverFlow\')){
						el.classList.remove(\'chatoverFlow\');
					}
				});
				updateChatBoxPosition();
			}
		    
			function slideRightCallBack(){
			
				el = document.querySelectorAll(\'.chatBoxslider .chatbox.chatoverFlowHide\');
				Array.prototype.forEach.call(el, function(el, i){
					if(el.classList.contains(\'chatoverFlowHide\')){
						el.classList.remove(\'chatoverFlowHide\');
					}
				});
				updateChatBoxPosition();
			}
			
			var doUpdate = function () {
				updatebar();
				setTimeout(doUpdate, HeartbeatTime);
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
							for (x in data.ids) {
								if (!document.getElementById(\'cmsg\'+data.ids[x]) && data.ids[x] != null && data.ids[x] != null || document.getElementById(data.ids[x]).style.display == \'none\' && data.ids[x] != null) {
									chatTo(data.ids[x]);
								}else {
									if(document.getElementById(\'cmsg\'+data.ids[x]) && data.ids[x] != null && document.getElementById(data.ids[x]).style.display != \'none\') {
										updatemsg(data.ids[x],x);								
									}
								}
								itemsfound += 1;
								//console.log(data.ids[x]);
							}
						}
						
						heartbeattimeout();
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
			
			function heartbeattimeout(){
				HeartbeatCount++;

				if (itemsfound > 0) {
							
					HeartbeatTime = minHeartbeat;
					HeartbeatCount = 1;
							
				} else if (HeartbeatCount >= 10) {
							
					HeartbeatTime *= 2;
					HeartbeatCount = 1;
							
					if (HeartbeatTime > maxHeartbeat) 
					{
						HeartbeatTime = maxHeartbeat;
					}		
				}
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
				if (data.ONLINE != null) {
					var div = document.getElementById("friends");
					if(div){div.innerHTML = data.ONLINE;}
				}
				if (data.userTypingSay != null) {
					var div = document.getElementById("typeon"+data.userTyping);
					if(div){div.innerHTML = data.userTypingSay;}
				}
				if (data != null && data.userTypingSay == null) {
					var div = document.getElementById("typeon"+data.userTyping);
					if(div){div.innerHTML = "";}
				}
			}
			
			function chatKeydown(typing) {
				var gpa;
				var gfa;
				clearTimeout(gpa);
				gpa = setTimeout(function () {
					var request = setup_XMLHttpRequest(\'POST\',\''.$boardurl.'/sachat/index.php?action=typing&f\');
					request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
					request.send("userid='.$member_id.'&typing=typing&untype=1");
					gfa = -1
				}, 5E3);
				if (gfa != typing) {
					var request = setup_XMLHttpRequest(\'POST\',\''.$boardurl.'/sachat/index.php?action=typing&t\');
					request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
					request.send("userid='.$member_id.'&typing=typing");
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

				'.(!empty($modSettings['2sichat_live_type']) ? '$sachat(document).keydown(function () {return chatKeydown(id);});' : '').'
				
				var request = setup_XMLHttpRequest(\'GET\',\''.$boardurl.'/sachat/index.php?'.$thjs.'cid=\'+id);

				request.onload = function() {
					if (request.status >= 200 && request.status < 400){
						// Success!
						resp = request.responseText;
						
						if(resp){
							data = JSON.parse(resp);
						}

						document.getElementById(+id).innerHTML = data.DATA;
						document.getElementById(+data.BID).style.bottom = \'27px\';
						
						updateChatBoxPosition();
						
						document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollTop = document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollHeight;
						
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
			
			function updatemsg(id,m){
				
				var request = setup_XMLHttpRequest(\'GET\',\''.$boardurl.'/sachat/index.php?'.$thjs.'update=\'+id+\'&msg=\'+m);
				
				request.setRequestHeader(\'Content-Type\', \'application/x-www-form-urlencoded; charset=UTF-8\');
				request.send();
				
				request.onreadystatechange = function() {//Call a function when the state changes.
					if(request.readyState == 4 && request.status == 200) {
						data = JSON.parse(request.responseText);
						var newdiv = document.createElement(\'div\');
						newdiv.setAttribute(\'dir\',\'ltr\');
						newdiv.innerHTML = data.DATA;
						document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).lastChild);
						document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollTop = document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollHeight;
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
						document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollTop = document.getElementById(id).getElementsByClassName("chatboxcontent")[0].scrollHeight;
					}
				}
				HeartbeatTime = minHeartbeat;
				HeartbeatCount = 1;
			}
			
			function updateChatBoxPosition(){
				
				var right=0;
				var slideLeft = false;

				elements = document.querySelectorAll(".chatBoxslider .chatbox");
				
                Array.prototype.forEach.call(elements, function(el, i){
					
					if(el.offsetWidth > 0 && el.offsetHeight > 0 || isVisible(el)){
			
						el.style.right = right+"px";
						
						right += el.offsetWidth + 20;
					
						el.style.width = right;
						
						if (el.offsetLeft - 20 < 0){
							el.classList.add(\'chatoverFlow\');
							slideLeft = true;
						}
						else{
							el.classList.remove(\'chatoverFlow\');
						}
					}
				});
				
				if(slideLeft) {
					document.getElementById("slideLeft").style.display = \'block\';  
				}else{ 
					document.getElementById("slideLeft").style.display = \'none\';  
				}

				if($sachat(\'.chatoverFlowHide\').html()) {
					document.getElementById("slideRight").style.display = \'block\';
				}else{
					document.getElementById("slideRight").style.display = \'none\';  
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
                document.getElementById(layer_ref).style.display = \'block\';  
            }
            else{
				document.getElementById(layer_ref).style.display = \'none\';
            }	
		}
	';
}
?>