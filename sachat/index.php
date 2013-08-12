<?php

/**
 * @copyright Wayne Mankertz, 2013
 * I release this code as free software, under the MIT license.
**/

//Define SMF
define('SMF', 1);

//set the micro start time
$time_bstart = microtime();

//debug load time not much to see realy load times and db query count
$debug_load = true;

//Experimental Optimizer
define('loadOpt', 1);

session_start();
session_cache_limiter('nocache'); //Shouldent this be before session_start() http://php.net/manual/en/function.session-cache-limiter.php
//Lets go head and load the settings here.
require_once(str_replace('//', '/', dirname(__FILE__) . '/') . '../Settings.php');

//Lets go head and load the functions here.
require_once(dirname(__FILE__) . '/functions.php');

// Register a error handler
require_once(dirname(__FILE__) . '/error_handler.php');
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');

//Load SMF's compatibility file for unsupported functions.
if (@version_compare(PHP_VERSION, '5') == -1) {
    require_once($sourcedir . '/Subs-Compat.php');
}

//Load our theme
list ($themeurl, $themedir, $thjs, $load_btime) = initTheme();
require_once($themedir . '/template.php');

//Load our language strings
$doLang = initLang($language);
require_once($doLang);

//SMF Cookie autentication!!!
list ($member_id, $password) = initCookies();

//Load Optimizer if applicable
if (defined('loadOpt')) {
    loadOpt();
}

// Connect to the database
$smcFunc = array();
loadDatabase();

//Do charset if needed!
if (!empty($db_character_set)) {
    doCharset($db_character_set);
}

//Load modsettings array
$modSettings = initModSettings();

//Is this enabled?
if (!empty($modSettings['2sichat_disable'])) {
    die();
}

//Do the load check if applicable
if (!empty($modSettings['2sichat_load_chk'])) {
    doLoadCHK();
}

// If it is a member lets load some data.
if ($member_id != 0) {

    $OnCount = genMemcount();
    $user_settings = loadUserSettings($member_id);

    if (!empty($modSettings['2sichat_permissions'])) {
        $permission = loadPermissions($user_settings['groups']);
    }
    // Load $buddy_settigns if we are chatting.
    if (isset($_REQUEST['cid']) || isset($_REQUEST['update'])) {
        if (isset($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) {
            $buddy_id = $_REQUEST['cid'];
        } else if (is_numeric($_REQUEST['update'])) {
            $buddy_id = $_REQUEST['update'];
        } else {
            die(); // Something fishy about a non numeric buddy id.
        }
        if ($buddy_id) {
            $buddy_settings = loadUserSettings($buddy_id);
        }
    }
} else if (!empty($modSettings['2sichat_permissions'])) {
    $permission = loadPermissions(-1); // -1 is guest.
}

//Lets see if 2-SI Chat is enabled for this group.
if (!empty($modSettings['2sichat_permissions']) && empty($permission['2sichat_access']) && empty($permission['is_admin']) && empty($permission['is_mod'])) {
    $context['JSON']['STATUS'] = 'NO ACCESS'; // Sorry but you don't have access
    doOutput();
} else if (!empty($modSettings['2sichat_permissions']) && empty($permission['is_admin']) && empty($permission['is_mod'])) {
    // Lets just hook into the modSettings
    if (empty($permission['2sichat_chat'])) {
        $modSettings['2sichat_dis_list'] = 1;
        $modSettings['2sichat_dis_chat'] = 1;
    }
    if (!empty($permission['2sichat_bar'])) {
        $modSettings['2sichat_dis_bar'] = 1;
    }
}
// Lets validate the password, anyone can put a number in a cookie, lets see if the password checks out.
if (isset($user_settings) && strlen($password) != 40 || isset($user_settings) && sha1($user_settings['passwd'] . $user_settings['password_salt']) != $password) {
    $context['JSON']['STATUS'] = 'AUTH FAILED';
    doOutput();
} else {
    $context['JSON']['STATUS'] = 'ACTIVE';
}

// Check actions
if (isset($_REQUEST['action'])) {

    // If we are loading the main javascript lets get it ready based on the user
    if ($_REQUEST['action'] == 'body') {
        initJs('body');
        initLink();
        initGadgets();
        initchat();
        initCleanup();
    }
    if ($_REQUEST['action'] == 'heart' && $member_id) {
        doheart();
    }
    if ($_REQUEST['action'] == 'head') {
        initJs('head');
        $context['HTML'] = ' ';
    }
} else {
    //No action defined so lets assume they are using the chat.
    if (!isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id) {
        // Must be starting out.
        initChatSess();
    } else if (isset($_REQUEST['msg']) && isset($_REQUEST['cid']) && $member_id) {
        savemsg();
    } else if (isset($_REQUEST['update']) && $member_id) {
        retmsgs();
    } else if (isset($_REQUEST['gid'])) {
        gadget();
    }
}
if ($member_id && isset($_REQUEST['action']) && $_REQUEST['action'] == 'heart' && !empty($modSettings['2sichat_live_online']) || $member_id && !isset($_REQUEST['action']) && !empty($modSettings['2sichat_live_online'])) {
    liveOnline();
}
// If output function hasn't been declared lets do it.
doOutput();
?>