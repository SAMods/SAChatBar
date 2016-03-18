<?php
if (!defined('SMF'))
	die('No direct access...');
/*
@Name: Gadgets
@Description: Create php/html gadgets. These gadgets are diplayed in the chat tools.
@Author: SA
@Version: 0.1
@Author URL: http://samods.github.io/SAChatBar/
@Plugin ID: sa_gadgets
*/
	
	
//SMF hooks
register_hook('integrate_chat_admin', 'gadget_action','sa_gadgets',true);
register_hook('integrate_pre_include', '$boarddir/sachat/Plugins/gadgets/gadgetsAdmin.php','sa_gadgets',true);
register_hook('integrate_pre_include', '$boarddir/sachat/Plugins/gadgets/template/gadgetAdmin.template.php','sa_gadgets',true);
register_hook('integrate_chat_admin_template', 'chat_admin_area','sa_gadgets',true);
	
//SA Chat hooks
register_hook('hook_load_file',$boarddir.'/sachat/Plugins/gadgets/template/gadget.template.php','sa_gadgets');
register_hook('hook_load_file',$boarddir.'/sachat/Plugins/gadgets/gadgets.php','sa_gadgets');
register_hook('hook_non_actions', 'dogadgets','sa_gadgets');
register_hook('hook_initialize', 'initGadgets','sa_gadgets');
register_hook('hook_tools_template', 'template_display_gad','sa_gadgets');
register_hook('hook_load_js', 'load_gadgets_js','sa_gadgets');

//gadgets Language strings
LoadLanguage('gadgets/template/gadgets');
?>