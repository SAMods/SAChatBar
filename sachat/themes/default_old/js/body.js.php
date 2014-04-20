<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
function initchat() {

	global $user_settings, $budCount, $member_id, $boardurl, $options, $modSettings, $themeurl, $thjs, $context;

	if ($member_id && empty($modSettings['2sichat_dis_bar'])) {
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", chat_bar_template()));
		$buddies = addslashes(preg_replace("/\r?\n?\t/m", "", genMemList('list')));
			
	} elseif (empty($modSettings['2sichat_dis_bar']) && !$member_id) {
		$bar = addslashes(preg_replace("/\r?\n?\t/m", "", guest_bar_template()));
	}

	$extra = addslashes(preg_replace("/\r?\n?\t/m", "", chat_extra_template()));

	$context['HTML'] = '
	
		image1 = new Image;
		image2 = new Image;
		image1.src = \''.$themeurl.'/images/x_inactive.png\';
		image2.src = \''.$themeurl.'/images/x_hover.png\';

		var msgArray = new Array();
		var ie=document.all;
		var isIE = /*@cc_on!@*/false;

		var cSession;
		var zdex = 100;
		zdex = zdex * 1;

		jQuery.noConflict()(function($){
		    $(\'head\').append(\'<link rel="stylesheet" href="'.$themeurl.'/style.css" type="text/css" />\');
        });
		
		'.(!empty($modSettings['2sichat_dis_bar']) ? '':'
		    jQuery.noConflict()(function($){
		        $("<div />" ).attr("id","chat_containter").attr("dir","ltr").attr("style","zIndex: 1000").html(\''.$bar.'\').appendTo($("body"));
		    });
		');
		
		$context['HTML'].= '
		    jQuery.noConflict()(function($){
		        $("<div />" ).attr("id","extra").attr("dir","ltr").attr("style","zIndex: 1000; display: none").html(\''.$extra.'\').appendTo($("body"));
		    });
		';
		

		// Members only JavaScript
		if ($member_id) {
			$context['HTML'].= '
		jQuery.noConflict()(function($){
		    $("<div />" ).attr("id","friends").attr("dir","ltr").attr("style","zIndex: 1000; display: none").html(\''.$buddies.'\').appendTo($("body"));
		});
	

		setInterval("updatebar()",'.$modSettings['2sichat_mn_heart'].');

		updatebar();

		function updatebar() {
			jQuery.noConflict()(function($){
			$(\'#test\').attr(\'src\',\''.$themeurl.'/images/ajax-loader.gif\');
				$.ajax({
					url: \''.$boardurl.'/sachat/index.php\',
					data: \'action=heart\',
					dataType: "json",
					cache: false,
					timeout: '.$modSettings['2sichat_mn_heart'].',
					success: function(data){
						if (data != null && data.ids != null){
							jQuery.each(data.ids, function() {
							    if (this != null) {
							        var chatmin1 = document.getElementById(\'minchats\'+this);
							    }							
								if (!chatmin1 && !document.getElementById(\'cmsg\'+this) && this != null) {
									chatTo(this);
									loadsnd(\'new_msg\');
								}
								if (document.getElementById(\'cmsg\'+this) && this != null) {
									updatemsg(this);
								}
								if(chatmin1){
								    $(\'#minchats\'+ this).fadeOut(\'1000\', function(){
                                    $(this).fadeIn(\'1000\', function(){});});
									chatmin1.style.backgroundColor = \'red\';
									chatmin1.style.margin += \'0.3em 2px 0em 2px\';
									loadsnd(\'new_msg\');
				                }
							});
						}
						if (data != null && data.CONLINE != null) {
						    $("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null) {
							$("#friends").html(data.ONLINE);
						} 
						
						$(\'#test\').attr(\'src\',\''.$themeurl.'/images/arrow_refresh.png\');
					}
				});
			});
		}

		function chatSnd() {
			
			jQuery.noConflict()(function($){
		        mute = $.cookie(\'chatSnd\');
			});
			
			if (mute == 1){
				jQuery.noConflict()(function($){
				    $(\'#chat_Snd\').attr(\'src\',\''.$themeurl.'/images/mute1.png\');
		            $.cookie(\'chatSnd\',null);
			    });
			} 
			else {
				jQuery.noConflict()(function($){
				    $(\'#chat_Snd\').attr(\'src\',\''.$themeurl.'/images/mute2.png\');
		            $.cookie(\'chatSnd\',\'1\',{ expires: expdate});
			    });
			}
		}

		function loadsnd(snd){
			jQuery.noConflict()(function($){
		        mute = $.cookie(\'chatSnd\');
				if(!mute){
				    $.playSound(\''.$themeurl.'/sounds/\'+snd);
				}
			});
		}

		function chatTo(id) {

			var DId = arguments[0];
			if (document.getElementById(DId) == undefined && DId != undefined) {

				var div = document.createElement(\'div\');

				div = document.createElement(\'div\');
                div.id = +DId;
				div.dir = \'ltr\';
				div.className = \'msg_win\';
				div.style.position = \'fixed\';
				zdex = (zdex+1);
				div.style.zIndex = zdex;
				
				document.body.appendChild(div);
			  
				jQuery.noConflict()(function($){
					$.ajax({
						url: \''.$boardurl.'/sachat/index.php\',
						data: \''.$thjs.'cid=\'+DId,
						dataType: "json",
						cache: false,
						success: function(data){
							if (data.DATA != null) {
								div.setAttribute(\'id\', data.BID); // For IE7&8 need to reset div id because arguments are not passed.
								div.innerHTML=data.DATA;
								// msgArray[id] = data.ID;
							} else {
								xchat(DId);
							}
							
							var chatmin = document.getElementById(\'minchats\'+ id);
                            if(chatmin){
							    chatmin.parentNode.removeChild(chatmin);
					            jQuery.noConflict()(function($){
				                    $.cookie(\'2sichat_min\'+id, null);
			                    });
							}
							
							if (data != null && data.CONLINE != null) {
						        $("#cfriends").text(\'(\'+data.CONLINE+\')\');
						    }
						    if (data != null && data.ONLINE != null) {
							    $("#friends").html(data.ONLINE);
						    } 
						}
					});
					
				});

				jQuery.noConflict()(function($){
		            $(window).load(selectmouse());
		        });

				'.(!empty($modSettings['2sichat_cw_h_enable']) ? 'heartbeat(id);':'').'
				if (cSession == undefined) {
				      var myArray = [];
                      myArray[0] = \'2sichat\';
					  myArray[1] = \'msg_win\'+DId;
				      myArray[2] = DId;
                      jQuery.noConflict()(function($){
					  $.cookie(\'2sichat\'+DId, escape(myArray.join(\',\')));
					  });
						
				}else{
					  var myArray = [];
					  myArray[0] = \'2sichat\';
                      myArray[1] = \'msg_win\'+DId;
					  myArray[2] = DId;
					  myArray[3] = cSession[3];
					  myArray[4] = cSession[4];
                      jQuery.noConflict()(function($){
					  $.cookie(\'2sichat\'+DId, escape(myArray.join(\',\')));
					  });
				}
			 }
		}
		
		function minchat(id, name) {

			jQuery.noConflict()(function($){
			    $(\'#\'+id).remove();
			});

			if (window["re" + id]) {
				clearInterval(window["re" + id]);
			}
			jQuery.noConflict()(function($){
			    $.cookie(\'2sichat\'+id, null);
			});
			
			var tsting = \'minchats\'+ id;

			var myArray = [];
			myArray[0] = \'2sichat_min\';
            myArray[1] = \'min\'+id;
		    myArray[2] = id;
			myArray[3] = name;
            jQuery.noConflict()(function($){
				$.cookie(\'2sichat_min\'+id, escape(myArray.join(\',\')));
			});
			
			jQuery.noConflict()(function($){
			    if (document.getElementById(\'minchats\'+id) == undefined){
                    $(\'#minchats\').append(\'<span id="\'+tsting+\'">&nbsp;<a class="white" href="javascript:void(0)" onclick="javascript:chatTo(\'+id+\');return false;"><strong>\' + name + \'</strong></a>&nbsp;</span>\');
			    }
			});
		}

		function xchat(id) {
		    jQuery.noConflict()(function($){
			    $(\'#\'+id).remove();
			    $.cookie(\'2sichat\'+id, null);
			});
		}

		function jsubmit(id){
			var textbox = \'msg\'+id;
		    	var msg = document.getElementById(textbox).value;
		    	document.getElementById(textbox).value = \'\';

			jQuery.noConflict()(function($){
				$.ajax({
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
						}
						if (data != null && data.CONLINE != null) {
						    $("#cfriends").text(\'(\'+data.CONLINE+\')\');
						}
						if (data != null && data.ONLINE != null) {
							$("#friends").html(data.ONLINE);
						} 
					}
				});
			});
		}

		function updatemsg(id){
			if (document.getElementById(\'cmsg\'+id)) {
				jQuery.noConflict()(function($){
					$.ajax({
						url: \''.$boardurl.'/sachat/index.php\',
						data: \''.$thjs.'update=\'+id,
						dataType: "json",
						cache: false,
						timeout: '.$modSettings['2sichat_cw_heart'].',
						success: function(data){
							if (data.DATA != null) {
								if (msgArray[id] && msgArray[id] < data.ID && data.ID != null || msgArray[id] == undefined && data.ID != null) {
									var newdiv = document.createElement(\'div\');
									newdiv.setAttribute(\'dir\',\'ltr\');
									newdiv.innerHTML = data.DATA;		
									document.getElementById(\'cmsg\'+id).insertBefore(newdiv, document.getElementById(\'cmsg\'+id).firstChild);
									loadsnd(\'rec_msg\');
									msgArray[id] = data.ID;
								}
							}
							if (data != null && data.CONLINE != null) {
						        $("#cfriends").text(\'(\'+data.CONLINE+\')\');
						    }
						    if (data != null && data.ONLINE != null) {
							    $("#friends").html(data.ONLINE);
						    } 
						}
					});
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
		function selectmouse(){
		       jQuery.noConflict()(function($){
				   $(\'.msg_win\').draggable({
                       drag: function(event, ui) {
						
						   newX = ui.offset.left;
                           newY = ui.offset.top;
						   
				           $(this).css(\'left\', newX);
                           $(this).css(\'top\', newY);
							
						   zdex = (zdex+1);
				           $(this).css(\'zIndex\', zdex);

                           cgobj = $(this).attr(\'id\');
					       gadid = cgobj.substr(6);
                           gadFix = cgobj.substr(0, 6);
                                
                           if (gadFix == \'Gadget\') {
						       var myArray = [];
					           myArray[0] = \'2sichat_gadget\';
					           myArray[1] = cgobj;
					           myArray[2] = gadid;
					           myArray[3] = newX;
					           myArray[4] = newY;

					           $.cookie(\'2sichat_gadget\'+gadid, escape(myArray.join(\',\')));
					       }
					       else{
					           var myArray = [];
                               myArray[0] = \'2sichat\';
					           myArray[1] = \'msg_win\'+cgobj;
				               myArray[2] = cgobj;
					           myArray[3] = newX;
					           myArray[4] = newY;
					           
							   $.cookie(\'2sichat\'+cgobj, escape(myArray.join(\',\')));
					   
					      }
                      }
                  });	
              });
          }

          function openGadget(id) {
			if (document.getElementById(\'Gadget\'+id) == undefined) {
				
				var div = document.createElement(\'div\');
				div.id = \'Gadget\'+id;
				div.dir = \'ltr\';
				div.className = \'msg_win\';
				div.style.position = \'fixed\';
				zdex = (zdex+1);
				div.style.zIndex = zdex;
				document.body.appendChild(div);

				jQuery.noConflict()(function($){
					$.ajax({
						url: \''.$boardurl.'/sachat/index.php\',
						data: \''.$thjs.'gid=\'+id,
						dataType: "json",
						cache: false,
						success: function(data){
							if (data.DATA != null){  
							   div.innerHTML = data.DATA
							} 
							if (data != null && data.CONLINE != null) {
						        $("#cfriends").text(\'(\'+data.CONLINE+\')\');
						    }
						    if (data != null && data.ONLINE != null) {
							    $("#friends").html(data.ONLINE);
						    } 
						}
					});
				});
                 
				jQuery.noConflict()(function($){
		            $(window).load(selectmouse());
		        });
				if(document.getElementById("extra").style.display == \'block\'){
				    showhide(\'extra\');
				}
				if (cSession == undefined) {
				      var myArray = [];
                      myArray[0] = \'2sichat_gadget\';
				      myArray[1] = \'Gadget\'+id;
					  myArray[2] = id;
                      jQuery.noConflict()(function($){
					  $.cookie(\'2sichat_gadget\'+id, escape(myArray.join(\',\')));
					  });
						
				}else{
					  var myArray = [];
                      myArray[0] = \'2sichat_gadget\';
					  myArray[1] = \'Gadget\'+id;
					  myArray[2] = id;
					  myArray[3] = cSession[3];
					  myArray[4] = cSession[4];
                      jQuery.noConflict()(function($){
					  $.cookie(\'2sichat_gadget\'+id, escape(myArray.join(\',\')));
					  });
				}

				jQuery.noConflict()(function($){
					cwh = $(\'#Gadget\'+id).height();
					cww = $(\'#Gadget\'+id).width();
				});
			 }
		}

		function closeGadget(id) {
			jQuery.noConflict()(function($){
			    $(\'#Gadget\'+id).remove();
			    $.cookie(\'2sichat_gadget\'+id, null);
			 });
		}

		doCookies();
		function doCookies() {
			jQuery.noConflict()(function($){
				$.each(document.cookie.split(\';\'), function(i, cookie) {
					
					var c = $.trim(cookie);
					name = c.split(\'=\')[0];
			    	var cookie = unescape($.cookie(name));
					cSession = cookie.split(\',\');
					
					if(cSession[0] == \'2sichat_gadget\'){
				         openGadget(cSession[1].substr(6));
				         document.getElementById(cSession[1]).style.left = cSession[3]+\'px\';
				         document.getElementById(cSession[1]).style.top = cSession[4]+\'px\';
					}
					
					if(cSession[0] == \'2sichat\'){
						chatTo(cSession[1].substr(7));
						document.getElementById(cSession[2]).style.left = cSession[3]+\'px\';
				        document.getElementById(cSession[2]).style.top = cSession[4]+\'px\';
					}
					
					if(cSession[0] == \'2sichat_min\'){
						var tsting = \'minchats\'+cSession[2];
			            if (cSession[3] != undefined){
				            if (document.getElementById(\'minchats\'+cSession[2]) == undefined){
			                    $(\'#minchats\').append(\'<span id="\'+tsting+\'">&nbsp;<a class="white" href="javascript:void(0)" onclick="javascript:chatTo(\'+cSession[2]+\');return false;"><strong>\'+cSession[3]+\'</strong></a>&nbsp;</span>\');
			                }  
                        }				
					}
				});
			});
		}
		
		var expdate = new Date();
		expdate.setTime (expdate.getTime() +  (24 * 60 * 60 * 1000 * 365));
		';

		if ($modSettings['2sichat_gad_trans']){
			$context['HTML'].= '
		jQuery.noConflict()(function($){
			$.getScript(\'http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\');
		})
		document.getElementById(\'2siTranslate\').innerHTML = \'<img class=\"langload\" src="'.$themeurl.'/images/loadingBar.gif"/>\';
		function googleTranslateElementInit() {
			document.getElementById(\'2siTranslate\').innerHTML = \'\';
			new google.translate.TranslateElement({pageLanguage:\''.$modSettings['2sichat_gad_lang'].'\'},\'2siTranslate\');
		}';
		}
		$context['HTML'].= '
		function showhide(layer_ref) {
			jQuery.noConflict()(function($){
			    if(document.getElementById(layer_ref).style.display == \'none\')
                {
                    $(document.getElementById(layer_ref)).fadeIn("fast");
					if(layer_ref == \'extra\'){
					    $(\'#extraimg\').attr(\'src\',\''.$themeurl.'/images/control_eject_blue1.png\');
					}
                }
                else
                {
                    $(document.getElementById(layer_ref)).fadeOut("slow");
					if(layer_ref == \'extra\'){
					     $(\'#extraimg\').attr(\'src\',\''.$themeurl.'/images/control_eject_blue.png\');
					}
                }	
			});
		}
		
        jQuery.noConflict()(function($){
			$.getScript(\'http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-503f263237ff99da\');
		})
		
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