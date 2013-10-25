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
		var state = \'none\';
		var ie=document.all;
		var nn6=document.getElementById&&!document.all;
		var isIE = /*@cc_on!@*/false;

		var isdrag=false;
		var cSession;
		var x,y;
		var dobj;
		var cwh;
		var cww;
		var zdex = 100;
		zdex = zdex * 1;
		var aelem = \'none\';

		var css=document.createElement("link");
		css.setAttribute("rel", "stylesheet");
		css.setAttribute("type", "text/css");
		css.setAttribute("href", "'.$themeurl.'/style.css");
		document.documentElement.getElementsByTagName("HEAD")[0].appendChild(css);
        
		'.(!empty($modSettings['2sichat_dis_bar']) ? '':'
		var div = document.createElement(\'div\');
		div.setAttribute(\'id\',\'chat_containter\');
		div.setAttribute(\'dir\',\'ltr\');
		div.style.zIndex = 1000;
		div.innerHTML = \''.$bar.'\';
		document.body.appendChild(div);
		');
		
		$context['HTML'].= '
		var div = document.createElement(\'div\');
		div.setAttribute(\'id\',\'extra\');
		div.setAttribute(\'dir\',\'ltr\');
		div.style.display = \'none\';
		div.style.zIndex = 1000;
		div.innerHTML = \''.$extra.'\';
		document.body.appendChild(div);';
		

		// Members only JavaScript
		if ($member_id) {
			$context['HTML'].= '
		var div = document.createElement(\'div\');
		div.setAttribute(\'id\',\'friends\');
		div.setAttribute(\'dir\',\'ltr\');
		div.style.display = \'none\';
		div.style.zIndex = 1000;
    		div.innerHTML =\''.$buddies.'\';
		document.body.appendChild(div);

		setInterval("updatebar()",'.$modSettings['2sichat_mn_heart'].');

		updatebar();

		function updatebar() {
			jQuery.noConflict()(function($){
			document.getElementById("test").src = \''.$themeurl.'/images/ajax-loader.gif\';
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
                            document.getElementById(\'cfriends\').innerHTML =\'(\'+data.CONLINE+\')\';
						}
						if (data != null && data.ONLINE != null) {
							document.getElementById(\'friends\').innerHTML = data.ONLINE;
						} 
						
						document.getElementById("test").src = \''.$themeurl.'/images/arrow_refresh.png\';
					}
				});
			});
		}

		function chatSnd() {
			var mute = getCookie(\'chatSnd\');
			if (mute != null){
				document.getElementById("chat_Snd").src = \''.$themeurl.'/images/mute1.png\';
				delCookie(\'chatSnd\');
			} else {
				document.getElementById("chat_Snd").src = \''.$themeurl.'/images/mute2.png\';
				
				var cookie = new setCookie(\'chatSnd\', \'1\', expdate);
			}
		}

		function loadsnd(snd){

          	var mute = getCookie(\'chatSnd\');
          	
          	if (mute == null) {
				if (document.getElementById(\'csnd\')){
					var obj = document.getElementById(\'csnd\');
					obj.parentNode.removeChild(obj);
				}
				if (isIE) {
					var embed = document.createElement(\'embed\');
					embed.setAttribute(\'id\', \'csnd\');
					embed.src = \''.$boardurl.'/sachat/initsound.swf?snd='.$themeurl.'/sounds/\'+snd;
					embed.width = \'1\';
					embed.height = \'1\';
					embed.type = \'application/x-shockwave-flash\';
					document.body.appendChild(embed);
				}else{
					var obj = document.createElement(\'object\');
					obj.setAttribute(\'id\', \'csnd\');
					obj.classid = \'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\';
					obj.codebase = \'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\';
					obj.width = \'1\';
					obj.height = \'1\';
					obj.data = \''.$boardurl.'/sachat/initsound.swf?snd='.$themeurl.'/sounds/\'+snd;
					document.body.appendChild(obj);
				}
			}
		}

		function chatTo(id) {

			var DId = arguments[0];
			if (document.getElementById(DId) == undefined && DId != undefined) {

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
					            delCookieArray(\'min\' + id);
							}
							
							if (data != null && data.CONLINE != null) {
                                document.getElementById(\'cfriends\').innerHTML =\'(\'+data.CONLINE+\')\';;
						    }
							if (data != null && data.ONLINE != null) {
								document.getElementById(\'friends\').innerHTML = data.ONLINE;
							}
						}
					});
					
				});

				jQuery.noConflict()(function($){
					cwh = $(\'#\'+id).height();
					cww = $(\'#\'+id).width();
					
				});

				'.(!empty($modSettings['2sichat_cw_h_enable']) ? 'heartbeat(id);':'').'
				if (cSession == undefined) {
                    	var session = new setCookieArray(\'msg_win\'+DId, \'2sichat\', DId);
				}else{
					var session = new setCookieArray(\'msg_win\'+DId, \'2sichat\', DId, cSession[3], cSession[4]);
				}
			 }
		}

		function minchat(id, name) {

			var div = document.getElementById(arguments[0]);
			div.parentNode.removeChild(div);

			if (window["re" + id]) {
				clearInterval(window["re" + id]);
			}
			delCookieArray(\'msg_win\' + arguments[0]);
			
			var tsting = \'minchats\'+ id;

			var session = new setCookieArray(\'min\'+id, \'2sichat_min\',+id, name);
			
			jQuery.noConflict()(function($){
			    if (document.getElementById(\'minchats\'+id) == undefined){
                    $(\'#minchats\').append(\'<span id="\'+tsting+\'">&nbsp;<a class="white" href="javascript:void(0)" onclick="javascript:chatTo(\'+id+\');return false;"><strong>\' + name + \'</strong></a>&nbsp;</span>\');
			    }
			});
		}
		
		function xchat(id) {

			var div = document.getElementById(arguments[0]);
			div.parentNode.removeChild(div);
			
			if (window["re" + id]) {
				clearInterval(window["re" + id]);
			}
			delCookieArray(\'msg_win\' + arguments[0]);
			delCookieArray(\'min\' + id);
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
						if (data != null && data.ONLINE != null) {
							document.getElementById(\'friends\').innerHTML = data.ONLINE;
						}
						if (data != null && data.CONLINE != null) {
                            document.getElementById(\'cfriends\').innerHTML =\'(\'+data.CONLINE+\')\';
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
							if (data != null && data.ONLINE != null) {
								document.getElementById(\'friends\').innerHTML = data.ONLINE;
							}
							if (data != null && data.CONLINE != null) {
                                document.getElementById(\'cfriends\').innerHTML =\'(\'+data.CONLINE+\')\';;
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
			eval (\'window["re" + id] = setInterval("updatemsg(\'+id+\')",'.$modSettings['2sichat_cw_heart'].');\')
		}';
	 	}

		// Guest and Members JavaScript
		$context['HTML'].= '
		function movemouse(e){
			if (isdrag){
				style1 = nn6 ? tx + e.clientX - x : tx + event.clientX - x;
				style2 = nn6 ? ty + e.clientY - y : ty + event.clientY - y;
				style3 = nn6 ? dh - e.clientY + y : dh - event.clientY + y;
				style4 = nn6 ? dw - e.clientX + x : dw - event.clientX + x;
  				dobj.style.left = style1+\'px\';
  				dobj.style.top = style2+\'px\';
  				dobj.style.bottom = style3+\'px\';
  				dobj.style.right = style4+\'px\';
				gadFix = dobj.id.substr(0, 6);
				if (gadFix != \'Gadget\') {
	  				var session = new setCookieArray(\'msg_win\'+dobj.id, \'2sichat\', dobj.id, style1, style2);
			  	}
			  	if (gadFix == \'Gadget\') {
					var session = new setCookieArray(dobj.id, \'2sichat_gadget\', dobj.id, style1, style2);
				}
				return false;
			}
		}

		function selectmouse(e){
			var fobj = nn6 ? e.target : event.srcElement;

			while (fobj.tagName != "HTML" && fobj.className != "msg_win" && fobj.className != "msg_container"){
				fobj = nn6 ? fobj.parentNode : fobj.parentElement;
			}


			if (fobj.className=="msg_win"){
				jQuery.noConflict()(function($){
					var dpos = $(\'#\'+fobj.id).viewportOffset();
					var vwidth = $(window).width();
					var vheight = $(window).height();
					dobj = fobj;
					tx = parseInt(dobj.style.left+dpos.left);
					ty = parseInt(dobj.style.top+dpos.top);
					th = parseInt(dpos.top+cwh);
					dh = parseInt(vheight - th);
					tw = parseInt(dpos.left+cww);
					dw = parseInt(vwidth - tw);
				});
				isdrag = true;
				zdex = (zdex+1);
				dobj.style.zIndex = zdex;
				x = nn6 ? e.clientX : event.clientX;
				y = nn6 ? e.clientY : event.clientY;
				document.onmousemove=movemouse;
				document.body.onselectstart = function (){if (isdrag){return false;} else {return true;}}
			}
		}

		document.onmousedown=selectmouse;
		document.onmouseup=new Function("isdrag=false");
		document.body.onresize = function (){}

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
							if (data != null && data.ONLINE != null) {
								document.getElementById(\'friends\').innerHTML = data.ONLINE;
							}
							if (data != null && data.CONLINE != null) {
                                document.getElementById(\'cfriends\').innerHTML =\'(\'+data.CONLINE+\')\';;
						    }
						}
						
					});
				});
				
                if(document.getElementById("extra").style.display == \'block\'){
				    showhide(\'extra\');
				}
				
				if (cSession == undefined) {
                    	var session = new setCookieArray(\'Gadget\'+id, \'2sichat_gadget\', \'Gadget\'+id);
				}else{
					var session = new setCookieArray(\'Gadget\'+id, \'2sichat_gadget\', \'Gadget\'+id, cSession[3], cSession[4]);
				}

				jQuery.noConflict()(function($){
					cwh = $(\'#Gadget\'+id).height();
					cww = $(\'#Gadget\'+id).width();
				});
			 }
		}
		function closeGadget(id) {
			var div = document.getElementById(\'Gadget\'+id);
			div.parentNode.removeChild(div);
			delCookieArray(\'Gadget\' + arguments[0]);
		}

		function doScripts(e){

			if (e.nodeType != 1) {
				return;
			}

			if (e.tagName.toLowerCase() == \'script\') {
				var s = document.createElement(\'script\');
				s.setAttribute(\'type\', \'text/javascript\');
				if (e.src != null) {
					s.setAttribute(\'src\', e.src);
				}
				if (e.text != null) {
					s.text= e.text;
				}
				e.parentNode.insertBefore(s, e);
			} else {
				var n = e.firstChild;
				while(n) {
					doScripts(n);
					n = n.nextSibling;
				}
			}
		}

		doCookies();
		function doCookies() {
			jQuery.noConflict()(function($){
				$.each(document.cookie.split(\';\'), function(i, cookie) {
					var c = $.trim(cookie), name = c.split(\'=\')[0], value = c.split(\'=\')[1];
					var cname = name.substring(0, name.length - 1);
			    		var cSession = new getCookieArray(cname);
						
			    	if (cSession[1] == \'2sichat\') {
						chatTo(cSession[2]);
						document.getElementById(cSession[2]).style.left = cSession[3]+\'px\';
						document.getElementById(cSession[2]).style.top = cSession[4]+\'px\';
					}
					if (cSession[1] == \'2sichat_gadget\') {
						openGadget(cSession[2].substr(6));
						document.getElementById(cSession[2]).style.left = cSession[3]+\'px\';
						document.getElementById(cSession[2]).style.top = cSession[4]+\'px\';
					}
					
					if (cSession[1] == \'2sichat_min\' && cSession[3] != \'NaN\') {
					    var tsting = \'minchats\'+cSession[2];
					    if (document.getElementById(\'minchats\'+cSession[2]) == undefined){
					       $(\'#minchats\').append(\'<span id="\'+tsting+\'">&nbsp;<a class="white" href="javascript:void(0)" onclick="javascript:chatTo(\'+cSession[2]+\');return false;"><strong>\'+cSession[3]+\'</strong></a>&nbsp;</span>\');
					    }
					}
					
				});
			 });
		}

		function getCookieArray(name){
			var i = 0;
			while (getCookie(name + i) != null) {
				this[i + 1] = getCookie(name + i);
				i++;
				this.length = i;
			}
		}

		function setCookieArray(name){
			this.length = setCookieArray.arguments.length - 1;
			for (var i = 0; i < this.length; i++) {
				data = setCookieArray.arguments[i + 1];
				setCookie(name + i, data, expdate);
			}
		}

		function delCookieArray(name){
			var i = 0;
			while (getCookie(name + i) != null) {
				var expireNow = new Date();
				document.cookie = name + i + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" +  "; path=/";
				i++;
			}
		}

		function getCookie(name) {
			var dcookie = document.cookie;
			var cname = name + "=";
			var clen = dcookie.length;
			var cbegin = 0;
				while (cbegin < clen) {
					var vbegin = cbegin + cname.length;
					if (dcookie.substring(cbegin, vbegin) == cname) {
						var vend = dcookie.indexOf (";", vbegin);
						if (vend == -1) vend = clen;
							return unescape(dcookie.substring(vbegin, vend));
						}
						cbegin = dcookie.indexOf(" ", cbegin) + 1;
						if (cbegin == 0) break;
					}
			return null;
		}

		function setCookie(name, value, expires) {
			if (!expires) expires = new Date();
			document.cookie = name + "=" + escape (value) + "; expires=" + expires.toGMTString() +  "; path=/";
		}

		function delCookie(name) {
			var expireNow = new Date();
			document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" +  "; path=/";
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
					    document.getElementById("extraimg").src = \''.$themeurl.'/images/control_eject_blue1.png\';
					}
                }
                else
                {
                    $(document.getElementById(layer_ref)).fadeOut("slow");
					if(layer_ref == \'extra\'){
					    document.getElementById("extraimg").src = \''.$themeurl.'/images/control_eject_blue.png\';
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