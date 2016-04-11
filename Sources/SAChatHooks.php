<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (!defined('SMF'))
		die('Hacking attempt...');

	/**
	 * @param string $key
	 */
	function sachat_array_insert(&$input, $key, $insert, $where = 'before', $strict = false) {
		$position = array_search($key, array_keys($input), $strict);

		// Key not found -> insert as last
		if ($position === false) {
			$input = array_merge($input, $insert);
			return;
		}

		if ($where === 'after')
			$position += 1;

		// Insert as first
		if ($position === 0)
			$input = array_merge($insert, $input);
		else
			$input = array_merge(
					array_slice($input, 0, $position), $insert, array_slice($input, $position)
			);
	}

	function SAChat_loadTheme() {
		global $boarddir, $context;

		loadLanguage('2sichat');
	
		if (!isset($_REQUEST['xml'])) {
			$layers = $context['template_layers'];
			$context['template_layers'] = array();
			foreach ($layers as $layer) {
				$context['template_layers'][] = $layer;
				if ($layer == 'body' || $layer == 'main')
					$context['template_layers'][] = 'sachat';
			}
		}
		
		$context['html_headers'] .= SAChat_showBar('head');
	}

	function template_sachat_above() {
		echo SAChat_showBar('body');
	}

	function template_sachat_below() {}

	/**
	 * @param string $type
	 */
	function SAChat_showBar($type) {
		global $modSettings, $options, $settings, $boardurl, $context;

		//explode our actions
		$actions = explode(',', $modSettings['2sichat_board_index']);

		//is the bar disabled on this smf theme
		if (!empty($modSettings['2sichat_disabled_themes'])) {
			$disabled_theme = explode('|', $modSettings['2sichat_disabled_themes']);

			if (in_array($settings['theme_id'], $disabled_theme))
				return;
		}

		//get our actions
		SAChat_getActions($actions);

		//work out where the bar should be shown
		if (in_array($context['current_action'], $actions) && !empty($modSettings['2sichat_board_index']))//certain actions
			$bar = '<script type="text/javascript" src="' . $boardurl . '/sachat/index.php?action=' . $type . '"></script>';
		elseif (in_array('everywhere', $actions) && !empty($modSettings['2sichat_board_index']))//show everywhere
			$bar = '<script type="text/javascript" src="' . $boardurl . '/sachat/index.php?action=' . $type . '"></script>';
		else//nothing defined
			$bar = '';

		return $bar;
	}
 
	function SAChat_getActions($actions) {
		global $context;

		//define some extra actions
		if (empty($context['current_action']) && !isset($_REQUEST['board']) && !isset($_REQUEST['topic']) && in_array('board_index', $actions))
			$context['current_action'] = 'board_index'; //boardindex
		elseif (empty($context['current_action']) && isset($_REQUEST['board']) && !isset($_REQUEST['topic']) && isset($_REQUEST['action']) != 'post' && in_array('message_index', $actions))
			$context['current_action'] = 'message_index'; //message index
		elseif (empty($context['current_action']) && !isset($_REQUEST['board']) && isset($_REQUEST['topic']) && isset($_REQUEST['action']) != 'post' && in_array('topic_index', $actions))
			$context['current_action'] = 'topic_index'; //topic index
		else
			$context['current_action'] = $context['current_action']; //main actions
	}

	function SAChat_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions) {
		global $context;

		$permissionList['membergroup'] += array(
			'2sichat_access' => array(false, '2sichat', '2sichat'),
			'2sichat_chat' => array(false, '2sichat', '2sichat'),
			'2sichat_bar' => array(false, '2sichat', '2sichat'),
		);

		$context['non_guest_permissions'] = array_merge(
			$context['non_guest_permissions'], array(
				//'2sichat_access',
				'2sichat_chat',
			)
		);
	}

	function SAChat_admin_areas(&$admin_areas) {
		global $context, $modSettings, $scripturl, $txt;

		sachat_array_insert($admin_areas, 'layout', array(
			'sachat' => array(
				'title' => $txt['2sichat'],
				'permission' => array('admin_forum'),
				'areas' => array(
					'sachat' => array(
						'label' => $txt['twosichatConfig'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichatAdmin',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							'config' => array($txt['twosichatConfig']),
							'chat' => array($txt['twosichatChat']),
							'load' => array($txt['2sichatloadbal']),
							//'theme' => array($txt['2sichat_theme']),
							//'plugins' => array($txt['2sichat_plugins']),
							//'maintain' => array($txt['2sichatmaintain']),
							//'errorlogs' => array($txt['error_2si']),
						),
					),
					'themesa' => array(
						'label' => $txt['2sichat_theme'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichatThemes',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							
						),
					),
					'plugins' => array(
						'label' => $txt['2sichat_plugins'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichatplugin',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							
						),
					),
					'errors' => array(
						'label' => $txt['error_2si'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichaterror',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							
						),
					),
					'plugsetting' => array(
						'label' => $txt['2sichat_plugins1'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichatmisc',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							'plugsetting' => array($txt['2sichat_plugins1']),
						),
					),
					'maintainsa' => array(
						'label' => $txt['2sichatmaintain'],
						'file' => 'sachatAdmin.php',
						'function' => 'twosichatchmod',
						'icon' => 'languages.gif',
						'permission' => array('admin_forum'),
						'subsections' => array(
							
						),
					),
				),
			),
		));
		
		call_integration_hook('integrate_chat_admin_areas', array(&$admin_areas));
		
		if(empty($modSettings['2sichat_e_logs']))
				unset($admin_areas['sachat']['areas']['sachat']['subsections']['errorlogs']);	
	}

	function SAChat_LoadTemes() {
		global $boarddir, $dirArray, $indexCount;

		// open this directory 
		$myDirectory = opendir($boarddir . '/sachat/themes');

		// get each entry
		while ($entryName = readdir($myDirectory)) {
			$dirArray[] = $entryName;
		}

		// close directory
		closedir($myDirectory);

		//count elements in array
		$indexCount = count($dirArray);

		// sort 'em
		sort($dirArray);
	}

?>