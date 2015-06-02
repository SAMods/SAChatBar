<?php
if (!defined('SMF'))
	die('No direct access...');
/*
@Name: Links
@Description: Create Links. These gadgets are diplayed in the chat tools.
@Author: SA
@Version: 0.1
@Author URL: http://samods.github.io/SAChatBar/
@Plugin ID: sa_links
*/
	
	
//SMF hooks
register_hook('integrate_chat_admin', 'links_action','sa_links',true);
register_hook('integrate_pre_include', '$boarddir/sachat/Plugins/links/linksAdmin.php','sa_links',true);
register_hook('integrate_pre_include', '$boarddir/sachat/Plugins/links/template/linksAdmin.template.php','sa_links',true);
register_hook('integrate_chat_admin_template', 'link_admin_area','sa_links',true);
	
//SA Chat hooks
register_hook('hook_load_file',$boarddir.'/sachat/Plugins/links/template/links.template.php','sa_links');
register_hook('hook_load_file',$boarddir.'/sachat/Plugins/links/links.php','sa_links');
register_hook('hook_initialize', 'initLink','sa_links');
register_hook('hook_tools_template_bot', 'template_display_link','sa_links');

//gadgets Language strings
LoadLanguage('links/template/links');
?>