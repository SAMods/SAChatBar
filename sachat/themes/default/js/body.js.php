<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	function initchatjs() {

		global $buddies, $soundurl, $member_id, $txt, $filter_events, $boardurl, $modSettings, $themeurl, $thjs, $context;

		if ($member_id && empty($modSettings['2sichat_dis_bar'])) {
			$bar = addslashes(preg_replace("/\r?\n?\t/m", "", chat_bar_template()));
			$buddies = addslashes(preg_replace("/\r?\n?\t/m", "", genMemList('list')));
				
		} elseif (empty($modSettings['2sichat_dis_bar']) && !$member_id) {
			$bar = addslashes(preg_replace("/\r?\n?\t/m", "", guest_bar_template()));
		}

		$extra = addslashes(preg_replace("/\r?\n?\t/m", "", chat_extra_template()));
			
		$context['HTML'] = '
			var msgArray = new Array();
			var msgclear = new Array();
			var ie=document.all;
			var isIE = /*@cc_on!@*/false;
			var cSession;
			var minimised = 0;
			var zdex = 100;
			zdex = zdex * 1;
			var blinkOrder = 0;
			var newMessagesWin = new Array();
			var newMessages = new Array();
			originalTitle = document.title;
			var HeartbeatCount = 0;
			var minHeartbeat = '.$modSettings['2sichat_mn_heart'].';
			var maxHeartbeat = '.$modSettings['2sichat_mn_heartmin'].';
			var HeartbeatTime = minHeartbeat;
			var itemsfound = 0;
			
			var windowFocus = true;
			var memberID = "'.$member_id.'";
			lastKeyUp = 0;
			var saChatShow = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_show\');
			var saChatListKeepOpen = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_list_keep\');
			styleChat = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_Style\');
			var milliseconds = new Date().getTime();
		
			chatcss = \'style.css?\' + milliseconds;
			
			$sachat(\'head\').append(\'<link rel="stylesheet" id="stylechange" href="'.$themeurl.'/css/\' + chatcss + \'" type="text/css" />\');';
			if(isset($filter_events['hook_load_js']))
				call_hook('hook_load_js', array(&$context['HTML']));
			
			$context['HTML'] .= ''.(!empty($modSettings['2sichat_dis_bar']) ? '':'
					
					$sachat("<div />").attr("id","chat_containter").attr("class","chatBar_containter").attr("dir","ltr").html(\''.$bar.'\').appendTo($sachat("body"));
					if(saChatShow == 1){
						$sachat(\'#chat_containter\').removeClass(\'chatBar_containter\');
						$sachat(\'#chatonhover\').hide();
						$sachat(\'#sa_friends\').hide();
						$sachat(\'#chattools_containter\').hide();
						$sachat(\'#hideimg\').attr(\'src\',\''.LoadImage('world_off.png').'\');
						$sachat(\'#hideimg\').attr(\'title\',\''.$txt['bar_showChat'].'\');
					}
			');
			
			$context['HTML'].= '
				$sachat("<div />" ).attr("id","extra").attr("class","extrabox").attr("dir","ltr").html(\''.$extra.'\').appendTo($sachat("body"));
				$sachat(\'#extra\').hide();
			';
			
			// Members only JavaScript
			if ($member_id) {
				$context['HTML'].= '
			$sachat("<div />" ).attr("id","sa_friends").attr("class","buddybox").attr("dir","ltr").html(\''.$buddies.'\').appendTo($sachat("body"));
			//setup the basic html template structer for the chat boxes
			$sachat("<div />").attr("id","ChatBoxtemplate").attr("dir","ltr").appendTo($sachat("body"));
			$sachat("<div />").attr("dir","ltr").attr("class","chatbox").appendTo($sachat("#ChatBoxtemplate"));
			$sachat("<div />" ).attr("class","chatBoxWrap").appendTo($sachat("body"));
			$sachat("<div />" ).attr("class","chatBoxslider").appendTo($sachat(".chatBoxWrap"));
			$sachat("<div />" ).attr("id","slideLeft").html(\'<img src="'.LoadImage('arrow_right.png').'" />\').appendTo($sachat("body"));
			$sachat("<div />" ).attr("id","slideRight").html(\'<img src="'.LoadImage('arrow_left.png').'" />\').appendTo($sachat("body"));
			
			if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_list_keep\') == 1 && saChatShow != 1){
				$sachat(\'#sa_friends\').show();
			}else{
				$sachat(\'#sa_friends\').hide();
			}

			$sachat(\'.show_buddys\').change(function(){
				checkit = $sachat(\'#showbuddys\').prop(\'checked\');
				
				if(checkit == true){
					checked = 1;
				}else{
					checked = 0;
				}
				
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_buddys\',checked ,{ expires: 10000});
				updatebar();
				
			});
			
			$sachat(\'.list_keep_open\').change(function(){
				checkit = $sachat(\'#listkeepopen\').prop(\'checked\');
				
				if(checkit == true){
					checked = 1;
					if (!$sachat(\'#sa_friends\').is(\':visible\') && !$sachat(\'#extra\').is(\':visible\')){
						showhide(\'sa_friends\');
					}
				}else{
					checked = 0;
					if ($sachat(\'#sa_friends\').is(\':visible\')){
						showhide(\'sa_friends\');
					}
				}
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_list_keep\',checked ,{ expires: 10000});
				updatebar(false);
			});
			
			$sachat(\'.theme-change\').change(function(){
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_Theme\',$sachat(this).val(),{ expires: 10000});
				location.reload();
			});
			
			$sachat(\'._chatSnd\').change(function(){
				chatSnd();
			});
			
			var isSearching = false;
			
			$sachat("#sa_friends").on(\'blur\', \'#sasearch\', function () {
				$sachat(this).val(\'\');
				isSearching = false;
			});
			
			$sachat("#sa_friends").on(\'input\', \'#sasearch\', function () {
			
				var searchKeyword = $sachat(this).val();
				
				if(searchKeyword == \'\'){
					isSearching = false;
					updatebar();
				}else{
					isSearching = true;
				}
				
				if (searchKeyword.length >= 3) {
					$sachat.post(\''.$boardurl.'/sachat/index.php?chat_user_search\', { keywords: searchKeyword }, function(data) {
						$sachat(\'#bddy_box\').html(data.DATA);
					});
				}
			});
			
			$sachat("#chatonhover").on(\'click\',function() {
					showhide(\'sa_friends\');
			});
			
			$sachat("#sa_friends").on(\'click\', \'#chead\', function () {
				showhide(\'sa_friends\');
			});
;
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
			
			$sachat(\'.extrabox\').css({
				\'max-height\':$sachat(window).height()-50
			}) 
			
			/*$sachat(document).on(\'click\', \'.chatbox\', function () {
				for (x in newMessagesWin) {
					newMessagesWin[x] = false;
				}
			});*/
			
			if(saChatShow == 1){									
				$sachat(\'.chatbox\').hide();
			}
			
			var doUpdate = function () {
				if(saChatShow != 1){
					updatebar(false);
					setTimeout(doUpdate, HeartbeatTime);
				}
			};
			doUpdate();
			
			function updatebar(manual) {
				
				'.(!empty($modSettings['2sichat_live_notfy']) ? 'newmsg_says();' : '').'

				$sachat.ajax({
					url: \''.$boardurl.'/sachat/index.php\',
					data: \''.$thjs.'action=heart\',
					dataType: "json",
					cache: false,
					timeout: '.$modSettings['2sichat_mn_heart_timeout'].',
					success: function(data){
						
						if (data != null && data.ids != null){
							$sachat.each(data.ids, function(id,u) {
								
								if (!document.getElementById(\'cmsg\'+this) && this != null && this != null || $sachat("#"+this).css(\'display\') == \'none\' && this != null) {
									chatTo(this);
									loadsnd(\'new_msg\');
								}else {
									if(document.getElementById(\'cmsg\'+this) && this != null && $sachat("#"+this).css(\'display\') != \'none\') {
										chatTo(this,true);
										loadsnd(\'rec_msg\');
										newMessagesWin[data.NAME] = true;
										newmsg_says();
																	
									}
								}
								itemsfound += 1;
							});
						}
						if (data != null && data.buddySESSION != null) {
							$sachat("#session"+data.userTyping).html(\'<img id="extraimg" src="'.LoadImage('bullet_green.png').'" width="17" height="17" alt="" border="0">\');
						}
						if (data != null && data.buddySESSION == null) {
							$sachat("#session"+data.userTyping).html(\'<img id="extraimg" src="'.LoadImage('bullet_red.png').'" width="17" height="17" alt="" border="0">\');
						}
						if (data != null && data.buddySESSION!= null) {
							$sachat("#sent"+data.SENTMSGID).html(\'<br />\'+data.SENTMSGTIME+\'<br /><br />\');
						}
						if (data != null && data.userTypingSay != null) {
							$sachat("#typeon"+data.userTyping).html(data.userTypingSay);
						}
						if (data != null && data.userTypingSay == null) {
							$sachat("#typeon"+data.userTyping).html(\' \');
						}
						if (data != null && data.CONLINE != null) {
							$sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null && isSearching == false) {
							$sachat("#sa_friends").html(data.ONLINE);
						}
						
						heartbeattimeout();	
					}
				});	
			}
			
			function popoutChat(id) {//TODO: Untested RANDOM!!!!!!!!!
				$sachat.ajax({
					url: \''.$boardurl.'/sachat/index.php\',
					data: \''.$thjs.'cid=\'+id+\'&pop=1\',
					dataType: "json",
					cache: false,
					success: function(data){
						if (data.DATA != null) {
							
							//xchat(id);
							myRef = window.open("test",\'popoutchat\',\'left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width=800,height=600\');
							myRef.document.write(\'<script type="text/javascript" src="' . $boardurl . '/sachat/index.php?action=head"></script>\');
							myRef.document.write(data.DATA);
							myRef.document.write(\'<link rel="stylesheet" href="'.$themeurl.'/css/style.css" type="text/css" />\');
							myRef.document.write(\'<script type="text/javascript" src="' . $boardurl . '/sachat/index.php?action=body"></script>\');
							//xchat(id);
						} 
					}
				});
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
			
			function chatKeydown(typing) {
				var gpa;
				var gfa;
				clearTimeout(gpa);
				gpa = setTimeout(function () {
					$sachat.post("'.$boardurl.'/sachat/index.php?action=typing&f", {
						userid: \''.$member_id.'\',
						typing: typing,
						untype: 1
					}, function () {});
					gfa = -1
				}, 5E3);
				if (gfa != typing) {
					$sachat.post("'.$boardurl.'/sachat/index.php?action=typing&t", {
						userid: \''.$member_id.'\',
						typing: typing
					}, function () {});
					gfa = typing
				}
				/*$sachat(\'#mid_cont_form input[type=text]\').keyup(function(){
					lastKeyUp=0;
					$sachat.post("'.$boardurl.'/sachat/index.php?action=typing&f", {untype:"1"}, function(){
						lastKeyUp=0;
					});
				});
				setInterval(function(){
					lastKeyUp = ++lastKeyUp % 360 + 1;
					if(lastKeyUp>20 && $sachat("#mid_cont_form input[type=text]").val()!=""){
						$sachat.post("'.$boardurl.'/sachat/index.php?action=typing&t", function(){
							lastKeyUp=0;
						});
					}
				},1000);*/
			}
			
			function newmsg_says(){
				var blinkNumber = 0;
				var titleChanged = 0;
				
				for (x in newMessagesWin) {
					if (newMessagesWin[x] == true) {
						++blinkNumber;
						if (blinkNumber >= blinkOrder) {
							document.title = x+\' '.$txt['bar_newmsg_says'].'\';
							titleChanged = 1;
							break;	
						}
					}
				}
				if (titleChanged == 0) {
					document.title = originalTitle;
					blinkOrder = 0;
				} else {
					++blinkOrder;
				}
			}
			
			function chatSnd() {
				
				mute = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\');
				
				if (mute == 1){
					$sachat(\'#chat_Snd\').attr(\'src\',\''.LoadImage('sound.png').'\');
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\',null);
				} 
				else {
					$sachat(\'#chat_Snd\').attr(\'src\',\''.LoadImage('sound_mute.png').'\');
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\',\'1\',{ expires: expdate});
				}
			}

			function loadsnd(snd){
				mute = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\');
				if(!mute){
					var sound = new Audio(\''.$soundurl.'/\'+snd+\'.mp3\');
					sound.play();
				}
			}

			function snooper(){
				snoop = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnoop\');
				if(snoop == 1){
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnoop\',null);
				}
				else{
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnoop\',\'1\',{ expires: expdate});
				}
				updatebar();
			}
			
			function updateChatBoxPosition(){

				var slideLeft = false;
				var $visible_chatboxes = $sachat(\'.chatBoxslider .chatbox:visible\');
				var $all_chatboxes = $sachat(\'.chatBoxslider .chatbox\');
				var cssValue = $sachat($all_chatboxes).css(\'margin-right\'); 
				var parsedCssValue = parseInt(cssValue);
				
				$visible_chatboxes.each(function(){
				
					$sachat(this).css({
						\'margin-right\':parsedCssValue
					});

					parsedCssValue += $sachat(this).width()+5;
					
					$sachat(\'.chatBoxslider\').css({
						\'width\':parsedCssValue
					});
			
					if ($sachat(this).offset().left- 10<0){
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
			
			function chatTo(id, isupdate) {
				var DId = arguments[0];
				if(!isupdate){isupdate=false;}

				
				if (DId != undefined) {
					
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
					
					$sachat.ajax({
						url: \''.$boardurl.'/sachat/index.php\',
						data: \''.$thjs.'cid=\'+DId+\'&update=\'+isupdate,
						dataType: "json",
						cache: false,
						success: function(data){
							if (data.DATA != null) {
								
								if(isupdate == true){
									$sachat(\'#cmsg\'+id).empty().html(data.DATA);
									$sachat("#"+id+" .chatboxcontent").animate({ scrollTop: $sachat("#"+id+" .chatboxcontent")[0].scrollHeight }, 1000);
									newMessagesWin[data.NAME] = true;
									newMessages[id] = true;
									if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id)){
										$sachat("#new"+id).html(\'<img id="extraimg" src="'.LoadImage('new.png').'" width="17" height="17" alt="" border="0">\');
										$sachat("#new"+id).show();
										var myArray = [];
										myArray[0] = \''.$modSettings['2sichat_cookie_name'].'_min_new\';
										$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min_new\'+id, escape(myArray.join(\',\')));
									}
								}
								else{
									$sachat($sachat(\'#\'+id)).attr("id",+data.BID);
									$sachat($sachat(\'#\'+id)).html(data.DATA);
									if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min_new\'+id)){
										$sachat("#new"+id).html(\'<img id="extraimg" src="'.LoadImage('new.png').'" width="17" height="17" alt="" border="0">\');
										$sachat("#new"+id).show();
									}
									if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id)){
										upDownchat(id);	
									}
									$sachat("#"+id+" .chatboxcontent").animate({ scrollTop: $sachat("#"+id+" .chatboxcontent")[0].scrollHeight }, \'fast\');
									updateChatBoxPosition();
									$sachat("#search"+id).attr("style","display: none")
								}
								
							} 
							else {
								xchat(DId);
							}
						
							if (data != null && data.userTypingSay != null) {
								$sachat("#typeon"+data.userTyping).html(data.userTypingSay);
							}
							if (data != null && data.userTypingSay == null) {
								$sachat("#typeon"+data.userTyping).html(\' \');
							}
							if (data != null && data.SENTMSGTIME != null) {
								$sachat("#sent"+data.SENTMSGID).html(\'<br />\'+data.SENTMSGTIME+\'<br /><br />\');
							}
							if (data != null && data.buddySESSION != null) {
								$sachat("#session"+data.userTyping).html(\'<img id="extraimg" src="'.LoadImage('bullet_green.png').'" width="17" height="17" alt="" border="0">\');
							}
							if (data != null && data.buddySESSION == null) {
								$sachat("#session"+data.userTyping).html(\'<img id="extraimg" src="'.LoadImage('bullet_red.png').'" width="17" height="17" alt="" border="0">\');
							}
							if (data != null && data.CONLINE != null) {
								$sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
							}
							if (data != null && data.ONLINE != null) {
								$sachat("#sa_friends").html(data.ONLINE);
							} 
							if (data != null && data.SENTMSGTIME != null) {
								$sachat("#sent"+data.SENTMSGID).html(\'<br />\'+data.SENTMSGTIME+\'<br /><br />\');
							}
						}
					});
				
					var myArray = [];
					myArray[0] = \''.$modSettings['2sichat_cookie_name'].'\';
					myArray[1] = \'msg_win\'+DId;
					myArray[2] = DId;
					myArray[3] = \''.$member_id.'\'
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+DId, escape(myArray.join(\',\')));
				}
			}
			
			//TODO:
			//searhes the chatbox slider for chat boxes closes/opens the one clicked minimises the rest.
			//TODO:
			function minChat(id){
				var $boxids = $sachat(\'.chatBoxslider .chatbox\');
				
				$boxids.each(function(index,value){
					if(value.id != id){
						if ($sachat(\'#cmsg\'+value.id).is(\':visible\')){
							upDownchat(value.id);
						}
					}else{
						upDownchat(value.id);
					}
					//console.log(index + \':\' + value.id); 
				});
			}
			function upDownchat(id) {
				if (!$sachat(\'#cmsg\'+id).is(\':visible\')){
					$sachat(\'#cmsg\'+id).show();
					$sachat(\'#bddy\'+id).show();
					$sachat("#new"+id).hide();
					$sachat(\'#ch\'+id+\' .chatboxoptions\').show();
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id, null);
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min_new\'+id, null);
					$sachat(\'#ch\'+id).addClass(\'chatboxhead\');
					$sachat(\'#ch\'+id).removeClass(\'chatboxheadmin\');
					$sachat(\'#\'+id).removeClass(\'chatboxmin\');
				    
					updateChatBoxPosition();
					$sachat("#"+id+" .chatboxcontent").animate({ scrollTop: $sachat("#"+id+" .chatboxcontent")[0].scrollHeight }, 1000);
				
				} else if ($sachat(\'#cmsg\'+id).is(\':visible\')){
					$sachat(\'#ch\'+id+\' .chatboxoptions\').hide();
					$sachat(\'#cmsg\'+id).hide();
					$sachat(\'#bddy\'+id).hide();
					$sachat(\'#ch\'+id).removeClass(\'chatboxhead\');
					$sachat(\'#ch\'+id).addClass(\'chatboxheadmin\');
					$sachat(\'#\'+id).addClass(\'chatboxmin\');
					updateChatBoxPosition();
				
					var myArray = [];
					myArray[0] = \''.$modSettings['2sichat_cookie_name'].'_min\';
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id, escape(myArray.join(\',\')));
				}
			}
			
			function xchat(id) {
				$sachat(\'#\'+id).hide();
				updateChatBoxPosition();
				if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id)){
					$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id, null);	
				}
				if (window["re" + id]) {
					clearInterval(window["re" + id]);
				}
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+id, null);	
				$sachat.post("'.$boardurl.'/sachat/index.php?action=closechat",{},function(data){});	
			}
			
			function jsubmit(id){
			   
				var textbox = \'msg\'+id;
				var msg = document.getElementById(textbox).value;
				document.getElementById(textbox).value = \'\';
				$sachat("#"+id+" .chatboxcontent").animate({ scrollTop: $sachat("#"+id+" .chatboxcontent")[0].scrollHeight }, 1000);
				$sachat.ajax({
					url: \''.$boardurl.'/sachat/index.php\',
					data: \''.$thjs.'cid=\'+id+\'&msg=\'+encodeURIComponent(msg),
					dataType: "json",
					cache: false,
					success: function(data){
						if (data.DATA != null){
							var newdiv = document.createElement(\'div\');
							newdiv.setAttribute(\'dir\',\'ltr\');
							newdiv.innerHTML = data.DATA;
							document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).lastChild);
							loadsnd(\'snd_msg\');
							newMessagesWin[data.NAME] = false;
							newmsg_says();
						}
						if (data != null && data.CONLINE != null) {
							$sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null) {
							$sachat("#sa_friends").html(data.ONLINE);
						} 
					}
				});
				
				HeartbeatTime = minHeartbeat;
				HeartbeatCount = 1;
			}';
			}

			// Guest and Members JavaScript
			$context['HTML'].= '

			doCookies();
			function doCookies() {
			
				$sachat.each(document.cookie.split(\';\'), function(i, cookie) {
						
				var c = $sachat.trim(cookie);
				name = c.split(\'=\')[0];
				var cookie = unescape($sachat.cookie(name));
				cSession = cookie.split(\',\');
						
				
				
				if(cSession[0] == \''.$modSettings['2sichat_cookie_name'].'\'){
					if(cSession[3] == \''.$member_id.'\'){
						chatTo(cSession[1].substr(7));
					}
				}
			});
		}
			
			var expdate = new Date();
			expdate.setTime (expdate.getTime() +  (24 * 60 * 60 * 1000 * 365));
			';

			
			$context['HTML'].= '
			
			function hide_showChat() {
				if ($sachat(\'#chatonhover\').is(\':visible\')){
					$sachat(\'#chattools_containter\').hide();
					$sachat(\'#chatonhover\').hide();
					$sachat(\'#chat_containter\').removeClass(\'chatBar_containter\');
					$sachat(\'#extra\').hide();
					$sachat(\'#sa_friends\').hide();
					$sachat(\'.chatbox\').hide();
					$sachat(\'#hideimg\').attr(\'src\',\''.LoadImage('world_off.png').'\');
					$sachat(\'#hideimg\').attr(\'title\',\''.$txt['bar_showChat'].'\');
					$sachat(\'#chattools_containter\').removeClass(\'chat_tools_open\');
					$sachat(\'#chattools_containter\').addClass(\'chat_tools_containter\');
					$sachat(\'#opencog\').show();
					checked = 1;
				}else{
					$sachat(\'#chat_containter\').show();
					$sachat(\'#chatonhover\').show();
					$sachat(\'#chat_containter\').addClass(\'chatBar_containter\');
					$sachat(\'#chattools_containter\').show();
					$sachat(\'.chatbox\').show();
					$sachat(\'#hideimg\').attr(\'src\',\''.LoadImage('world_on.png').'\');
					$sachat(\'#hideimg\').attr(\'title\',\''.$txt['bar_hideChat'].'\');
					checked = 0;
				}
				
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_show\',checked ,{ expires: 10000});
				if(memberID){
					updateChatBoxPosition();
				}
			}
	
			$sachat("#chatcollapse_containter").on(\'click\',function() {
				hide_showChat();
			});
			
			$sachat("#sa_friends").on(\'click\', \'#chattools_containter\', function () {
				showhide(\'extra\');
			});
			$sachat("#chattools_containter").on(\'click\',function() {
				showhide(\'extra\');				
			});
			$sachat(".extraboxhead").on(\'click\',function() {
				showhide(\'extra\');	
			});
			function showhide(layer_ref) {
				
				if(document.getElementById(layer_ref).style.display == \'none\')
				{
					$sachat(document.getElementById(layer_ref)).fadeIn("fast");
					
					if(layer_ref == \'sa_friends\'){
						$sachat(\'#extra\').hide();
					
						$sachat(\'#chattools_containter\').removeClass(\'chat_tools_open\');
						$sachat(\'#chattools_containter\').addClass(\'chat_tools_containter\');
						$sachat(\'#opencog\').show();
					}
					if(layer_ref == \'extra\'){
						$sachat(\'#chat_containter\').show();
						$sachat(\'#sa_friends\').hide();
						$sachat(\'#chattools_containter\').removeClass(\'chat_tools_containter_hover\');
						$sachat(\'#chattools_containter\').removeClass(\'chat_tools_containter\');
						$sachat(\'#chattools_containter\').addClass(\'chat_tools_open\');
						$sachat(\'#opencog\').hide();
						
					}
				}
				else
				{
					$sachat(document.getElementById(layer_ref)).fadeOut("fast");
					
					if(layer_ref == \'sa_friends\'){
						$sachat(\'#chat_containter\').show();
					}
					if(layer_ref == \'extra\'){
						if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_list_keep\') == 1 && memberID){
							//$sachat(\'#chat_containter\').hide();
							$sachat(\'#sa_friends\').show();
						}
						$sachat(\'#chattools_containter\').removeClass(\'chat_tools_open\');
						$sachat(\'#chattools_containter\').addClass(\'chat_tools_containter\');
						$sachat(\'#opencog\').show();
						
					}
				}	
			}';
	}
?>