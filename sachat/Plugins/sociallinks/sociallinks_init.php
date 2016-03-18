<?php
if (!defined('SMF'))
	die('No direct access...');
/*
@Name: Social links
@Description: Adds various social links to Facebook, Twitter, Gplus, Addthis and Myspace
@Author: SA
@Version: 0.1
@Author URL: http://samods.github.io/SAChatBar/
@Plugin ID: sa_soc_links
@Uninstaller: sociallinks/uninstall_sociallinks.php
*/
	
	
//SMF hooks
register_hook('integrate_pre_include', $boarddir.'/sachat/Plugins/sociallinks/sociallinksAdmin.php','sa_soc_links',true);
register_hook('integrate_chat_admin_config', 'soc_config','sa_soc_links',true);
	
//SA Chat hooks
register_hook('hook_load_file', $boarddir.'/sachat/Plugins/sociallinks/template/sociallinks.template.php','sa_soc_links');
register_hook('hook_tools_template', 'template_display_soc_link','sa_soc_links');
register_hook('hook_load_js', 'load_soc_js','sa_soc_links');

//sociallinks Language strings
LoadLanguage('sociallinks/template/sociallinks');

?>