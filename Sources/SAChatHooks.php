<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function sachat_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);
	
	// Key not found -> insert as last
	if ($position === false)
	{
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
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}

function SAChat_loadtheme(){
    global $options, $modSettings, $user_info, $context;
	 
	loadLanguage('2sichat');

	if ($context['user']['is_logged'] && empty($options['cbar_opt_done'])){
	   
		SAChat_InsertOptions($user_info['id'],'show_cbar',0);
        SAChat_InsertOptions($user_info['id'],'show_cbar_buddys',0);
		SAChat_InsertOptions($user_info['id'],'cbar_opt_done',1);
	}
	
	if (!isset($_REQUEST['xml']))
    {
        $layers = $context['template_layers'];
        $context['template_layers'] = array();
        foreach ($layers as $layer)
        {
            $context['template_layers'][] = $layer;
            if ($layer == 'body' || $layer == 'main')
                $context['template_layers'][] = 'sachat';
        }
    }

    $context['html_headers'] .= SAChat_showBar('head');
	if($modSettings['2sichat_ico_adthis']){
	    $context['html_headers'] .= '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-503f263237ff99da"></script>';
    }
}

function SAChat_InsertOptions($chatmem, $chatvar, $chatval){
    global $smcFunc;
	
    $smcFunc['db_insert']('ignore',
        '{db_prefix}themes',
        array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',),
        array($chatmem, 1, $chatvar, $chatval,),
        array('id_member', 'id_theme')
    );

}

function template_sachat_above(){
	
	echo SAChat_showBar('body');
}

function template_sachat_below(){}

function SAChat_showBar($type){
   global $modSettings, $options, $boardurl, $context;
   
    //explode our actions
	$actions = explode(',', $modSettings['2sichat_board_index']);
	
	//set our default theme if none set
	if(empty($modSettings['2sichat_theme']))
	    $modSettings['2sichat_theme'] = 'default';
	
	//load our theme
	$sachatTheme = $modSettings['2sichat_theme'];
    
	//get our actions
	SAChat_getActions($actions);	
    
	//work out where the bar should be shown
    if (in_array($context['current_action'],$actions) && !empty($modSettings['2sichat_board_index']) && empty($options['show_cbar']))//certain actions
	      $bar = '<script type="text/javascript" src="'.$boardurl.'/sachat/index.php?action='.$type.'&amp;theme='.$sachatTheme.'"></script>';
	elseif (in_array('everywhere',$actions) && !empty($modSettings['2sichat_board_index']) && empty($options['show_cbar']))//show everywhere
	      $bar ='<script type="text/javascript" src="'.$boardurl.'/sachat/index.php?action='.$type.'&amp;theme='.$sachatTheme.'"></script>'; 
    else//nothing defined
         $bar =''; 	
		 
	return $bar;
}

function SAChat_getActions($actions){
   global $context;
	 
	//define some extra actions
	if(empty($context['current_action']) && !isset($_REQUEST['board']) && !isset($_REQUEST['topic']) && in_array('board_index',$actions))
	    $context['current_action'] = 'board_index'; //boardindex
	elseif(empty($context['current_action']) && isset($_REQUEST['board']) && !isset($_REQUEST['topic']) && isset($_REQUEST['action']) != 'post' && in_array('message_index',$actions))
        $context['current_action'] = 'message_index';//message index
	elseif(empty($context['current_action']) && !isset($_REQUEST['board']) && isset($_REQUEST['topic']) && isset($_REQUEST['action']) != 'post' && in_array('topic_index',$actions))
        $context['current_action'] = 'topic_index';//topic index
	else
	    $context['current_action'] = $context['current_action'];//main actions
}

function SAChat_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context;
	
	$permissionList['membergroup'] += array(
		'2sichat_access' => array(false, '2sichat', '2sichat'),
		'2sichat_chat' => array(false, '2sichat', '2sichat'),
		'2sichat_bar' => array(false, '2sichat', '2sichat'),
		'2sichat_bar_close' => array(false, '2sichat', '2sichat'),
		'2sichat_bar_buddys' => array(false, '2sichat', '2sichat'),
	);
	
	$context['non_guest_permissions'] = array_merge(
		$context['non_guest_permissions'],
		    array(
		        '2sichat_access',
		        '2sichat_chat',
		)
	);
}

function SAChat_admin_areas(&$admin_areas)
{
	global $context, $modSettings, $scripturl, $txt;
	
	sachat_array_insert($admin_areas, 'layout',
		array(
			
			'sachat' => array(
				'title' => $txt['2sichat'],
				'permission' => array('admin_forum'),
				'areas' => array(
					'sachat' => array(
					'label' => $txt['2sichat'],
					'file' => 'sachatAdmin.php',
					'function' => 'twosichatAdmin',
					'icon' => 'languages.gif',
					'permission' => array('admin_forum'),
					'subsections' => array(
				     	'config' => array($txt['twosichatConfig']),
				     	'gadget' => array($txt['twosichatGadget']),
						'link' => array($txt['2sichat_linksd2']),
						'load' => array($txt['2sichatloadbal']),
						'theme' => array($txt['2sichat_theme']),
						'chmod' => array($txt['2sichatchmod']),
					),
				 ),	
		      ),
	       ),
        )
    );
}

?>