<?

function template_twosichatGadgets(){

	global $context, $txt, $scripturl;

	echo '
		<table class="table_grid" cellspacing="0" width="100%">
			<tr class="catbg">
				<td align="left" class="windowbg2">'.$txt['2sichat_ord'].'</td>
				<td align="left" class="windowbg2">'.$txt['2sichat_title'].'</td>
				<td align="left" class="windowbg2">'.$txt['2sichat_vis'].'</td>
				<td align="left" class="windowbg2">Type</td>
			</tr>		';
	foreach ($context['gadgets'] as $row)
	  {
   echo'
			<tr class="windowbg">
				<td align="left" class="windowbg2">'.$row['ord'].'</td>
                    <td align="left" class="windowbg2">
					<strong>'.$row['title'].'</strong>
					<div style="float:right;padding-right:40px;">
						<button type="button" onclick="javascript:openGadget(\''.$row['id'].'\');">'.$txt['2sichat_preview'].'</button>
						<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=gadget;edit='.$row['id'].'\';">'.$txt['2sichat_edit'].'</button>
						<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=gadget;delete='.$row['id'].'\';">'.$txt['2sichat_delete'].'</button>
						<button type="button" onclick="javascript:ShowGadgetLink(\'GadgetCode'.$row['id'].'\')">'.$txt['2sichat_link'].'</button>
						<textarea id="GadgetCode'.$row['id'].'" rows="4" cols="28" style="display:none; overflow:hidden;"><a onclick="javascript:openGadget(\''.$row['id'].'\');return false;" href="javascript:void(0)">'.$row['title'].'</a></textarea>
	 				</div>
	 			</td>
                    <td align="left" class="windowbg2">
					'.($row['vis'] == 0 ? $txt['2sichat_vis0'] : '').'
					'.($row['vis'] == 1 ? $txt['2sichat_vis1'] : '').'
					'.($row['vis'] == 2 ? $txt['2sichat_vis2'] : '').'
					'.($row['vis'] == 3 ? $txt['2sichat_vis3'] : '').'
				</td>
				 <td align="left" class="windowbg2">
					'.($row['type'] == 0 ? 'PHP' : '').'
					'.($row['type'] == 1 ? 'HTML' : '').'
					'.($row['type'] == 2 ? 'BBC' : '').'
				</td>
               </tr>';
	  }
    echo'
 			<tr>
				<td align="left" class="windowbg2" colspan="4">
					<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=gadget;edit\';">'.$txt['2sichat_gad_add'].'</button>
				</td>
			</tr>
		</table>';
}

function template_twosichatchmod(){
global $txt,$scripturl;

     if(isset($_GET['done'])){echo'<div class="information">'.$txt['2sichatchmod3'].'</div>';}
      echo'  <table class="table_grid" cellspacing="0" width="100%">
			<tr class="catbg">
				<td align="left" class="windowbg2">'.$txt['2sichatchmod'].'</td>
			</tr>';
			
	  echo'<tr class="windowbg"><td>'.$txt['2sichatchmod2'].'</td></tr></table>';
}
function template_twosichatLinks(){

	global $context, $txt, $scripturl;

	echo '
		<table class="table_grid" cellspacing="0" width="100%">
			<tr class="catbg">
				<td align="left" class="windowbg2">'.$txt['2sichat_ord'].'</td>
				<td align="left" class="windowbg2">'.$txt['2sichat_title'].'</td>
				<td align="left" class="windowbg2">'.$txt['2sichat_vis'].'</td>
			</tr>		';
	foreach ($context['gadgets'] as $row)
	  {
   echo'
			<tr class="windowbg">
				<td align="left" class="windowbg2">'.$row['ord'].'</td>
                    <td align="left" class="windowbg2">
					<strong>'.$row['title'].'</strong>
					<div style="float:right;padding-right:40px;">
						<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=link;edit='.$row['id'].'\';">'.$txt['2sichat_edit'].'</button>
						<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=link;delete='.$row['id'].'\';">'.$txt['2sichat_delete'].'</button>
	 				</div>
	 			</td>
                    <td align="left" class="windowbg2">
					'.($row['vis'] == 0 ? $txt['2sichat_vis0'] : '').'
					'.($row['vis'] == 1 ? $txt['2sichat_vis1'] : '').'
					'.($row['vis'] == 2 ? $txt['2sichat_vis2'] : '').'
					'.($row['vis'] == 3 ? $txt['2sichat_vis3'] : '').'
				</td>
               </tr>';
	  }
    echo'
 			<tr>
				<td align="left" class="windowbg2" colspan="3">
					<button type="button" onclick="javascript:window.location.href = \''.$scripturl.'?action=admin;area=sachat;sa=link;edit\';">'.$txt['2sichat_ladd'].'</button>
				</td>
			</tr>
		</table>';
}

function template_twosichatLinkAdd(){

	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=sachat;sa=link;save" method="post" accept-charset="', $context['character_set'], '">
			<table width="100%" border="0" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
				<td align="left" class="windowbg2">
						<strong>'.$txt['2sichat_linkyyy'].'</strong>
					</td>
                         <td align="left" class="windowbg2">
					 <input type="checkbox" name="newwin" class="input_check" ' . (!empty($context['gadget']['newwin']) ? ' checked="checked" ' : '') . ' />
					</td>
				</tr>
					<td align="left" class="windowbg2">
						<strong>'.$txt['2sichat_title'].'</strong>
					</td>
                         <td align="left" class="windowbg2">
						<input type="text" name="title" size="64" maxlength="255" value="'.(isset($context['gadget']['title']) ? $context['gadget']['title']:'').'" /><br />
					</td>
				</tr>
				<tr>
					<td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_laddu'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<input type="text" name="url" size="64" maxlength="255" value="'.(isset($context['gadget']['url']) ? $context['gadget']['url']:'').'" />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_laddiu'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<input type="text" name="image" size="64" maxlength="255" value="'.(isset($context['gadget']['image']) ? $context['gadget']['image']:'').'" /><br />
					</td>
				</tr>
				
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_vis'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<select name="vis">
							<option value="0" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 0 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis0'].'</option>
							<option value="1" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 1 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis1'].'</option>
							<option value="2" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 2 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis2'].'</option>
							<option value="3" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 3 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis3'].'</option>
						</select>
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_ord'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<input type="text" name="ord" size="64" maxlength="255" value="'.(isset($context['gadget']['ord']) ? $context['gadget']['ord']:'').'" /><br />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2" colspan="2">
          				<input type="submit" name="submit" value="submit" />
					</td>
				</tr>
			</table>
			'.(isset($context['gadget']['id']) ? '<input type="hidden" name="mod" value="'.$context['gadget']['id'].'" />':'').'
		</form>
	';
}

function template_twosichatGadAdd(){

	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=sachat;sa=gadget;save" method="post" accept-charset="', $context['character_set'], '">
			<table width="100%" border="0" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="left" class="windowbg2">
						<strong>'.$txt['2sichat_title'].'</strong>
					</td>
                         <td align="left" class="windowbg2">
						<input type="text" name="title" size="64" maxlength="255" value="'.(isset($context['gadget']['title']) ? $context['gadget']['title']:'').'" /><br />
					</td>
				</tr>
				<tr>
					<td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_url'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<textarea rows="5" cols="60" name="url">'.(isset($context['gadget']['url']) ? $context['gadget']['url']:'').'</textarea>
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_width'].'</strong>
                         </td>
                         <td align="left" class="windowbg2">
						<input type="text" name="width" size="64" maxlength="255" value="'.(isset($context['gadget']['width']) ? $context['gadget']['width']:'').'" /><br />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_height'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<input type="text" name="height" size="64" maxlength="255" value="'.(isset($context['gadget']['height']) ? $context['gadget']['height']:'').'" /><br />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_vis'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<select name="vis">
							<option value="0" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 0 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis0'].'</option>
							<option value="1" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 1 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis1'].'</option>
							<option value="2" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 2 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis2'].'</option>
							<option value="3" '.(isset($context['gadget']['vis']) && $context['gadget']['vis'] == 3 ? 'selected="selected"' : '').'>'.$txt['2sichat_vis3'].'</option>
						</select>
					</td></tr><tr>
					 <td align="left" class="windowbg2">
                              <strong>Type</strong>
                         </td>
					<td align="left" class="windowbg2">
						<select name="type">
							<option value="0" '.(isset($context['gadget']['type']) && $context['gadget']['type'] == 0 ? 'selected="selected"' : '').'>PHP</option>
							<option value="1" '.(isset($context['gadget']['type']) && $context['gadget']['type'] == 1 ? 'selected="selected"' : '').'>HTML</option>
							<option value="2" '.(isset($context['gadget']['type']) && $context['gadget']['type'] == 2 ? 'selected="selected"' : '').'>BBC</option>
						</select>
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2">
                              <strong>'.$txt['2sichat_ord'].'</strong>
                         </td>
					<td align="left" class="windowbg2">
						<input type="text" name="ord" size="64" maxlength="255" value="'.(isset($context['gadget']['ord']) ? $context['gadget']['ord']:'').'" /><br />
					</td>
				</tr>
				<tr>
                         <td align="left" class="windowbg2" colspan="2">
          				<input type="submit" name="submit" value="submit" />
					</td>
				</tr>
			</table>
			'.(isset($context['gadget']['id']) ? '<input type="hidden" name="mod" value="'.$context['gadget']['id'].'" />':'').'
		</form>
	';
}

function template_twosichatThemes(){
  
      global $context, $txt, $settings, $modSettings, $scripturl, $dirArray, $indexCount;
      
	  echo'<div class="information">
	          <strong>'.$txt['2sichat_theme14'].'</strong>
           </div>';
	  if(isset($_GET['rdone'])) {
	  echo'<div class="information">
	          <strong>Succesfully removed '.$_GET['rdone'].' theme</strong>
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

	  echo'<form action="', $scripturl, '?action=admin;area=sachat;sa=theme;save" method="post">';
	  
	  echo'<table class="table_grid" cellspacing="0" width="100%">
			<tr class="catbg">
				<td align="left" class="windowbg2">'.$txt['2sichat_theme6'].'</td>
			</tr>';
			
	  echo'<tr class="windowbg"><td>';
	  echo' <strong>'.$txt['2sichat_theme15'].'</strong><br /><div class="smalltext">'.$txt['2sichat_theme7'].'</div><br />
	  <strong>'.$txt['2sichat_theme8'].'</strong> <select name="sachatTheme">';
           // loop through the array of files and echo all
          for($index=0; $index < $indexCount; $index++) {
                  if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php"){ // don't list hidden files
				     echo'  <option value="'.$dirArray[$index].'"', $modSettings['2sichat_theme'] == $dirArray[$index] ? 'selected="selected"' : '', '>'.$dirArray[$index].'</option>';
	              }
          }
	  echo'</select>
	  
		  <input type="submit" value="'.$txt['save'].'" />
	        <input type="hidden" name="sc" value="'.$context['session_id'].'" /><br /><br />';
	  
	  echo'</td>
			</tr>
			 
			</table>';
			
	  echo'</form>';
	  echo'<form action="', $scripturl, '?action=admin;area=sachat;sa=theme;upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" onsubmit="return confirm(\'', $txt['theme_install_new_confirm'], '\');">';
	  echo' <table class="table_grid" cellspacing="0" width="100%">
	             <tr class="windowbg2"><td>
	                <strong>'.$txt['2sichat_theme9'].'</strong><br />
					<div class="smalltext">'.$txt['2sichat_theme10'].'</div>
	                   <br /><strong>'.$txt['2sichat_theme11'].'</strong> 
					   <input type="file" name="theme_gz" id="theme_gz" value="theme_gz" size="40" onchange="this.form.copy.disabled = this.value != \'\'; this.form.theme_dir.disabled = this.value != \'\';" class="input_file" />
				 <input type="submit" value="'.$txt['2sichat_theme13'].'" />
				<br /> <br /><div class="smalltext"><strong>'.$txt['2sichat_theme12'].'</strong> zip</div>
	        <input type="hidden" name="sc" value="'.$context['session_id'].'" /><br /><br />
				</td></tr>
		 </table>';
	 echo'</form>';
	 
	  echo'<form action="', $scripturl, '?action=admin;area=sachat;sa=theme;copy" method="post" onsubmit="return confirm(\'', $txt['theme_install_new_confirm'], '\');">';
	  echo' <table class="table_grid" cellspacing="0" width="100%">
	             <tr class="windowbg"><td>
	                <strong>'.$txt['2sichat_theme16'].'</strong><br />
					<div class="smalltext">'.$txt['2sichat_theme17'].'</div>
	                   <br /><strong>'.$txt['2sichat_theme18'].'</strong> 
					 <input type="text" name="copy" id="copy" value="" size="40" class="input_text" />
				 <input type="submit" value="'.$txt['2sichat_theme19'].'" />
	        <input type="hidden" name="sc" value="'.$context['session_id'].'" /><br /><br />
				</td></tr>
		 </table>';
	 echo'</form>';
	 
	  echo' <table class="table_grid" cellspacing="0" width="100%">
	             <tr class="windowbg2"><td>
	                <strong>'.$txt['2sichat_theme23'].'</strong><br />
					<div class="smalltext">'.$txt['2sichat_theme24'].'</div>
	                   <br /><strong>'.$txt['2sichat_theme25'].'</strong><br />';
			if(!empty($dirArray)){
				for($index=0; $index < $indexCount; $index++) {
                  if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php" && $dirArray[$index] != 'default' ){ // don't list hidden files
				     echo' '.$dirArray[$index].' <a href="', $scripturl, '?action=admin;area=sachat;sa=theme;remove='.$dirArray[$index].'" onclick="return confirm(\''.$txt['2sichat_theme26'].'\');"><img src="', $settings['default_images_url'], '/pm_recipient_delete.gif" alt="'.$txt['2sichat_theme27'].'" /></a><br />';
	              }
                }
			}		 
				echo'</td></tr>
		 </table>';
		  
}
?>