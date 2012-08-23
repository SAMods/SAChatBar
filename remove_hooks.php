<?php

global $txt, $smcFunc, $db_prefix, $modSettings;
global $project_version, $addSettings, $permissions, $tables, $sourcedir;

if (!defined('SMF'))
	require '../SSI.php';
	
remove_integration_function('integrate_pre_include', '$boarddir/Sources/SAChatHooks.php');
remove_integration_function('integrate_load_permissions', 'SAChat_load_permissions');
remove_integration_function('integrate_admin_areas', 'SAChat_admin_areas');
remove_integration_function('integrate_buffer', 'SAChat_load_buffer');
remove_integration_function('integrate_load_theme', 'SAChat_loadTheme');

?>