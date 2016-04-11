<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/

function template_twosichaterror(){

    template_show_list('chat_error');

}

function template_twosichatchmod(){
global $txt,$modSettings,$scripturl;

     if(isset($_GET['done'])){echo'<div class="information">'.$txt['2sichatchmod3'].'</div>';}

	 echo' 
	    <div class="cat_bar">
		    <h3 class="catbg">
			   '.$txt['2sichatmaintainopt'].'
		    </h3>
	    </div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			 '.$txt['2sichatmaintainopt1'].'<br /><br />
			   <button type="button" onclick="javascript:location.href = \''.$scripturl.'?action=admin;area=maintainsa;sa=maintain;opti\';">'.$txt['2sichatmaintainopt2'] .'</button>
			</div>
	    <span class="botslice"><span></span></span>
	</div><br />';
	
	 echo' 
	    <div class="cat_bar">
		    <h3 class="catbg">
			    '.$txt['2sichatmaintainpurge'].'
		    </h3>
	    </div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			  <div class="error">'.$txt['2sichatmaintainpurge1'].'.</div><br />
			   <button type="button" onclick="javascript:location.href = \''.$scripturl.'?action=admin;area=maintainsa;sa=maintain;purge\';">'.$txt['2sichatmaintainpurge2'].'</button>
			</div>
	    <span class="botslice"><span></span></span>
	</div>';

	  echo'<br /> 
	    <div class="cat_bar">
		    <h3 class="catbg">
			    '.$txt['2sichatmaintaincache'].'
		    </h3>
	    </div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			  '.$txt['2sichatmaintaincache1'].'<br /><br />
			     <button type="button" onclick="javascript:location.href = \''.$scripturl.'?action=admin;area=maintainsa;sa=maintain;cache\';">'.$txt['2sichatmaintaincache'].'</button>
			</div>
	    <span class="botslice"><span></span></span>
	</div>';
	//}
	 echo' <br />
	    <div class="cat_bar">
		    <h3 class="catbg">
			    '.$txt['2sichatmaintainfb'].'
		    </h3>
	    </div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			    '.$txt['2sichatmaintainfb1'].'<br /><br />
				<button type="button" onclick="javascript:location.href = \''.$scripturl.'?action=admin;area=maintainsa;sa=maintain;fixbar\';"> '.$txt['2sichatmaintainfb'].'</button>
			</div>
	    <span class="botslice"><span></span></span>
	</div>';
	
}

function template_twosichatplugin() {
global $modSettings, $txt, $scripturl, $context, $boarddir;
	 
	echo'<form action="', $scripturl, '?action=admin;area=sachat;sa=plugins;upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" onsubmit="return confirm(\'Install plugin?\');">';
	
	/*echo' 
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
				<div class="content">
					<div class="smalltext">'.$txt['2sichat_plugins5'].'</div><br />
					Upload a Plugin: <input type="file" name="plug_gz" id="plug_gz" value="plug_gz" size="40" onchange="this.form.copy.disabled = this.value != \'\'; this.form.theme_dir.disabled = this.value != \'\';" class="input_file" />
				<input type="submit" value="'.$txt['2sichat_theme13'].'" />
				</div>
	    <span class="botslice"><span></span></span>
		</div></form><br />';*/
		
	$DirPlug = glob($boarddir.'/sachat/Plugins/*/*_init.php');
	$NoDirPlug = glob($boarddir.'/sachat/Plugins/*_init.php');
	$AllPlug = array_merge($DirPlug,$NoDirPlug);
	foreach($AllPlug  as $plugin) {
		
		$comments = GetComments($plugin);
		
		if(!empty($comments)){
			foreach($comments as $comment){
				
				if($comment[0] == '@Name'){
					$plugin_name = $comment[1];
				}			
				if($comment[0] == '@Description'){
					$plugin_desc = $comment[1];
				}
				if($comment[0] == '@Author'){
					$plugin_author = $comment[1];
					$plugin_author_txt = str_replace('@','',$comment[0]);
				}
				if($comment[0] == '@Version'){
					$plugin_version = $comment[1];
					$plugin_version_txt = str_replace('@','',$comment[0]);
				}
				if($comment[0] == '@Author URL'){
					$plugin_url = $comment[1];
					$plugin_url_txt = str_replace('@','',$comment[0]);
				}
				if($comment[0] == '@Plugin ID'){
					$plugin_id = $comment[1];
					$plugin_id_txt = str_replace('@','',$comment[0]);
				}
			}
			
			$plug = $plugin_id;
			$plug = trim($plug);
			
			echo'<div class="cat_bar">
					<h3 class="catbg">
						<strong>'.$plugin_name.'</strong>';
						echo'<div class="floatright">';
							if(!empty($modSettings[$plug]))
								echo'<a href="'. $scripturl. '?action=admin;area=sachat;sa=plugins;disable_plugin='.$plugin_id.'">'.$txt['2sichat_plugins6'].'</a>';
							else
								echo'<a href="'. $scripturl. '?action=admin;area=sachat;sa=plugins;enable_plugin='.$plugin_id.'">'.$txt['2sichat_plugins7'].'</a>';
							
							echo' <a href="'. $scripturl. '?action=admin;area=sachat;sa=plugins;remove_plugin='.$plugin_id.';file='.$plugin.'">'.$txt['2sichat_plugins8'].'</a>';
						echo'</div>';
					echo'</h3>
				</div>'; 
				
			echo' <div class="windowbg2">
			<span class="topslice"><span></span></span>
				<div class="content">';	
					echo parse_bbc($plugin_desc);
							
					echo'<br />';
					echo'<br />
					<div class="smalltext">';
					
					echo '<strong>'.$plugin_id_txt.':</strong> '; 
					echo $plugin_id;
					
					echo' | '; 
					
					echo'<strong>'.$plugin_version_txt.':</strong> '; 
					echo $plugin_version;
							
					echo' | '; 
					
					echo'<strong>'.$plugin_author_txt.':</strong> '; 
					echo $plugin_author;
							
					echo' | '; 
					
					echo'<strong>'.$plugin_url_txt.':</strong> '; 
					echo '<a href="'.$plugin_url.'">'.$plugin_url.'</a>';
				
				echo'</div></div>
			<span class="botslice"><span></span></span>
			</div><br />';
		}
	}
}

function template_twosichatThemes(){
  
      global $context, $txt, $settings, $modSettings, $scripturl, $dirArray, $indexCount;
      
	  if(isset($_GET['rdone'])) {
	  echo'<div class="information">
	          <strong>'.$txt['2sichat_theme28'].' '.$_GET['rdone'].'</strong>
      </div>';
	  }
	  if(isset($_GET['done'])) {
	  echo'<div class="information">
	          <strong>'.$txt['2sichat_theme4'].' '.$modSettings['2sichat_theme'].'</strong>
      </div>';
	  }
	  if(isset($_GET['udone'])) {
	  echo'<div class="information">
	          <strong>'.$txt['2sichat_theme5'].' '.$_GET['udone'].'</strong>
      </div>';
	  }

	 
	  echo'<form action="', $scripturl, '?action=admin;area=themesa;sa=theme;upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" onsubmit="return confirm(\'', $txt['theme_install_new_confirm'], '\');">';
	  echo'
	  <div class="cat_bar">
		<h3 class="catbg">
			<strong> <strong>'.$txt['2sichat_theme9'].'</strong></strong>
		</h3>
	</div>'; 
	echo' <div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			<div class="smalltext">'.$txt['2sichat_theme10'].'</div>
			 <br />'.$txt['2sichat_theme11'].'
					   <input type="file" name="theme_gz" id="theme_gz" value="theme_gz" size="40" onchange="this.form.copy.disabled = this.value != \'\'; this.form.theme_dir.disabled = this.value != \'\';" class="input_file" />
				 <input type="submit" value="'.$txt['2sichat_theme13'].'" />
				<br /> <br /><div class="smalltext"><strong>'.$txt['2sichat_theme12'].'</strong> zip</div>
	        <input type="hidden" name="sc" value="'.$context['session_id'].'" /><br /><br />
		</div>
	    <span class="botslice"><span></span></span>
	</div></form>';
	  
	 echo'<form action="', $scripturl, '?action=admin;area=sachat;sa=theme;copy" method="post" onsubmit="return confirm(\'', $txt['theme_install_new_confirm'], '\');">';
	echo'<br />
	  <div class="cat_bar">
		<h3 class="catbg">
			 <strong>'.$txt['2sichat_theme16'].'</strong>
		</h3>
	</div>'; 
	echo' <div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			<div class="smalltext">'.$txt['2sichat_theme17'].'</div>
			<br />'.$txt['2sichat_theme18'].'
					 <input type="text" name="copy" id="copy" value="" size="40" class="input_text" />
				 <input type="submit" value="'.$txt['2sichat_theme19'].'" />
	        <input type="hidden" name="sc" value="'.$context['session_id'].'" /><br /><br />
		</div>
	    <span class="botslice"><span></span></span>
	</div></form>';
	 
	 echo'<br />
	  <div class="cat_bar">
		<h3 class="catbg">
			 <strong>'.$txt['2sichat_theme23'].'</strong>
		</h3>
	</div>'; 
	echo' <div class="windowbg2">
		<span class="topslice"><span></span></span>
	        <div class="content">
			<div class="smalltext">'.$txt['2sichat_theme24'].'</div>
	                   <br /><strong>'.$txt['2sichat_theme25'].'</strong><br />';
			if(!empty($dirArray)){
				for($index=0; $index < $indexCount; $index++) {
                    if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php"){ // dont list hidden files
				        if ($dirArray[$index] == 'default' || $dirArray[$index] == 'default_new'){ //these should not be deleted
				          echo' '.$dirArray[$index].' <br />';
	                    }
					    else{
					        echo' '.$dirArray[$index].' <a href="', $scripturl, '?action=admin;area=sachat;sa=theme;remove='.$dirArray[$index].'" onclick="return confirm(\''.$txt['2sichat_theme26'].'\');"><img src="', $settings['default_images_url'], '/pm_recipient_delete.gif" alt="'.$txt['2sichat_theme27'].'" /></a><br />';
	                    }
				    }
                }
			}		 
				echo'
		</div>
	    <span class="botslice"><span></span></span>
	</div>';	  
}
?>