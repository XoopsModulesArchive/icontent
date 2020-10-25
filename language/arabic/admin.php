<?php

//Admin Constants
define('_IC_ADMINTITLE', 'iContent administration');
define('_IC_HOME_MENU', 'Home');
define('_IC_DIRECTORYADMIN_MENU', 'Directories administration');
define('_IC_PAGEADMIN_MENU', 'Pages administration');
define('_IC_SHORTCUTADMIN_MENU', 'Shortcuts administration');

//Directories admin constants
define('_IC_DIRECTORIESADMIN', 'Directories administration');
//Directories activation
define('_IC_DIRECTORIESNOTACTIVATED', 'Directories not acivated');
define('_IC_ACTIVATEDIRECTORIES', 'Activate directories');
define('_IC_DIRECTORIESACTIVATION', 'Directories activation');
define('_IC_DIRECTORIESACTIVATED', 'Directories correctly activated');
//Directories configuration
define('_IC_CONFIGURABLEDIRECTORIES', 'Configurable directories');
define('_IC_CONFIGUREDIRECTORIES', 'Configure the directories');
define('_IC_DIRECTORIESCONFIGURED', 'Directories configured');
//Directories desactivation
define('_IC_DESACTIVABLEDIRECTORIES', 'Desactivable directories');
define('_IC_DESACTIVATEDIRECTORIES', 'Desactivate the directories');
define('_IC_DIRECTORIESDESACTIVATION', 'Directories desactivation');
//Directories homepage
define('_IC_HOMEPAGECREATED', 'Homepage created');
define('_IC_HOMEPAGE', 'Homepage');
define('_IC_NOHOMEPAGE', 'No homepage');
//Misc
define('_IC_DIRECTORIES', 'directories');
define('_IC_CURRENTDIRECTORY', 'Current directory');
define('_IC_GOTOPAGEADMIN', 'Go to directories admin in the same directory');
define('_IC_BACKTODIRECTORYADMIN', 'Back to directories admin');
//Confirmations
define('_IC_CONFIRM_DIRECTORIESDESACTIVATION', 'Do you really want to desactivate these directories ?');
//Errors
define('_IC_ERROR_SELECTDIRECTORIES', 'Select first on or several directories');
define('_IC_ERROR_DIRECTORYACTIVATION', 'Unable to activate the directory');
define('_IC_ERROR_DIRECTORYDESACTIVATION', 'Unable to desactivate the directory');
define('_IC_ERROR_HOMEPAGECREATION', 'Unable to create the homepage');

//Pages admin constants
define('_IC_PAGESADMIN', 'Pages administration');
//Pages activation
define('_IC_PAGESNOTACTIVATED', 'Pages not activated');
define('_IC_ACTIVATEPAGES', 'Activate the pages');
define('_IC_PAGESACTIVATION', 'Pages activation');
//Pages configuration
define('_IC_CONFIGURABLEPAGES', 'Configurable pages');
define('_IC_CONFIGUREPAGES', 'Configure the pages');
define('_IC_PAGESCONFIGURED', 'Pages configured');
//Pages modification
define('_IC_PAGEMODIFICATION', 'Page modification');
define('_IC_PAGEMODIFIED', 'Page modified');
//Pages desactivation
define('_IC_DESACTIVABLEPAGES', 'Desactivable pages');
define('_IC_DESACTIVATEPAGES', 'Desactivate the pages');
define('_IC_PAGESDESACTIVATION', 'Pages desactivation');
define('_IC_RELATEDSHORTCUTSDELETED', 'Related shortcuts deleted');
define('_IC_RELATEDCOMMENTSDELETED', 'Related comments deleted');
//Pages compiled
define('_IC_PURGECOMPILEDPAGES', 'Purge the compiled pages');
define('_IC_COMPILEDPAGESPURGED', 'Compiled pages purged');
//Misc
define('_IC_PAGE', 'Page');
define('_IC_PAGES', 'page(s)');
define('_IC_PAGEURL', 'Page url');
define('_IC_GOTODIRECTORYADMIN', 'Go to directories admin in the same directory');
define('_IC_BACKTOPAGEADMIN', 'Back to page admin');
define('_IC_MAKEITHOMEPAGEOF', 'Make it homepage of');
define('_IC_PAGEUPDATED', 'Page successfully updated');
define('_IC_UPDATETHEPAGE', 'Update the page');
//Confirmations
define('_IC_CONFIRM_PAGESDESACTIVATION', 'Do your really want to desactivate these pages ?');
//Errors
define('_IC_ERROR_PAGELOADING', 'Unable to load the page');
define('_IC_ERROR_SELECTPAGES', 'Select first one or several pages');
define('_IC_ERROR_PAGEACTIVATION', 'Unable to activate the page');
define('_IC_ERROR_PAGECONFIGURATION', 'Unable to configure the page');
define('_IC_ERROR_PAGEMODIFICATION', 'Unable to modify the page');
define('_IC_ERROR_PAGEDESACTIVATION', 'Unable to desactivate the page');
define('_IC_ERROR_PAGEUPDATE', 'Unable to update the page');
define('_IC_ERROR_PAGENOTACTIVATED', "The page isn't activated");

//Shortcuts admin constant
define('_IC_SHORTCUTSADMIN', 'Shortcuts administration');
//Shortcuts creation
define('_IC_SHORTCUTCREATION', 'Shortcut creation');
define('_IC_SHORTCUTCREATED', 'Shortcut created');
define('_IC_SHORTCUTALREADYCREATED', 'Shortcut already created');
//Shortcuts deleting
define('_IC_SHORTCUTDELETED', 'Shortcut deleted');
//Shortcuts configuration
define('_IC_SHORTCUTCONFIGURATION', 'Shortcut configuration');
define('_IC_SHORTCUTCONFIGURED', 'Shortcut configured');
//Misc
define('_IC_SHORTCUTSLIST', 'Shortcuts list');
define('_IC_SHORTCUTTO', 'Shortcut to');
//Confirmation constants
define('_IC_CONFIRM_SHORTCUTDELETING', 'Do you really want to delete this shortcut ?');
//Errors constants
define('_IC_ERROR_SHORTCUTCREATION', 'Unable to create the shortcut');
define('_IC_ERROR_SHORTCUTCONFIGURATION', 'Unable to configure the shortcut');
define('_IC_ERROR_SHORTCUTDELETING', 'Unable to delete the shortcut');

//Misc constants
define('_IC_ACTIVATED', 'activated');
define('_IC_NORECORD', 'No record');
define('_IC_ACTIONS', 'Actions');
define('_IC_CONFIGURE', 'Configure');
define('_IC_NAME', 'Name');
define('_IC_CREATE', 'Create');
define('_IC_CANCEL', 'Cancel');
define('_IC_ID', 'Id');
define('_IC_DELETE', 'Delete');
define('_IC_DESACTIVATED', 'desactivated');
define('_IC_DESACTIVATE', 'Desactivate');
define('_IC_DELETED', 'deleted');
define('_IC_HIDDEN', 'Hidden');
define('_IC_YES', 'Yes');
define('_IC_NO', 'No');
define('_IC_ACCESSRIGHTS', 'Access rights');
define('_IC_CONTAINS', 'Contains');
define('_IC_COMMENTSOF', 'Comments of');
define('_IC_AND', 'and');
define('_IC_REALNAME', 'Real name');
define('_IC_URL', 'Url');
define('_IC_CONFIGURED', 'Configured');
define('_IC_MODIFY', 'Modify');
//Errors constants
define("_IC_ERROR_CHMOD',"WARNING : the 'incontent/compiled' direcory isn't writable !\n Thanks to chmod it 777 if not iContent will not work");
define("_IC_ERROR_IMGLIB',"WARNING :  the directory 'incontent/inPages/imagesLibrary' was removed. The  wysiwyg editor will not work correctly");
define('_IC_ERROR_COMMENTSDELETING', 'Unable to delete the comments of');

