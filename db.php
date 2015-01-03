<?php
	/**
	 * @copyright Wayne Mankertz, 2013
	 * I release this code as free software, under the MIT license.
	**/
	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $boardurl, $scripturl, $db_prefix, $smcFunc;


	// First load the SMF 2's Extra DB Functions
	db_extend('packages');
	db_extend('extra');

	add_integration_function('integrate_pre_include', '$boarddir/Sources/SAChatHooks.php');
	add_integration_function('integrate_load_permissions', 'SAChat_load_permissions', '$boarddir/Sources/SAChatHooks.php');
	add_integration_function('integrate_admin_areas', 'SAChat_admin_areas', '$boarddir/Sources/SAChatHooks.php');
	add_integration_function('integrate_load_theme', 'SAChat_loadTheme', '$boarddir/Sources/SAChatHooks.php');

	$result = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members', array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		
		$smcFunc['db_insert']('ignore', '{db_prefix}themes', 
			array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',), 
			array($row['id_member'], 1, 'show_cbar', 0,), 
			array('id_member', 'id_theme')
		);
		$smcFunc['db_insert']('ignore', '{db_prefix}themes', 
			array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',), 
			array($row['id_member'], 1, 'show_cbar_buddys', 0,), 
			array('id_member', 'id_theme')
		);
	}

	$smcFunc['db_create_table']('{db_prefix}2sichat_typestaus', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'status',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'member_id',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
	), 
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		),
		array(), 'update');
			
	$smcFunc['db_create_table']('{db_prefix}2sichat_error', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'type',
			'type' => 'varchar',
			'size' => 255,
			'null' => false,
		),
		array(
			'name' => 'line',
			'type' => 'int',
			'size' => 4,
			'null' => false,
		),
		array(
			'name' => 'file',
			'type' => 'varchar',
			'size' => 255,
			'null' => false,
		),
		array(
			'name' => 'info',
			'type' => 'varchar',
			'size' => 255,
			'null' => false,
		),
	), 
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		),
		array(), 'update');

	$smcFunc['db_create_table']('{db_prefix}2sichat_gchat', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'from',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'msg',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'room',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'sent',
			'type' => 'int',
			'size' => 10,
			'null' => false,
		),
		 array(
			'name' => 'rd',
			'type' => 'bigint',
			'size' => 20,
			'default' => 0,
			'null' => false,
		),
	),  
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		), 
		array(), 'update');
	$smcFunc['db_remove_column'](
		'{db_prefix}2sichat_gchat',
		 'sent'
	);
	$smcFunc['db_add_column']('{db_prefix}2sichat_gchat',
		array(
			'name' => 'sent',
			'type' => 'int',
			'size' => 10,
			'null' => false,
		),
		array(),
		'ignore',
		'fatal'
	);

	$smcFunc['db_add_column']('{db_prefix}2sichat_gchat',
		array(
			 'name' => 'rd',
			'type' => 'bigint',
			'size' => 20,
			'default' => 0,
			'null' => false,
		),
		array(),
		'ignore',
		'fatal'
	);
		
	$smcFunc['db_create_table']('{db_prefix}2sichat', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'to',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'from',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'msg',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'sent',
			'type' => 'DATETIME',
			'null' => false,
		),
		array(
			'name' => 'inactive',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'rd',
			'type' => 'bigint',
			'size' => 20,
			'default' => 0,
			'null' => false,
		),
		array(
			'name' => 'isrd',
			'type' => 'DATETIME',
			'null' => false,
		),
	),  
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		), 
		array(), 'update');
		
	$smcFunc['db_remove_column'](
		'{db_prefix}2sichat',
		 'sent'
	);

	$smcFunc['db_add_column']('{db_prefix}2sichat',
		array(
			'name' => 'sent',
			'type' => 'DATETIME',
			'null' => false,
		),
		array(),
		'ignore',
		'fatal'
	);

	$smcFunc['db_add_column']('{db_prefix}2sichat',
		array(
			'name' => 'inactive',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(),
		'ignore',
		'fatal'
	);

	$smcFunc['db_add_column']('{db_prefix}2sichat',
		array(
			'name' => 'isrd',
			'type' => 'DATETIME',
			'null' => false,
		),
		array(),
		'ignore',
		'fatal'
	);

	$smcFunc['db_insert']('ignore', '{db_prefix}settings', 
		array(
			'variable' => 'string',
			'value' => 'string',
		),
		array(
			array('2sichat_mn_heart', '10000'),
			array('2sichat_mn_heart_timeout', '40000'),
			array('2sichat_cw_heart', '5000'),
			array('2sichat_mn_heartmin', '33000'),
			array('2sichat_live_notfy','1'),
			array('2sichat_e_last3min', '1'),
			array('2sichat_e_last3minv', '180'),
			array('2sichat_live_type', '0'),
			array('2sichat_purge', '1'),
			array('2sichat_gad_lang', 'en'),
			array('2sichat_gad_trans', '1'),
			array('2sichat_ico_myspace', '1'),
			array('2sichat_ico_gplus', '1'),
			array('2sichat_ico_twit', '1'),
			array('2sichat_ico_fb', '1'),
			array('2sichat_ico_adthis', '1'),
			array('2sichat_board_index', 'everywhere'),
			array('2sichat_theme', 'default'),
			array('2sichat_live_online', '1'),
			array('2sichat_e_logs', '1'),
			array('2sichat_cookie_name', '2sichat')
		), 
		array()
	);

	$smcFunc['db_create_table']('{db_prefix}2sichat_gadgets', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'title',
			'type' => 'varchar',
			'size' => 255,
			'null' => false,
		),
		array(
			'name' => 'url',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'width',
			'type' => 'int',
			'size' => 4,
			'null' => false,
		),
		array(
			'name' => 'height',
			'type' => 'int',
			'size' => 4,
			'null' => false,
		),
		array(
			'name' => 'ord',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		),
		array(
			'name' => 'vis',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		),
		array(
			'name' => 'type',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		),
	), 
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		), 
		array(), 'update');

	$smcFunc['db_add_column']('{db_prefix}2sichat_gadgets', 
		array(
			'name' => 'type',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		));

	$smcFunc['db_create_table']('{db_prefix}2sichat_barlinks', array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
			'null' => false,
		),
		array(
			'name' => 'newwin',
			'type' => 'int',
			'size' => 11,
			'null' => false,
		),
		array(
			'name' => 'title',
			'type' => 'varchar',
			'size' => 255,
			'null' => false,
		),
		array(
			'name' => 'url',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'image',
			'type' => 'text',
			'null' => false,
		),
		array(
			'name' => 'ord',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		),
		array(
			'name' => 'vis',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
		),
	), 
		array(
			array(
				'name' => 'id',
				'type' => 'primary',
				'columns' => array('id'),
			),
		), 
		array(), 'update');

	$smcFunc['db_add_column']('{db_prefix}2sichat_barlinks', 
		array(
			'name' => 'newwin',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		)
	);

	$result = $smcFunc['db_query']('', '
		  SELECT id
		  FROM {db_prefix}2sichat_barlinks
		  LIMIT 1', array());

	list ($has_link) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	if (empty($has_link)) {
		$smcFunc['db_insert']('ignore', '{db_prefix}2sichat_barlinks',

			array(
				'title' => 'string',
				'url' => 'string',
				'image' => 'string',
				'vis' => 'int',
				'ord' => 'int',
			),
			array(
			// home
				array(
					'title' => 'Home',
					'url' => $scripturl,
					'image' => $boardurl . '/sachat/themes/default/images/home.png',
					'vis' => 3,
					'ord' => 0,
				),
				// messages
				array(
					'title' => 'Messages',
					'url' => $scripturl . '?action=pm',
					'image' => $boardurl . '/sachat/themes/default/images/mail.png',
					'vis' => 3,
					'ord' => 1,
				),
			), 
			array()
		);
	}
?>