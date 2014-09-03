<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
if (!defined('SMF'))
    die('Hacking attempt...');

/**
 * @param string $chatvar
 */
function SAChat_InsertOptions($chatmem, $chatvar, $chatval) {
    global $settings, $smcFunc;

    $smcFunc['db_insert']('replace', '{db_prefix}themes', 
		array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',), 
		array($chatmem, $settings['theme_id'], $chatvar, $chatval,), 
		array('id_member', 'id_theme')
    );
}

function twosichatAdmin() {

    global $context, $txt, $scripturl, $modSettings, $settings, $sourcedir;

    isAllowedTo('admin_forum');
    loadLanguage('Help');
    loadLanguage('ManageSettings');
    loadLanguage('2sichat');

    require_once($sourcedir . '/ManageServer.php');

    $subActions = array(
        'config' => 'twosichatConfig',
        'gadget' => 'twosichatGadget',
		'chat' => 'twosichatChat',
        'link' => 'twosichatLinks',
        'theme' => 'twosichatThemes',
        'load' => 'twosichatLoad',
        'maintain' => 'twosichatchmod',
        'errorlogs' => 'twosichaterror',
    );

    if (isset($_REQUEST['sa'])) {
        $subActions[$_REQUEST['sa']]();
    } else {
        twosichatConfig();
    }
}

function twosichaterror() {
    global $txt, $db_prefix, $scripturl, $sourcedir, $smcFunc, $context;

    loadTemplate('sachat');
    $context['sub_template'] = 'twosichaterror';
    $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['error_2si'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['error_2si'];

    $list_options = array(
        'id' => 'chat_error',
        'title' => $txt['error_2si'],
        'items_per_page' => 20,
        'base_href' => $scripturl . '?action=admin;area=sachat;sa=errorlogs',
        'get_items' => array(
            'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $user_info, $memid, $smcFunc;
		  
		$request = $smcFunc[\'db_query\'](\'\', \'
		    SELECT id, type, file, line,info
            FROM {db_prefix}2sichat_error
            LIMIT {int:start}, {int:per_page}\',
            array( 
			    \'sort\' => $sort,
			    \'start\' => $start,
			    \'per_page\' => $items_per_page,
			));
		$error = array();
		while ($row = $smcFunc[\'db_fetch_assoc\']($request))
			$error[] = $row;   
		$smcFunc[\'db_free_result\']($request);

		return $error;
			'),
        ),
        'get_count' => array(
            'function' => create_function('', '
				global $memid, $smcFunc;
             
				
				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}2sichat_error\',
			        array(
					)
				);
				
				list ($total_error) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_error;
			'),
        ),
        'no_items_label' => 'No Errors',
        'columns' => array(
            'type' => array(
                'header' => array(
                    'value' => $txt['error_type'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
					global $settings;
					
						return \'\'.$row[\'type\'].\'<br /><br />\';
					
					'),
                    'style' => 'width: 1%; text-align: left;',
                ),
                'sort' => array(
                    'default' => 'id DESC',
                    'reverse' => 'id',
                ),
            ),
            'info' => array(
                'header' => array(
                    'value' => $txt['error_msg'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
					global $scripturl;
						return \'\'.$row[\'info\'].\'<br /><br />\';
					'),
                    'style' => 'width: 20%; text-align: left;',
                ),
                'sort' => array(
                    'default' => 'id DESC',
                    'reverse' => 'id',
                ),
            ),
            'line' => array(
                'header' => array(
                    'value' => $txt['error_file'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
					global $scripturl;
						return \'File: \'.$row[\'file\'].\' <br />Line: \'.$row[\'line\'].\'<br /><br />\';
					'),
                    'style' => 'width: 20%; text-align: left;',
                ),
                'sort' => array(
                    'default' => 'id DESC',
                    'reverse' => 'id',
                ),
            ),
            'action' => array(
                'header' => array(
                    'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
                ),
                'data' => array(
                    'function' => create_function('$row', '
                         global $sc,$scripturl;
						return \'<input type="checkbox" class="input_check" name="del[]" value="\' . $row[\'id\'] . \'" />\';
					'),
                    'style' => 'width: 2%; text-align: center;',
                ),
            ),
        ),
        'form' => array(
            'href' => $scripturl . '?action=admin;area=sachat;sa=errorlogs',
            'include_sort' => true,
            'include_start' => true,
            'hidden_fields' => array(
                $context['session_var'] => $context['session_id'],
            ),
        ),
        'additional_rows' => array(
            array(
                'position' => 'below_table_data',
                'value' => '
						
						<input type="submit" name="del_sel" value="' . $txt['remove_selection'] . '" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="del_all" value="' . $txt['remove_all'] . '" class="button_submit" onclick="return confirmSubmit();" />'
            ),
        ),
    );
    require_once($sourcedir . '/Subs-List.php');

    createList($list_options);
    if (isset($_POST['del_all'])) {
        $smcFunc['db_query']('', '
	       DELETE FROM {db_prefix}2sichat_error', array());
		   redirectexit('action=admin;area=sachat;sa=errorlogs');
    }
    if (!empty($_POST['del_sel']) && isset($_POST['del'])) {
        $smcFunc['db_query']('', '
	       DELETE FROM {db_prefix}2sichat_error
		   WHERE id IN ({array_string:delete_actions})', array('delete_actions' => array_unique($_POST['del']),));
		   redirectexit('action=admin;area=sachat;sa=errorlogs');
    }
}

function twosichatchmod() {
    global $txt, $db_prefix, $smcFunc, $context;

    loadTemplate('sachat');
    $context['sub_template'] = 'twosichatchmod';
    $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichatmaintain'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichatmaintaininfo'];

    if (isset($_GET['opti'])) {
        db_extend();
        $real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;
        $temp_tables = $smcFunc['db_list_tables'](false, $real_prefix . '%');
        $sichatTables = array($db_prefix . '2sichat', $db_prefix . '2sichat_gadgets', $db_prefix . '2sichat_barlinks');
        $tables = array();

        foreach ($temp_tables as $table)
            $tables[] = array('table_name' => $table);

        $context['optimized_tables'] = array();

        foreach ($tables as $table) {
            if (in_array($table['table_name'], $sichatTables)) {
                $data_freed = $smcFunc['db_optimize_table']($table['table_name']);
            }
        }
        redirectexit('action=admin;area=sachat;sa=maintain;done');
    }
    if (isset($_GET['chmod'])) {
        chmodDirectory('sachat/', 0);
        redirectexit('action=admin;area=sachat;sa=maintain;done');
    }
    if (isset($_GET['cache'])) {
        twosicleanCache();
        redirectexit('action=admin;area=sachat;sa=maintain;done');
    }
    if (isset($_GET['purge'])) {
        $smcFunc['db_query']('', 'DELETE FROM {db_prefix}2sichat', array());
        $smcFunc['db_query']('', 'ALTER TABLE {db_prefix}2sichat AUTO_INCREMENT = 0', array());
        redirectexit('action=admin;area=sachat;sa=maintain;done');
    }
}

function twosichatConfig() {

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
        array('check', '2sichat_cache', 'size' => 10, 'subtext' => $txt['2sichat_cache_sub']),
		'',
        array('text', '2sichat_cookie_name'),
		'',
        array('check', '2sichat_e_logs'),
        '',
        array('check', '2sichat_gad_trans'),
        array('text', '2sichat_gad_lang', 'size' => 10, 'subtext' => $txt['2sichat_gad_lang_sub']),
        '',
        array('check', '2sichat_ico_myspace'),
        array('check', '2sichat_ico_gplus'),
        array('check', '2sichat_ico_twit'),
        array('check', '2sichat_ico_fb'),
        array('check', '2sichat_ico_adthis'),
        '',
        array('text', 'size' => 50, '2sichat_board_index'),
        '',
        array('check', '2sichat_censor'),
        array('large_text', '2sichat_censor_words', '8', '2sichat_censor_words'),
        '',
        array('text', 'size' => 50, '2sichat_disabled_themes'),
    );

    if (!empty($return_config))
        return $config_vars;

    $context['post_url'] = $scripturl . '?action=admin;area=sachat;save;sa=config';
    $context['settings_title'] = $txt['2sichat_admin'];

    if (empty($config_vars)) {
        $context['settings_save_dont_show'] = true;
        $context['settings_message'] = '<div class="centertext">' . $txt['modification_no_misc_settings'] . '</div>';
        return prepareDBSettingContext($config_vars);
    }

    if (isset($_GET['save'])) {
        checkSession();
        twosicleanCache();
        if (isset($_POST['2sichat_purge_all']) && $_POST['2sichat_purge_all'] == 1) {
            $_POST['2sichat_purge_all'] = 0;
            $smcFunc['db_query']('', 'DELETE FROM {db_prefix}2sichat', array());
            $smcFunc['db_query']('', 'ALTER TABLE {db_prefix}2sichat AUTO_INCREMENT = 0', array());
        }
        $save_vars = $config_vars;
        saveDBSettings($save_vars);
        redirectexit('action=admin;area=sachat;sa=config');
    }
    prepareDBSettingContext($config_vars);
}

function twosichatChat() {

    global $txt, $scripturl, $context, $settings, $sc, $modSettings, $smcFunc;

    $context['sub_template'] = 'show_settings';
    $context['page_title'] = $txt['twosichatChat'];
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['twosichatChat'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['twosichatChat'];
	
	$config_vars = array(
	    array('text', '2sichat_mn_heart', 'size' => 10, 'subtext' => $txt['2sichat_mn_heart_sub']),
		array('text', '2sichat_mn_heartmin', 'size' => 10, 'subtext' => $txt['2sichat_mn_heart_submin']),
		array('text', '2sichat_mn_heart_timeout', 'size' => 10, 'subtext' => $txt['2sichat_mn_heart_timeout_sub']),
        '',
        array('text', '2sichat_purge', 'size' => 10, 'subtext' => $txt['2sichat_purge_sub']),
        '',
		array('check', '2sichat_dis_list', 'subtext' => $txt['2sichat_dis_l_sub']),
        array('check', '2sichat_simple_bbc'),
		array('check', '2sichat_live_online'),
		array('check', '2sichat_live_notfy'),
		array('check', '2sichat_live_type', 'subtext' => $txt['2sichat_live_type_sub']),
		'',
		array('check', '2sichat_e_last3min'),
		array('text', '2sichat_e_last3minv', 'size' => 10, 'subtext' => $txt['2sichat_e_last3minv_sub']),
    );
	
	if (!empty($return_config))
        return $config_vars;

    $context['post_url'] = $scripturl . '?action=admin;area=sachat;save;sa=chat';
    $context['settings_title'] = $txt['twosichatChat'];

    if (empty($config_vars)) {
        $context['settings_save_dont_show'] = true;
        $context['settings_message'] = '<div class="centertext">' . $txt['modification_no_misc_settings'] . '</div>';
        return prepareDBSettingContext($config_vars);
    }

    if (isset($_GET['save'])) {
        checkSession();
        twosicleanCache();
        $save_vars = $config_vars;
        saveDBSettings($save_vars);
        redirectexit('action=admin;area=sachat;sa=chat');
    }
	prepareDBSettingContext($config_vars);
}

function twosichatLoad() {

    global $txt, $scripturl, $context, $settings, $sc, $modSettings, $smcFunc;

    $context['sub_template'] = 'show_settings';
    $context['page_title'] = $txt['2sichatloadbal'];

    $disabled = false;

   /* ob_start();
    passthru('typeperf -sc 1 "\processor(_total)\% processor time"', $statusa);
    $contenta = ob_get_contents();
    ob_end_clean();
    if ($statusa === 0) {
        if (preg_match("/\,\"([0-9]+\.[0-9]+)\"/", $contenta, $loada)) {
            $cpua = $loada[1];
        }
        $context['chat_settings_message'] = sprintf($txt['2sichat_loadavg_warning'], $cpua);
    }*/
	if(function_exists ("sys_getloadavg")){
		$load = sys_getloadavg();
		$context['chat_settings_message'] = sprintf($txt['2sichat_loadavg_warning'], $load[0]);
	}else{
		$context['chat_settings_message'] = '<div class="error">Load checking is not avalible on windows</div>';
	}

    $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichatloadbal'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $context['chat_settings_message'];


    $config_vars = array(
        array('check', '2sichat_load_chk'),
        array('check', '2sichat_load_dis_chat'),
        array('check', '2sichat_load_dis_list'),
        array('check', '2sichat_load_dis_bar'),
        array('text', '2sichat_max_load'),
    );

    if (!empty($return_config))
        return $config_vars;

    $context['post_url'] = $scripturl . '?action=admin;area=sachat;save;sa=load';
    $context['settings_title'] = $txt['2sichatloadbal'];

    if (empty($config_vars)) {
        $context['settings_save_dont_show'] = true;
        $context['settings_message'] = '<div class="centertext">' . $txt['modification_no_misc_settings'] . '</div>';
        return prepareDBSettingContext($config_vars);
    }

    if (isset($_GET['save'])) {
        checkSession();
        twosicleanCache();
        $save_vars = $config_vars;
        saveDBSettings($save_vars);
        redirectexit('action=admin;area=sachat;sa=load');
    }
    prepareDBSettingContext($config_vars);
}

function twosichatThemes() {

    global $txt, $context, $boarddir, $modSettings, $sourcedir, $dirArray, $indexCount, $smcFunc;

    //TODO: Rewrite this it fugly :P
    loadTemplate('sachat');
    loadLanguage('Themes');
    loadLanguage('Settings');
    $context['sub_template'] = 'twosichatThemes';
    $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_theme1'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_theme2'] . '<br /><span class="error">' . $txt['2sichat_theme14'] . '</span>';

    SAChat_LoadTemes();

    //save theme
    if (isset($_GET['save'])) {
        checkSession();
        updateSettings(array('2sichat_theme' => $_POST['sachatTheme']));

        if (isset($_POST['sachatThemer'])) {
            $request = $smcFunc['db_query']('', '
		        SELECT id_member
	            FROM {db_prefix}members', array());
				
            
            while ($row = $smcFunc['db_fetch_assoc']($request)) {
				SAChat_InsertOptions($row['id_member'], 'cbar_theme', $_POST['sachatThemer']);
			
            }$smcFunc['db_free_result']($request);
			
        }

        redirectexit('action=admin;area=sachat;sa=theme;done');
    }

    //upload theme
    if (isset($_GET['upload'])) {

        // Hopefully the themes directory is writable, or we might have a problem.
        if (!is_writable($boarddir . '/sachat/themes'))
            fatal_lang_error('theme_install_write_error', 'critical');

        if (SAchat_isAllowedExtension($_FILES['theme_gz']['name'])) {

            require_once($sourcedir . '/Subs-Package.php');

            // Set the default settings...
            $theme_name = basename($_FILES['theme_gz']['name']);
            $theme_name1 = preg_replace(array('/\.zip/', '/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('', '_', '.', ''), $theme_name);
            $theme_dir = $boarddir . '/sachat/themes/' . $theme_name1;

            mkdir($theme_dir, 0777);

            if (isset($_FILES['theme_gz']) && is_uploaded_file($_FILES['theme_gz']['tmp_name']) && (@ini_get('open_basedir') != '' || file_exists($_FILES['theme_gz']['tmp_name']))) {

                $extracted = read_tgz_file($_FILES['theme_gz']['tmp_name'], $theme_dir, false, true);

                redirectexit('action=admin;area=sachat;sa=theme;udone=' . $theme_name1 . '');
            } else {
                redirectexit('action=admin;area=sachat;sa=theme');
            }
        } else {
            //Invalide file type
            fatal_lang_error('2sichat_theme3', false);
        }
    }

    //copy theme
    if (isset($_GET['copy'])) {
        // Hopefully the themes directory is writable, or we might have a problem.
        if (!is_writable($boarddir . '/sachat/themes'))
            fatal_lang_error('theme_install_write_error', 'critical');
        if (empty($_POST['copy']))
            fatal_lang_error('2sichat_theme22', false);

        require_once($sourcedir . '/Subs-Package.php');
        $theme_dir = $boarddir . '/sachat/themes/' . $_POST['copy'] . '';

        umask(0);
        mkdir($theme_dir, 0777);

        @set_time_limit(600);
        if (function_exists('apache_reset_timeout'))
            @apache_reset_timeout();

        // Copy over the default non-theme files.
        $to_copy = array('/head.js.php', '/body.js.php', '/index.php', '/template.php', '/style.css');
        foreach ($to_copy as $file) {
            if (file_exists($boarddir . '/sachat/themes/default' . $file)) {
                copy($boarddir . '/sachat/themes/default' . $file, $theme_dir . $file);
                @chmod($theme_dir . $file, 0777);
            }
        }

        // And now the entire images directory!
        copytree($boarddir . '/sachat/themes/default/images', $theme_dir . '/images');

        // And now the entire languages directory!
        copytree($boarddir . '/sachat/themes/default/languages', $theme_dir . '/languages');

        // And now the entire sound directory!
        copytree($boarddir . '/sachat/themes/default/sounds', $theme_dir . '/sounds');

        // And now the entire sound directory!
        copytree($boarddir . '/sachat/themes/default/js', $theme_dir . '/js');

        package_flush_cache();
        redirectexit('action=admin;area=sachat;sa=theme;udone');
    }

    //remove theme
    if (isset($_GET['remove'])) {
        if ($_GET['remove'] != $modSettings['2sichat_theme']) {
            if ($_GET['remove'] != '') {
                SAChat_deleteAll($boarddir . '/sachat/themes/' . $_GET['remove']);
                redirectexit('action=admin;area=sachat;sa=theme;rdone=' . $_GET['remove'] . '');
            } else {
                fatal_lang_error('2sichat_theme20', false);
            }
        } else {
            fatal_lang_error('2sichat_theme21', false);
        }
    }
}

function twosichatGadget() {

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
			WHERE id = {int:did}', array(
            'did' => (int) $_REQUEST['delete'],
                )
        );
        twosicleanCache();
        redirectexit('action=admin;area=sachat;sa=gadget');
    } else if (isset($_REQUEST['edit'])) {
        $context['sub_template'] = 'twosichatGadAdd';
        $context['gadget'] = array();
        if ($_REQUEST['edit'] != '') {
            $request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_gadgets
				WHERE id = {int:did}', array(
                'did' => (int) $_REQUEST['edit'],
                    )
            );
            $context['gadget'] = $smcFunc['db_fetch_assoc']($request);
            $smcFunc['db_free_result']($request);
        }
    } elseif (isset($_REQUEST['save'])) {
        if (isset($_REQUEST['mod'])) {
            $smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_gadgets
				SET title = {string:title}, url = {string:url}, width = {string:width}, height = {string:height}, ord = {string:ord}, vis = {string:vis}, type = {string:type}
				WHERE id = {int:did}', array('did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'width' => $_POST['width'], 'height' => $_POST['height'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'], 'type' => $_POST['type'])
            );
        } else {
            $smcFunc['db_insert']('', '{db_prefix}2sichat_gadgets', array('title' => 'string', 'url' => 'string', 'width' => 'string', 'height' => 'string', 'ord' => 'string', 'vis' => 'string', 'type' => 'string'), array($_POST['title'], $_POST['url'], $_POST['width'], $_POST['height'], $_POST['ord'], $_POST['vis'], $_POST['type']), array()
            );
        }
        twosicleanCache();
        redirectexit('action=admin;area=sachat;sa=gadget');
    } else {
        twosigadDis();
    }
}

function twosichatlinks() {

    global $txt, $context, $smcFunc;

    $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['2sichat_linksd'];
    $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['2sichat_linksd1'];

    $context['page_title'] = $txt['2sichat_admin'];
    loadTemplate('sachat');

    if (isset($_REQUEST['delete'])) {
        $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}2sichat_barlinks
			WHERE id = {int:did}', array(
            'did' => (int) $_REQUEST['delete'],
                )
        );
        twosicleanCache();
        redirectexit('action=admin;area=sachat;sa=link');
    } else if (isset($_REQUEST['edit'])) {
        $context['sub_template'] = 'twosichatLinkAdd';
        $context['gadget'] = array();
        if ($_REQUEST['edit'] != '') {
            $request = $smcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}2sichat_barlinks
				WHERE id = {int:did}', array(
                'did' => (int) $_REQUEST['edit'],
                    )
            );
            $context['gadget'] = $smcFunc['db_fetch_assoc']($request);
            $smcFunc['db_free_result']($request);
        }
    } elseif (isset($_REQUEST['save'])) {
        if (isset($_REQUEST['mod'])) {
            $_POST['newwin'] = isset($_POST['newwin']) ? 1 : 0;
            $smcFunc['db_query']('', '
				UPDATE {db_prefix}2sichat_barlinks
				SET newwin = {string:newwin}, title = {string:title}, url = {string:url}, image = {string:image}, ord = {string:ord}, vis = {string:vis}
				WHERE id = {int:did}', array('newwin' => $_POST['newwin'], 'did' => $_POST['mod'], 'title' => $_POST['title'], 'url' => $_POST['url'], 'image' => $_POST['image'], 'ord' => $_POST['ord'], 'vis' => $_POST['vis'])
            );
        } else {
            $_POST['newwin'] = isset($_POST['newwin']) ? 1 : 0;
            $smcFunc['db_insert']('', '{db_prefix}2sichat_barlinks', array('newwin' => 'string', 'title' => 'string', 'url' => 'string', 'image' => 'string', 'ord' => 'string', 'vis' => 'string'), array($_POST['newwin'], $_POST['title'], $_POST['url'], $_POST['image'], $_POST['ord'], $_POST['vis']), array()
            );
        }
        twosicleanCache();
        redirectexit('action=admin;area=sachat;sa=link');
    } else {
        twosigadLink();
    }
}

function twosicleanCache() {
    global $boarddir;

    $cachedir = $boarddir . '/sachat/cache';
    if (!is_dir($cachedir))
        return;

    $files = scandir($cachedir);

    foreach ($files as $key => $value) {
        @unlink($cachedir . '/' . $value);
    }
}

function twosigadLink() {

    global $txt, $context, $smcFunc;

    $context['sub_template'] = 'twosichatLinks';
    $context['gadgets'] = array();
    $request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_barlinks
		ORDER BY ord', array(
            )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
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

function twosigadDis() {

    global $txt, $context, $smcFunc;

    $context['sub_template'] = 'twosichatGadgets';
    $context['gadgets'] = array();
    $request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}2sichat_gadgets
		ORDER BY ord', array(
            )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
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

function chmodDirectory($path = '.', $level = 0) {

    $ignore = array('cgi-bin', '.', '..');
    $dh = @opendir($path);
    while (false !== ( $file = readdir($dh) )) {
        if (!in_array($file, $ignore)) {
            if (is_dir($path . '/' . $file)) {
                chmod($path . '/' . $file, 0755);
                chmodDirectory($path . '/' . $file, ($level + 1));
            } else {
                chmod($path . '/' . $file, 0755);
            }
        }
    }
    closedir($dh);
}

/**
 * @param string $dir
 */
function SAChat_deleteAll($dir) {

    if (!file_exists($dir))
        return true;
    if (!is_dir($dir) || is_link($dir))
        return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..')
            continue;
        if (!SAChat_deleteAll($dir . "/" . $item)) {
            chmod($dir . "/" . $item, 0777);
            if (!SAChat_deleteAll($dir . "/" . $item))
                return false;
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