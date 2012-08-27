<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $boardurl, $scripturl, $db_prefix, $smcFunc;


// First load the SMF 2's Extra DB Functions
    db_extend('packages');
	db_extend('extra');

	add_integration_function('integrate_pre_include', '$boarddir/Sources/SAChatHooks.php');
    add_integration_function('integrate_load_permissions', 'SAChat_load_permissions');
    add_integration_function('integrate_admin_areas', 'SAChat_admin_areas');
	add_integration_function('integrate_load_theme', 'SAChat_loadTheme');
	
    $result = $smcFunc['db_query']('','
	    SELECT id_member
	    FROM {db_prefix}members',
	    array()
    );
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $smcFunc['db_insert']('ignore',
            '{db_prefix}themes',
            array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',),
            array($row['id_member'], 1, 'show_cbar', 0,),
            array('id_member', 'id_theme')
        );
		$smcFunc['db_insert']('ignore',
            '{db_prefix}themes',
            array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',),
            array($row['id_member'], 1, 'show_cbar_buddys', 0,),
            array('id_member', 'id_theme')
        );
	}
	
	$smcFunc['db_create_table']('{db_prefix}2sichat',
	array(
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
			'type' => 'timestamp',
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
		array(),
	'update');

	
	$smcFunc['db_insert']('ignore', '{db_prefix}settings',
		array(
			'variable' => 'string',
			'value' => 'string',
		),
		array(
			array ('2sichat_mn_heart' ,'10000'),
			array ('2sichat_cw_heart' ,'5000'),
			array ('2sichat_purge' ,'1'),
			array ('2sichat_gad_lang' ,'en'),
			array ('2sichat_gad_trans' ,'1'),
			array ('2sichat_ico_myspace' ,'1'),
			array ('2sichat_ico_twit' ,'1'),
			array ('2sichat_ico_fb' ,'1'),
			array ('2sichat_board_index' ,'everywhere'),
			array ('2sichat_theme' ,'default'),
		),
		array()
	);
	
	$smcFunc['db_create_table']('{db_prefix}2sichat_gadgets',
	array(
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
		array(),
	'update');
	
	$smcFunc['db_add_column']('{db_prefix}2sichat_gadgets', 
	array(
	        'name' => 'type',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
			'null' => false,
	));	
	
	$smcFunc['db_create_table']('{db_prefix}2sichat_barlinks',
	array(
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
		array(),
	'update');
	
	$smcFunc['db_add_column']('{db_prefix}2sichat_barlinks', 
	   array(
	       'name' => 'newwin',
	       'type' => 'int',
	       'size' => 11,
	       'default' => 0,
	   )
	);		
	
   $result = $smcFunc['db_query']('','
	  SELECT id
	  FROM {db_prefix}2sichat_barlinks
	  LIMIT 1',
	  array());
	  
   list ($has_link) = $smcFunc['db_fetch_row']($result);
   $smcFunc['db_free_result']($result);

   if (empty($has_link))
   {
	// Insert chat defult links
      $smcFunc['db_insert']('ignore',
            '{db_prefix}2sichat_barlinks',
	 // Fields
	 array(
		'title' => 'string',
		'url' => 'string',
		'image' => 'string',
		'vis' => 'int',
		'ord' => 'int',
		),
	
	 // Values
	 array(
		// home
		array(
			'title' => 'Home',
			'url' => $scripturl,
			'image' => $boardurl.'/sachat/themes/default/images/home.png',
			'vis' => 3,
			'ord' => 0,
			),
		// messages
		array(
			'title' => 'Messages',
			'url' => $scripturl.'?action=pm',
			'image' => $boardurl.'/sachat/themes/default/images/mail.png',
			'vis' => 3,
			'ord' => 1,
			),
		),
	array());
}

?>