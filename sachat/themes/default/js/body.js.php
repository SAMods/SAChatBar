<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
function initchat() {

	global $user_settings, $budCount, $member_id, $usershowBar,$buddy_settings, $boardurl, $options, $modSettings, $themeurl, $thjs, $context;

	if ($member_id && empty($modSettings['2sichat_dis_bar'])) {
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", chat_bar_template()));
		$buddies = addslashes(preg_replace("/\r?\n?\t/m", "", genMemList('list')));
			
	} elseif (empty($modSettings['2sichat_dis_bar']) && !$member_id) {
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", guest_bar_template()));
	}

	$extra = addslashes(preg_replace("/\r?\n?\t/m", "", chat_extra_template()));

	$context['HTML'] = '
		var msgArray = new Array();
		var ie=document.all;
		var isIE = /*@cc_on!@*/false;
		var cSession;
		var minimised = 0;
		var zdex = 100;
		zdex = zdex * 1;
		var blinkOrder = 0;
		var newMessagesWin = new Array();
		originalTitle = document.title;
		var HeartbeatCount = 0;
        var minHeartbeat = '.$modSettings['2sichat_mn_heart'].';
        var maxHeartbeat = '.$modSettings['2sichat_mn_heartmin'].';
		var HeartbeatTime = minHeartbeat;
		var itemsfound = 0;
		var $sachat = jQuery.noConflict();
		var windowFocus = true;
		
	    $sachat(\'head\').append(\'<link rel="stylesheet" href="'.$themeurl.'/style.css" type="text/css" />\');
		
		'.(!empty($modSettings['2sichat_dis_bar']) || !empty($usershowBar) ? '':'
		        $sachat("<div />").attr("id","chat_containter").attr("class","chatBar_containter").attr("dir","ltr").attr("style","zIndex: 1000").html(\''.$bar.'\').appendTo($sachat("body"));
		');
		
		$context['HTML'].= '
		    $sachat("<div />" ).attr("id","extra").attr("class","extrabox").attr("dir","ltr").attr("style","zIndex: 1000; display: none").html(\''.$extra.'\').appendTo($sachat("body"));
		';
		

		// Members only JavaScript
		if ($member_id) {
			$context['HTML'].= '
		$sachat("<div />" ).attr("id","friends").attr("class","buddybox").attr("dir","ltr").attr("style","zIndex: 1000; display: none").html(\''.$buddies.'\').appendTo($sachat("body"));
		
		//setup the basic html template structer for the chat boxes
		$sachat("<div />").attr("id","ChatBoxtemplate").attr("dir","ltr").appendTo($sachat("body"));
		$sachat("<div />").attr("dir","ltr").attr("class","chatbox").attr("style","position: fixed;").appendTo($sachat("#ChatBoxtemplate"));
		$sachat("<div />" ).attr("class","chatBoxWrap").appendTo($sachat("body"));
		$sachat("<div />" ).attr("class","chatBoxslider").appendTo($sachat(".chatBoxWrap"));
		$sachat("<div />" ).attr("id","slideLeft").html(\'<img src="'.$themeurl.'/images/arrow_right.png" />\').appendTo($sachat("body"));
		$sachat("<div />" ).attr("id","slideRight").html(\'<img src="'.$themeurl.'/images/arrow_left.png" />\').appendTo($sachat("body"));
			
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
		
		$sachat(document).on(\'click\', \'.chatbox\', function () {
			for (x in newMessagesWin) {
				newMessagesWin[x] = false;
			}
        });
		
		var doUpdate = function () {
			updatebar();
			setTimeout(doUpdate, HeartbeatTime);
		};
		doUpdate();
		
		function updatebar() {
			
			$sachat(\'#test\').attr(\'src\',\''.$themeurl.'/images/ajax-loader.gif\');
			
			'.(!empty($modSettings['2sichat_live_notfy']) ? 'newmsg_says();' : '').'
			
			$sachat.ajax({
				url: \''.$boardurl.'/sachat/index.php\',
				data: \'action=heart\',
				dataType: "json",
				cache: false,
				timeout: '.$modSettings['2sichat_mn_heart_timeout'].',
				success: function(data){
					if (data != null && data.ids != null){
						jQuery.each(data.ids, function() {
							
						    $sachat("#typeon"+this).html(\' \');
							
						    if (!document.getElementById(\'cmsg\'+this) && this != null) {
								chatTo(this);
							    loadsnd(\'new_msg\');
							}
				           
							if (document.getElementById(\'cmsg\'+this) && this != null) {
								updatemsg(this);
								
								if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+this)){
									$sachat(\'#cmsg\'+this).show();
									$sachat(\'#bddy\'+this).show();
								}
                                							
							}
						    itemsfound += 1;
						});
					}

					if (data != null && data.buddySESSION != null) {
						$sachat("#session"+data.userTyping).html(\'<span class="green">*&nbsp;</span>\');
					}
					if (data != null && data.buddySESSION == null) {
						$sachat("#session"+data.userTyping).html(\'<span class="red">*&nbsp;</span>\');
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
					if (data != null && data.ONLINE != null) {
						$sachat("#friends").html(data.ONLINE);
					}
					if (data != null && data.ONLINER != null) {
						$sachat(".chatroominvite").html(data.ONLINER);
					}
					if (data.ONLINER == null) {
						$sachat(".chatroominvite").html(\'\');
					}

					$sachat(\'#test\').attr(\'src\',\''.$themeurl.'/images/arrow_refresh.png\');
					
					heartbeattimeout();	
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
		}
		
		function newmsg_says(){
			var blinkNumber = 0;
			var titleChanged = 0;
			
			for (x in newMessagesWin) {
				if (newMessagesWin[x] == true) {
					++blinkNumber;
					if (blinkNumber >= blinkOrder) {
						document.title = x+\' says...\';
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
				$sachat(\'#chat_Snd\').attr(\'src\',\''.$themeurl.'/images/mute1.png\');
		        $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\',null);
			} 
			else {
				$sachat(\'#chat_Snd\').attr(\'src\',\''.$themeurl.'/images/mute2.png\');
		        $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\',\'1\',{ expires: expdate});
			}
		}

		function loadsnd(snd){
		    mute = $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_chatSnd\');
		    if(!mute){
				var sound = new Audio(\''.$themeurl.'/sounds/\'+snd+\'.mp3\');
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
		
	    function chatTo(id, minimised) {
            var DId = arguments[0];
			
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
					data: \''.$thjs.'cid=\'+DId,
					dataType: "json",
					cache: false,
					success: function(data){
						if (data.DATA != null) {
						    $sachat($sachat(\'#\'+id)).attr("id",+data.BID);
							$sachat($sachat(\'#\'+id)).html(data.DATA);
							
							$sachat("#"+data.BID).css(\'bottom\', \'27px\');
							
							$sachat(\'.slidedown\').show();
							$sachat(\'.slideup\').hide();
							
							if($sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id)){
								upDownchat(id);
							}
							
							updateChatBoxPosition();
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
							$sachat("#session"+data.userTyping).html(\'<span class="green">*&nbsp;</span>\');
						}
						if (data != null && data.buddySESSION == null) {
							$sachat("#session"+data.userTyping).html(\'<span class="red">*&nbsp;</span>\');
						}
						if (data != null && data.CONLINE != null) {
						    $sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null) {
							$sachat("#friends").html(data.ONLINE);
						} 
						if (data != null && data.SENTMSGTIME != null) {
						$sachat("#sent"+data.SENTMSGID).html(\'<br />\'+data.SENTMSGTIME+\'<br /><br />\');
					}
					}
				});
				'.(!empty($modSettings['2sichat_cw_h_enable']) ? 'heartbeat(id);':'').'
			
			    var myArray = [];
                myArray[0] = \''.$modSettings['2sichat_cookie_name'].'\';
			    myArray[1] = \'msg_win\'+DId;
		        myArray[2] = DId;
		        $sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'\'+DId, escape(myArray.join(\',\')));
			}
		}
		
		function upDownchat(id) {
			if (!$sachat(\'#cmsg\'+id).is(\':visible\')){
				$sachat(\'#cmsg\'+id).show();
				$sachat(\'#bddy\'+id).show();
				$sachat(\'#slideup\'+id).hide();
				$sachat(\'#slidedown\'+id).show();
				$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_min\'+id, null);
			
			} else if ($sachat(\'#cmsg\'+id).is(\':visible\')){
				$sachat(\'#cmsg\'+id).hide();
				$sachat(\'#bddy\'+id).hide();
				$sachat(\'#slideup\'+id).show();
				$sachat(\'#slidedown\'+id).hide();
				
				if (window["re" + id]) {
					clearInterval(window["re" + id]);
				}
			
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
						document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).firstChild);
						loadsnd(\'snd_msg\');
						newMessagesWin[data.NAME] = false;
						newmsg_says();
					}
					if (data != null && data.CONLINE != null) {
						$sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
					}
					if (data != null && data.ONLINE != null) {
					    $sachat("#friends").html(data.ONLINE);
					} 
				}
			});
			HeartbeatTime = minHeartbeat;
		    HeartbeatCount = 1;
		}

		function updatemsg(id){
			if (document.getElementById(\'cmsg\'+id)) {
		        
				$sachat.ajax({
					url: \''.$boardurl.'/sachat/index.php\',
					data: \''.$thjs.'update=\'+id,
					dataType: "json",
					cache: false,
					timeout: '.$modSettings['2sichat_mn_heart_timeout'].',
					success: function(data){
						if (data.DATA != null) {
							if (msgArray[id] && msgArray[id] < data.ID && data.ID != null || msgArray[id] == undefined && data.ID != null) {
								var newdiv = document.createElement(\'div\');
								newdiv.setAttribute(\'dir\',\'ltr\');
								newdiv.innerHTML = data.DATA;
                                document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).firstChild);
								loadsnd(\'rec_msg\');
								msgArray[id] = data.ID;
						        newMessagesWin[data.NAME] = true;
								newmsg_says();
							}
						}
						if (data != null && data.SENTMSGTIME != null) {
							$sachat("#sent"+data.SENTMSGID).html(\'<br />\'+data.SENTMSGTIME+\'<br /><br />\');
						}
						if (data != null && data.buddySESSION != null) {
							$sachat("#session"+data.userTyping).html(\'<span class="green">*&nbsp;</span>\');
						}
						if (data != null && data.buddySESSION == null) {
							$sachat("#session"+data.userTyping).html(\'<span class="red">*&nbsp;</span>\');
						}
						if (data != null && data.CONLINE != null) {
						    $sachat("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null) {
							$sachat("#friends").html(data.ONLINE);
						} 
					}
				});
			}
		}

		function heartbeat(id){
			if (window["re" + id]) {
				clearInterval(window["re" + id]);
			}
			eval (\'window["re" + id] = setInterval("updatemsg(\'+id+\')",'.$modSettings['2sichat_cw_heart'].');\');
		}';
	 	}

		// Guest and Members JavaScript
		$context['HTML'].= '
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
							  $sachat("#friends").html(data.ONLINE);
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

		function closeGadget(id) {
			$sachat(\'#Gadget\'+id).remove();
			$sachat.cookie(\''.$modSettings['2sichat_cookie_name'].'_gadget\'+id, null);
		}

		doCookies();
		function doCookies() {
		
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
					
			if(cSession[0] == \''.$modSettings['2sichat_cookie_name'].'\'){
			    chatTo(cSession[1].substr(7));
				//document.getElementById(cSession[2]).style.left = cSession[3]+\'px\';
				//document.getElementById(cSession[2]).style.top = cSession[4]+\'px\';
			}
		});
	}
		
		var expdate = new Date();
		expdate.setTime (expdate.getTime() +  (24 * 60 * 60 * 1000 * 365));
		';

		if ($modSettings['2sichat_gad_trans']){
			$context['HTML'].= '
		$sachat.getScript(\'http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\');

		document.getElementById(\'2siTranslate\').innerHTML = \'<img class=\"langload\" src="'.$themeurl.'/images/loadingBar.gif"/>\';
		function googleTranslateElementInit() {
			document.getElementById(\'2siTranslate\').innerHTML = \'\';
			new google.translate.TranslateElement({pageLanguage:\''.$modSettings['2sichat_gad_lang'].'\'},\'2siTranslate\');
		}';
		}
		$context['HTML'].= '
		function showhide(layer_ref) {
			if(document.getElementById(layer_ref).style.display == \'none\')
            {
                $sachat(document.getElementById(layer_ref)).fadeIn("fast");
			    if(layer_ref == \'extra\'){
					$sachat(\'#extraimg\').attr(\'src\',\''.$themeurl.'/images/control_eject_blue1.png\');
				}
				if(layer_ref == \'chatroomlobby\'){
					 $sachat(document.getElementById(\'chatroomcreate\')).hide();
				}
				if(layer_ref == \'chatroomcreate\'){
					 $sachat(document.getElementById(\'chatroomlobby\')).hide();
				}
            }
            else
            {
                $sachat(document.getElementById(layer_ref)).fadeOut("slow");
				if(layer_ref == \'extra\'){
					 $sachat(\'#extraimg\').attr(\'src\',\''.$themeurl.'/images/control_eject_blue.png\');
				}
            }	
		}
		
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
}
?>