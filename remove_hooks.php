<?php
/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/
global $boarddir;

if (!defined('SMF'))
    require '../SSI.php';

remove_integration_function('integrate_pre_include', '$boarddir/Sources/SAChatHooks.php');
remove_integration_function('integrate_load_permissions', 'SAChat_load_permissions');
remove_integration_function('integrate_admin_areas', 'SAChat_admin_areas');
remove_integration_function('integrate_buffer', 'SAChat_load_buffer');
remove_integration_function('integrate_load_theme', 'SAChat_loadTheme');
?>