<?php
if (!defined('SMF'))
	die('Hacking attempt...');
	
function twosichatAdmin(){

	global $context, $txt, $scripturl, $modSettings, $settings, $sourcedir;
	
	isAllowedTo('admin_forum');
	loadLanguage('Help');
	loadLanguage('ManageSettings');
	loadLanguage('2sichat');

	require_once($sourcedir . '/ManageServer.php');

	$subActions = array(
     	'config' => 'twosichatConfig',
     	'gadget' => 'twosichatGadget',
		'link' => 'twosichatLinks',
		'theme' => 'twosichatThemes',
		'load' => 'twosichatLoad',
		'chmod' => 'twosichatchmod',
	);

	if(isset($_REQUEST['sa'])) {
		$subActions[$_REQUEST['sa']]();
	} else {
		twosichatConfig();
	}
}

function twosichatchmod(){
  global  $txt, $context;
    
	loadTemplate('sachat');
    $context['sub_template'] = 'twosichatchmod';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichatchmod'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichatchmod1'];

  if(isset($_GET['chmod'])){
	chmodDirectory('sachat/',0);
	redirectexit('action=admin;area=sachat;sa=chmod;done');
  }
  

}
 

function twosichatConfig(){

	global $txt, $scripturl, $context, $settings, $sc, $modSettings, $smcFunc;

	$context['sub_template'] = 'show_settings';
    $context['page_title'] = $txt['2sichat_admin'];

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_config'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_config_des'];
	
	$config_vars = array(
			array('check', '2sichat_disable', 'subtext' => $txt['2sichat_disable_sub']),
			'',
			array('check', '2sichat_dis_bar', 'subtext' => $txt['2sichat_dis_b_sub']),
			'',
			array('check', '2sichat_permissions', 'subtext' => $txt['2sichat_permissions_sub']),
			'',
			array('text', '2sichat_mn_heart', 'size' => 10, 'subtext' => $txt['2sichat_mn_heart_sub']),
			'',
			array('check', '2sichat_cw_h_enable', 'subtext' => $txt['2sichat_cw_h_e_sub']),
			array('text', '2sichat_cw_heart', 'size' => 10, 'subtext' => $txt['2sichat_cw_heart_sub']),
			'',
			array('text', '2sichat_purge', 'size' => 10, 'subtext' => $txt['2sichat_purge_sub']),
			'',
			array('check', '2sichat_purge_all', 'subtext' => $txt['2sichat_purge_a_sub']),
			'',
			array('check', '2sichat_live_online'),
			array('check', '2sichat_list_type', 'subtext' => $txt['2sichat_list_t_sub']),
			'',
			array('check', '2sichat_dis_list', 'subtext' => $txt['2sichat_dis_l_sub']),
			'',
			array('check', '2sichat_simple_bbc'),
			'',
			array('check', '2sichat_gad_trans'),
			array('text', '2sichat_gad_lang', 'size' => 10, 'subtext' => $txt['2sichat_gad_lang_sub']),
			'',
			//array('check', '2sichat_ico_home'),
			//array('check', '2sichat_ico_pm'),
			array('check', '2sichat_ico_myspace'),
			array('check', '2sichat_ico_twit'),
			array('check', '2sichat_ico_fb'),
			'',
			/*array('check', '2sichat_load_chk'),
			array('text', '2sichat_max_load', 'subtext' => $txt['2sichat_max_l_sub']),
			array('check', '2sichat_load_dis_chat'),
			array('check', '2sichat_load_dis_list'),
			array('check', '2sichat_load_dis_bar'),
			'',*/
			array('text', 'size' => 50, '2sichat_board_index'),
			//post,boardindex,board,topic,help,search,profile,mlist,pm,moderate,admin
	);
	
	if (!empty($return_config))
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=sachat;save;sa=config';
	$context['settings_title'] = $txt['2sichat_admin'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div class="centertext">' . $txt['modification_no_misc_settings'] . '</div>';
		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		checkSession();
		if (isset($_POST['2sichat_purge_all']) && $_POST['2sichat_purge_all'] == 1) {
			$_POST['2sichat_purge_all'] = 0;
			$smcFunc['db_query']('', 'DELETE FROM {db_prefix}2sichat',array());
			$smcFunc['db_query']('', 'ALTER TABLE {db_prefix}2sichat AUTO_INCREMENT = 0',array());
		}
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=sachat;sa=config');
	}
	prepareDBSettingContext($config_vars);
	
}

function twosichatLoad(){

	global $txt, $scripturl, $context, $settings, $sc, $modSettings, $smcFunc;

	$context['sub_template'] = 'show_settings';
    $context['page_title'] = $txt['2sichatloadbal'];

	$disabled = true;
	
	if (strpos(strtolower(PHP_OS), 'win') === 0)
		$context['chat_settings_message'] = $txt['loadavg_disabled_windows'];
	else
	{
	$modSettings['chat_load_average'] = @file_get_contents('/proc/loadavg');
		if (!empty($modSettings['chat_load_average']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $modSettings['chat_load_average'], $matches) !== 0)
			$modSettings['chat_load_average'] = (float) $matches[1];
		elseif (($modSettings['chat_load_average'] = @`uptime`) !== null && preg_match('~load averages?: (\d+\.\d+), (\d+\.\d+), (\d+\.\d+)~i', $modSettings['chat_load_average'], $matches) !== 0)
			$modSettings['chat_load_average'] = (float) $matches[1];
		else
			unset($modSettings['chat_load_average']);

		if (!empty($modSettings['chat_load_average']))
		{
			$context['chat_settings_message'] = sprintf($txt['2sichat_loadavg_warning'], $modSettings['chat_load_average']);
			$disabled = false;
			
		}
	}
		
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichatloadbal'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $context['chat_settings_message'];

	$default_text = array(
		'2sichat_max_load' => '',
	);
	$default_ints = array(
		'2sichat_load_chk' => '0',
	    '2sichat_load_dis_chat' => '0',
		'2sichat_load_dis_list' => '0',
		'2sichat_load_dis_bar' => '0',
	);

	foreach ($default_ints as $name => $value)
	{
		// Use the default value if the setting isn't set yet.
		$value = !isset($modSettings[$name]) ? $value : $modSettings[$name];
		$config_vars[] = array('check', $name, 'value' => $value, 'disabled' => $disabled);
	}
	foreach ($default_text as $name => $value)
	{
		// Use the default value if the setting isn't set yet.
		$value = !isset($modSettings[$name]) ? $value : $modSettings[$name];
		$config_vars[] = array('text', $name, 'value' => $value, 'disabled' => $disabled);
	}
	
	if (!empty($return_config))
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=sachat;save;sa=load';
	$context['settings_title'] = $txt['2sichatloadbal'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div class="centertext">' . $txt['modification_no_misc_settings'] . '</div>';
		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=sachat;sa=load');
	}
	prepareDBSettingContext($config_vars);
	
}

function twosichatThemes(){
	
	global $txt, $context, $boarddir, $modSettings, $sourcedir, $dirArray, $indexCount, $smcFunc;
	
	loadTemplate('sachat');
	loadLanguage('Themes');
	loadLanguage('Settings');
	$context['sub_template'] = 'twosichatThemes';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_theme1'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_theme2'];
	 
	// open this directory 
    $myDirectory = opendir($boarddir.'/sachat/themes');

    // get each entry
    while($entryName = readdir($myDirectory)) {
	      $dirArray[] = $entryName;
    }

    // close directory
    closedir($myDirectory);
	  
	//count elements in array
    $indexCount = count($dirArray);

    // sort 'em
    sort($dirArray);
	
	//save theme
	if(isset($_GET['save'])) {
	  checkSession();
      updateSettings (array('2sichat_theme' => $_POST['sachatTheme'])); 
	  redirectexit('action=admin;area=sachat;sa=theme;done');
	}
	//upload theme
	if(isset($_GET['upload'])) {

		// Hopefully the themes directory is writable, or we might have a problem.
		if (!is_writable($boarddir . '/sachat/themes'))
			fatal_lang_error('theme_install_write_error', 'critical');
		
	if(SAchat_isAllowedExtension($_FILES['theme_gz']['name'])) {

		require_once($sourcedir . '/Subs-Package.php');

		// Set the default settings...
		$theme_name = basename($_FILES['theme_gz']['name']);
		$theme_name1 = preg_replace(array('/\.zip/','/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('','_', '.', ''), $theme_name);
		$theme_dir = $boarddir . '/sachat/themes/' . $theme_name1;
       
	    mkdir($theme_dir, 0777);
	   
		if (isset($_FILES['theme_gz']) && is_uploaded_file($_FILES['theme_gz']['tmp_name']) && (@ini_get('open_basedir') != '' || file_exists($_FILES['theme_gz']['tmp_name']))){
			
			$extracted = read_tgz_file($_FILES['theme_gz']['tmp_name'], $theme_dir, false, true);
		  
		    redirectexit('action=admin;area=sachat;sa=theme;udone='.$theme_name1.'');
		}
		else{
			redirectexit('action=admin;area=sachat;sa=theme');
		}
	}
	else{
	 //Invalide file type
    fatal_lang_error('2sichat_theme3',false);
	}
	}
	//copy theme
	if(isset($_GET['copy'])) {
	// Hopefully the themes directory is writable, or we might have a problem.
		if (!is_writable($boarddir . '/sachat/themes'))
			fatal_lang_error('theme_install_write_error', 'critical');
		if(empty($_POST['copy']))
		     fatal_lang_error('2sichat_theme22', false);
         
		 require_once($sourcedir . '/Subs-Package.php');
		 $theme_dir = $boarddir . '/sachat/themes/'.$_POST['copy'].'';

		umask(0);
		mkdir($theme_dir, 0777);

		@set_time_limit(600);
		if (function_exists('apache_reset_timeout'))
			@apache_reset_timeout();

		// Copy over the default non-theme files.
		$to_copy = array('/index.php', '/template.php', '/style.css');
		foreach ($to_copy as $file)
		{
			copy($boarddir . '/sachat/themes/default' . $file, $theme_dir . $file);
			@chmod($theme_dir . $file, 0777);
		}

		// And now the entire images directory!
		copytree($boarddir . '/sachat/themes/default/images', $theme_dir . '/images');
		// And now the entire languages directory!
		copytree($boarddir . '/sachat/themes/default/languages', $theme_dir . '/languages');
		package_flush_cache();
		redirectexit('action=admin;area=sachat;sa=theme;udone');
	}
	//remove theme
	if(isset($_GET['remove'])) {
	  if($_GET['remove'] != $modSettings['2sichat_theme']){
	    if($_GET['remove'] != '') {
	        SAChat_deleteAll($boarddir . '/sachat/themes/'.$_GET['remove']);
	        redirectexit('action=admin;area=sachat;sa=theme;rdone='.$_GET['remove'].'');
		}
		else{
		    fatal_lang_error('2sichat_theme20', false);
		}
	  }
	  else{
		 fatal_lang_error('2sichat_theme21', false);
	  }
	}

}

function twosichatGadget(){
	
	global $txt, $context, $smcFunc;

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_gadgets'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_gadgets_des'];

	$context['page_title'] = $txt['2sichat_admin'];
	loadTemplate('sachat');

	$context['html_headers'].= '
		<script type="text/javascript">
			function ShowGadgetLink(id){
				if (document.getElementById(id).style.display == \'none\') {
					document.getElementById(id).style.display = \'block\';
				} else {
					document.getElementById(id).style.display = \'none\';
				}
				document.getElementById(id).focus();
				document.getElementById(id).select();
			}
		</script>';

	if (isset($_REQUEST['delete'])) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat_gadgets
			WHERE id = {int:did}',
			array(
				'did' => (int) $_REQUEST['delete'],
			)
		);
		redirectexit('action=admin;area=sachat;sa=gadget');
	}else if (isset($_REQUEST['edit'])) {
		$context['sub_template'] = 'twosichatGadAdd';
		$context['gadget'] = array();
		if ($_REQUEST['edit'] != '') {
			$request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_gadgets
				WHERE id = {int:did}',
				array(
					'did' => (int) $_REQUEST['edit'],
				)
			);
			$context['gadget'] = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
		}
	}elseif (isset($_REQUEST['save'])) {
		//errors!!!!!
		/*$fields_string = array('title', 'url', 'width', 'height');
		$fields_ints = array('width', 'height','ord');
		foreach ($fields_string as $field)
		{
			if (empty($_POST[$field]))
				fatal_error($field . $txt['2silempty'], false);
			else
				$_POST[$field] = $smcFunc['htmlspecialchars']($_POST[$field], ENT_QUOTES);
		}
		foreach ($fields_ints as $field)
		{
			if (is_numeric($_POST[$field]))
				$_POST[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
			if(empty($_POST[$field['ord']]))
				$_POST[$field['ord']] = 0;
			else
				fatal_error($field . $txt['2silempty1'], false);
		}*/
		if (isset($_REQUEST['mod'])){
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_gadgets
				SET title = {string:title}, url = {string:url}, width = {string:width}, height = {string:height}, ord = {string:ord}, vis = {string:vis}, type = {string:type}
				WHERE id = {int:did}',
			array('did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'width' => $_POST['width'], 'height' => $_POST['height'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'], 'type' => $_POST['type'])
			);
		} else {
			$smcFunc['db_insert']('',
				'{db_prefix}2sichat_gadgets',
				array('title' => 'string', 'url' => 'string', 'width' => 'string', 'height' => 'string', 'ord' => 'string', 'vis' => 'string', 'type' => 'string'),
				array($_POST['title'], $_POST['url'], $_POST['width'], $_POST['height'], $_POST['ord'], $_POST['vis'], $_POST['type']),
				array()
			);
		}
		redirectexit('action=admin;area=sachat;sa=gadget');
	}else{
		twosigadDis();
	}

}

function twosichatlinks(){
	
	global $txt, $context, $smcFunc;

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_linksd'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_linksd1'];

	$context['page_title'] = $txt['2sichat_admin'];
	loadTemplate('sachat');

	if (isset($_REQUEST['delete'])) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat_barlinks
			WHERE id = {int:did}',
			array(
				'did' => (int) $_REQUEST['delete'],
			)
		);
		redirectexit('action=admin;area=sachat;sa=link');
	}else if (isset($_REQUEST['edit'])) {
		$context['sub_template'] = 'twosichatLinkAdd';
		$context['gadget'] = array();
		if ($_REQUEST['edit'] != '') {
			$request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_barlinks
				WHERE id = {int:did}',
				array(
					'did' => (int) $_REQUEST['edit'],
				)
			);
			$context['gadget'] = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
		}
	}elseif (isset($_REQUEST['save'])) {
		if (isset($_REQUEST['mod'])){
		$_POST['newwin'] = isset($_POST['newwin'])? 1 : 0;
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_barlinks
				SET newwin = {string:newwin}, title = {string:title}, url = {string:url}, image = {string:image}, ord = {string:ord}, vis = {string:vis}
				WHERE id = {int:did}',
			array('newwin' => $_POST['newwin'], 'did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'image' => $_POST['image'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'])
			);
		} else {
		$_POST['newwin'] = isset($_POST['newwin'])? 1 : 0;
			$smcFunc['db_insert']('',
				'{db_prefix}2sichat_barlinks',
				array('newwin' => 'string', 'title' => 'string', 'url' => 'string', 'image' => 'string', 'ord' => 'string', 'vis' => 'string'),
				array($_POST['newwin'], $_POST['title'], $_POST['url'], $_POST['image'],  $_POST['ord'], $_POST['vis']),
				array()
			);
		}
		redirectexit('action=admin;area=sachat;sa=link');
	}else{
		twosigadLink();
	}

}

function twosigadLink(){
	
	global $txt, $context, $smcFunc;
	
	$context['sub_template'] = 'twosichatLinks';
	$context['gadgets'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_barlinks
		ORDER BY ord',
		array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)){
		$context['gadgets'][] = array(
			'id' => $row['id'],
			'title' => $row['title'],
			'url' => $row['url'],
			'image' => $row['image'],
			'ord' => $row['ord'],
			'vis' => $row['vis'],
			'newwin' => $row['newwin']
		);
	}
	$smcFunc['db_free_result']($request);
}

function twosigadDis(){
	
	global $txt, $context, $smcFunc;
	
	$context['sub_template'] = 'twosichatGadgets';
	$context['gadgets'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_gadgets
		ORDER BY ord',
		array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)){
		$context['gadgets'][] = array(
			'id' => $row['id'],
			'title' => $row['title'],
			'ord' => $row['ord'],
			'vis' => $row['vis'],
			'type' => $row['type']
		);
	}
	$smcFunc['db_free_result']($request);
}

function chmodDirectory( $path = '.', $level = 0 ){  
    
	$ignore = array( 'cgi-bin', '.', '..' ); 
    $dh = @opendir( $path ); 
    while( false !== ( $file = readdir( $dh ) ) ){ 
      if( !in_array( $file, $ignore ) ){
        if( is_dir($path.'/'.$file) ){
          chmod($path.'/'.$file,0755);
          chmodDirectory($path.'/'.$file, ($level+1));
        } else {
          chmod($path.'/'.$file,0755); 
        }
      }
    }
    closedir($dh); 
}

function SAChat_deleteAll($dir) { 
    
	if (!file_exists($dir)) return true; 
    if (!is_dir($dir) || is_link($dir)) return unlink($dir); 
        foreach (scandir($dir) as $item) { 
            if ($item == '.' || $item == '..') continue; 
            if (!SAChat_deleteAll($dir . "/" . $item)) { 
                chmod($dir . "/" . $item, 0777); 
                if (!SAChat_deleteAll($dir . "/" . $item)) return false; 
            }; 
        } 
        return rmdir($dir); 
} 

function SAchat_isAllowedExtension($fileName) {
   global $allowedExtensions;
  
   $allowedExtensions = array('zip');
   return in_array(end(explode(".", $fileName)), $allowedExtensions);
}

?>