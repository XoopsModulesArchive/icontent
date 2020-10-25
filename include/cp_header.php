<?php

/**
 * module files can include this file for admin authorization
 * the file that will include this file must be located under xoops_url/modules/module_directory_name/admin_directory_name/
 */
require_once dirname(__DIR__, 2) . '/mainfile.php';
require_once '../../include/cp_functions.php';
$url_arr = explode('/', mb_strstr($xoopsRequestUri, '/modules/')); // Thanks mercibe
$moduleHandler = xoops_getHandler('module');
$xoopsModule = $moduleHandler->getByDirname($url_arr[2]); // Thanks mercibe

unset($url_arr);
if (!is_object($xoopsModule) || !$xoopsModule->getVar('isactive')) {
    redirect_header(XOOPS_URL . '/', 1, _MODULENOEXIST);

    exit();
}
$modulepermHandler = xoops_getHandler('groupperm');
if ($xoopsUser) {
    if (!$modulepermHandler->checkRight('module_admin', $xoopsModule->getVar('mid'), $xoopsUser->getGroups())) {
        redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

        exit();
    }
} else {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

    exit();
}
// set config values for this module
if (1 == $xoopsModule->getVar('hasconfig') || 1 == $xoopsModule->getVar('hascomments')) {
    $configHandler = xoops_getHandler('config');

    $xoopsModuleConfig = &$configHandler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
}
// include the default language file for the admin interface
if (file_exists('language/' . $xoopsConfig['language'] . '/admin.php')) {
    include 'language/' . $xoopsConfig['language'] . '/admin.php';
}
