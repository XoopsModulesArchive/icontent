<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// CLASS DEPENDANCE
//==========================================================================

// Tools class dependence
if (!isset($tools)) {
    if (file_exists('class/tools.class.php')) {
        require_once __DIR__ . '/class/tools.class.php';

        $tools = new tools();
    } else {
        trigger_error('unable to find class <b><i>tools.class.php</i></b>', E_USER_ERROR);
    }
}

//==========================================================================
// DIRECTORY ADMIN CLASS
//==========================================================================

class directoryAdmin
{
    public $parentModule;

    public $parentDirectory;

    public $currentDirectory;

    public $childDirectories;

    public $directories;

    /**
     * @Constructor of this class.
     * @Purpose     : initialises the class.
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->parentDirectory = [];

        $this->currentDirectory = ['id' => 0, 'pid' => -1, 'name' => 'inPages', 'url' => 'inPages'];

        $this->childDirectories = [];

        $this->directories = [];
    }

    /**
     * @Purpose : returns the name of the current directory from an url
     * @param mixed $url
     * @return string|string[]
     * @return string|string[]
     */
    public function getDirName($url)
    {
        // $url contains a directory url

        // Returns only the name of the directory

        $dirNames = explode('/', $url);

        $dirName = str_replace('_', ' ', $dirNames[(count($dirNames) - 1)]);

        return $dirName;
    }

    /**
     * @Purpose : gets all the choosen directories from the root to the directory specified by
     * $directoryId.
     * @param mixed $directoryId
     */
    public function getDirectories($directoryId)
    {
        global $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `id`, `pid`, `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $directoryId . "'");

        $item = $xoopsDB->fetchArray($result);

        if (0 != $directoryId) {
            $this->directories[] = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $item['id'])];

            $this->getDirectories($item['pid']);
        } else {
            $this->directories[] = ['name' => 'inPages', 'link' => 'admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=0'];

            // Puts the sections in the goog order

            $this->directories = array_reverse($this->directories);

            // Puts the indentations fort the sections

            for ($i = 0, $iMax = count($this->directories); $i < $iMax; $i++) {
                $this->directories[$i]['indentation'] = str_repeat('&nbsp;&nbsp;', $i);
            }
        }
    }

    /**
     * @Purpose : gets all the child directories of $this->currentDirectory and puts it in
     * $this->getChildDirectories.
     */
    public function getChildDirectories()
    {
        global $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `id`, `pid`, `name`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `pid`='" . $this->currentDirectory['id'] . "' ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $this->childDirectories[] = ['id' => $item['id'], 'pid' => $item['pid'], 'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'url' => $item['url'], 'link' => ('admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $item['id'])];
        }
    }

    /**
     * @Purpose : gets the number of child and activated directories since the directory
     * sepcified by $parentDirectoryId.
     * @param mixed $parentDirectoryId
     */
    public function getNbChildDirectories($parentDirectoryId)
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->queryF('SELECT `id` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `pid`='" . $parentDirectoryId . "'");

        $nbChildDirectories = $GLOBALS['xoopsDB']->getRowsNum($result);

        return $nbChildDirectories;
    }

    /**
     * @Purpose :gets the number of child and activated pages of a parent directory
     * specified by $parentDirectoryId.
     * @param mixed $parentDirectoryId
     */
    public function getNbChildPages($parentDirectoryId)
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->queryF('SELECT `id` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `directory`='" . $parentDirectoryId . "'");

        $nbChildPages = $GLOBALS['xoopsDB']->getRowsNum($result);

        return $nbChildPages;
    }

    /**
     * @Purpose : displays the main page of "Directories administration".
     */
    public function display()
    {
        global $xoopsDB, $xoopsConfig, $_GET;

        $myts = MyTextSanitizer::getInstance();

        // Gets the current directory id if transmitted

        if (isset($_GET['currentDir'])) {
            $this->currentDirectory['id'] = $_GET['currentDir'];
        }

        // Shows the chosen directories

        $this->getDirectories($this->currentDirectory['id']);

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th>' . _IC_CHOSENDIRECTORIES . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->directories); $i < $iMax; $i++) {
            echo '  <tr><td class="head">' . $this->directories[$i]['indentation'] . '<img src="images/directory.gif">&nbsp;<a href="' . $this->directories[$i]['link'] . '">' . $this->directories[$i]['name'] . "</a></td></tr>\n";
        }

        echo "</table><br>\n";

        // Initialisation of the current directory data if id = 0 we are on the root so no initialisation needed

        if (0 != $this->currentDirectory['id']) {
            $result = $xoopsDB->queryF('SELECT `pid`, `name`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->currentDirectory['id'] . "'");

            $item = $xoopsDB->fetchArray($result);

            $this->currentDirectory['pid'] = $item['pid'];

            $this->currentDirectory['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

            $this->currentDirectory['url'] = $item['url'];
        }

        // Initialisation of the parent directory if exists

        if (-1 != $this->currentDirectory['pid']) {
            if (0 != $this->currentDirectory['pid']) {
                $result = $xoopsDB->queryF('SELECT `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->currentDirectory['pid'] . "'");

                $item = $xoopsDB->fetchArray($result);

                $this->parentDirectory = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $this->currentDirectory['pid'])];
            } else {
                $this->parentDirectory = ['name' => 'inPages', 'link' => 'admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=0'];
            }
        }

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_CURRENTDIRECTORY . '&nbsp;:&nbsp;' . $this->currentDirectory['name'] . "</th></tr>\n";

        // Displays the parent directory link if exists

        if (isset($this->parentDirectory['name'])) {
            echo '  <tr><td class="head" colspan="2"><img src="images/back.gif">&nbsp;<a href="' . $this->parentDirectory['link'] . '">' . $this->parentDirectory['name'] . "</a></td></tr>\n";
        }

        // Recuperation and displaying of the child directories

        $this->getChildDirectories();

        for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
            echo "  <tr>\n"
                 . "    <td class=\"even\" style=\"width: 60%;\">\n"
                 . '      <img src="images/directory.gif">&nbsp;<a href="'
                 . $this->childDirectories[$i]['link']
                 . '" title="'
                 . $this->childDirectories[$i]['url']
                 . '">'
                 . $this->childDirectories[$i]['name']
                 . "</a>\n"
                 . "    </td>\n"
                 . '    <td class="odd" style="width: 40%;"><b>'
                 . _IC_CONTAINS
                 . "&nbsp;\n"
                 . '      '
                 . $this->getNbChildDirectories($this->childDirectories[$i]['id'])
                 . '&nbsp;'
                 . _IC_DIRECTORIES
                 . '&nbsp;'
                 . _IC_AND
                 . "&nbsp;\n"
                 . '      '
                 . $this->getNbChildPages($this->childDirectories[$i]['id'])
                 . '&nbsp;'
                 . _IC_PAGES
                 . "</b>\n"
                 . '      <br>'
                 . _IC_REALNAME
                 . '&nbsp;:&nbsp;'
                 . $this->getDirName($this->childDirectories[$i]['url'])
                 . "\n"
                 . "    </td>\n"
                 . "  </tr>\n";
        }

        echo "</table><br>\n";

        // Actions part

        echo '<font style="color: #0000FF; font-weight: bold;">[X]</font>&nbsp;<a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir='
             . $this->currentDirectory['id']
             . '">'
             . _IC_GOTOPAGEADMIN
             . "</a><br><br>\n"
             . '<font style="color: #FF0000; font-weight: bold;">[X]</font><a href="admin.php?op=directoryAdmin&amp;cop=creation&amp;currentDir='
             . $this->currentDirectory['id']
             . '">'
             . _IC_CREATEDIRRECTORYINDIRECTORY
             . '&nbsp;<i>'
             . $this->currentDirectory['name']
             . "</i></a><br><br>\n"
             . "<table class=\"outer\" style=\"width: 100%; text-align: center;\">\n"
             . '  <tr><th colspan="3">'
             . _IC_ACTIONS
             . "</th></tr>\n"
             . "  <tr>\n"
             . "    <td class=\"head\" style=\"width : 33%;\">\n";

        // Only physical dir could contain activable dirs !

        if ('Online' != $this->currentDirectory['url']) {
            // Initialisation of the explorer

            $explorer = opendir($this->currentDirectory['url']);

            echo "      <img src=\"images/desactivated.gif\"><br><br>\n"
                 . '      '
                 . _IC_DIRECTORIESNOTACTIVATED
                 . "&nbsp;:<br><br>\n"
                 . "      <form name=\"activationForm\" method=\"post\" action=\"admin.php?op=directoryAdmin&amp;cop=activate\" onSubmit=\"javascript:document.activationForm.submition.disabled=true;\">\n"
                 . "        <select name=\"childDirectoriesUrl[]\" size=\"10\" multiple=\"multiple\">\n";

            // Recuperation of non activated directories in the activation list

            while ($item = readdir($explorer)) {
                $childDirectoryUrl = $this->currentDirectory['url'] . '/' . $item;

                if (('.' != $item) && ('..' != $item) && is_dir($childDirectoryUrl)) {
                    // Checks if the directory is already activated (in the db)

                    $size = count($this->childDirectories);

                    if ($size > 0) {
                        $i = 0;

                        do {
                            $isActivated = ($childDirectoryUrl == $this->childDirectories[$i]['url']);

                            $i++;
                        } while (($i < $size) && (!$isActivated));
                    } else {
                        $isActivated = false;
                    }

                    // The directory isn't activated, we put it in the activation list

                    if (!$isActivated) {
                        echo '          <option value="' . $childDirectoryUrl . '">' . $this->getDirName($childDirectoryUrl) . "</option>\n";
                    }
                }
            }

            echo "        </select>\n" . '        <input type="hidden" name="currentDirectoryId" value="' . $this->currentDirectory['id'] . "\"><br><br>\n" . '        <input name="submition" type="submit" value="' . _IC_ACTIVATEDIRECTORIES . "\">\n" . "      </form>\n";
        } else {
            echo _IC_NOACTIVATION;
        }

        echo "    </td>\n"
             . "    <td class=\"odd\" style=\"width : 33%;\">\n"
             . "      <img src=\"images/edit.gif\"><br><br>\n"
             . '      <b>'
             . _IC_CONFIGURABLEDIRECTORIES
             . "&nbsp;:</b><br><br>\n"
             . "      <form method=\"post\" action=\"admin.php?op=directoryAdmin&amp;cop=configuration\">\n"
             . "        <select name=\"childDirectoriesId[]\" size=\"10\" multiple=\"multiple\">\n";

        for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
            echo '        <option value="' . $this->childDirectories[$i]['id'] . '">' . $this->childDirectories[$i]['name'] . "</option>\n";
        }

        echo "        </select>\n"
             . '        <input type="hidden" name="currentDirectoryId" value="'
             . $this->currentDirectory['id']
             . "\"><br><br>\n"
             . '        <input type="submit" value="'
             . _IC_CONFIGUREDIRECTORIES
             . "\">\n"
             . "      </form>\n"
             . "    </td>\n"
             . "    <td class=\"head\" style=\"width : 33%;\">\n"
             . "      <img src=\"images/desactivate.gif\"><br><br>\n"
             . '      '
             . _IC_DESACTIVABLEDIRECTORIES
             . "&nbsp;:<br><br>\n"
             . "      <form method=\"post\" action=\"admin.php?op=directoryAdmin&amp;cop=desactivation\">\n"
             . "        <select name=\"childDirectoriesId[]\" size=\"10\" multiple=\"multiple\">\n";

        $size = count($this->childDirectories);

        for ($i = 0; $i < $size; $i++) {
            // Only empty dirs can be desactivated

            if ((0 == $this->getNbChildDirectories($this->childDirectories[$i]['id'])) && (0 == $this->getNbChildPages($this->childDirectories[$i]['id']))) {
                echo '        <option value="' . $this->childDirectories[$i]['id'] . '">' . $this->childDirectories[$i]['name'] . "</option>\n";
            }
        }

        echo "        </select>\n"
             . '        <input type="hidden" name="currentDirectoryId" value="'
             . $this->currentDirectory['id']
             . "\"><br><br>\n"
             . '        <input type="submit" value="'
             . _IC_DESACTIVATEDIRECTORIES
             . "\">\n"
             . "      </form>\n"
             . "    </td>\n"
             . "  </tr>\n"
             . "</table>\n";
    }

    /**
     * @Purpose : activates specified directories in the DB.
     */
    public function activate()
    {
        global $xoopsDB, $xoopsConfig, $tools, $_POST;

        if (isset($_POST['childDirectoriesUrl'])) {
            // Recuperation of current directory url

            $this->currentDirectory['id'] = $_POST['currentDirectoryId'];

            // Recuperation of child directories

            $size = count($_POST['childDirectoriesUrl']);

            for ($i = 0; $i < $size; $i++) {
                $this->childDirectories[$i] = ['id' => '', 'pid' => $this->currentDirectory['id'], 'name' => $this->getDirName($_POST['childDirectoriesUrl'][$i]), 'url' => $_POST['childDirectoriesUrl'][$i], 'link' => ''];
            }

            // Retrieves xoops groups id

            $result = $xoopsDB->queryF('SELECT `groupid` FROM `' . $xoopsDB->prefix('groups') . '`');

            while (false !== ($item = $xoopsDB->fetchArray($result))) {
                $groupId[] = $item['groupid'];
            }

            // Makes the page access for all the groups

            $childDirectoryAccess = implode(':', $groupId);

            // Saves the directories in the DB

            echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_DIRECTORIESACTIVATION . "</th></tr>\n";

            for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
                if (true === $xoopsDB->queryF(
                    'INSERT INTO `'
                        . $xoopsDB->prefix()
                        . '_'
                        . $this->parentModule
                        . "_directories` (`pid`, `name`, `url`, `access`) VALUES ('"
                        . $this->childDirectories[$i]['pid']
                        . "', '"
                        . $this->childDirectories[$i]['name']
                        . "', '"
                        . $this->childDirectories[$i]['url']
                        . "', '"
                        . $childDirectoryAccess
                        . "')"
                )) {
                    echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_DIRECTORY . '</b>&nbsp;<i>' . $this->childDirectories[$i]['url'] . '</i>&nbsp;<b>' . _IC_ACTIVATED . "</b></td></tr>\n";
                } else {
                    echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_DIRECTORIESACTIVATION . '&nbsp;:</b>&nbsp;<i>' . $this->childDirectories[$i]['url'] . "</i></td></tr>\n";
                }
            }

            echo "</table>\n";
        } else {
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTDIRECTORIES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTODIRECTORYADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : desactivates specified directories in DB.
     */
    public function desactivate()
    {
        global $xoopsConfig, $xoopsDB;

        // Recuperation of directories url from the DB to display them

        for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
            $result = $xoopsDB->queryF('SELECT `name`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->childDirectories[$i]['id'] . "'");

            $item = $xoopsDB->fetchArray($result);

            $this->childDirectories[$i]['name'] = $item['name'];

            $this->childDirectories[$i]['url'] = $item['url'];
        }

        // Desactivates directories in DB

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_DIRECTORIESDESACTIVATION . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
            if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->childDirectories[$i]['id'] . "'")) {
                echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>'
                     . _IC_DIRECTORY
                     . '</b>&nbsp;<i>'
                     . $this->childDirectories[$i]['url']
                     . '</i>&nbsp;<b>'
                     . _IC_DESACTIVATED
                     . '</b><hr><b>'
                     . _IC_NAME
                     . '&nbsp;:</b>&nbsp;'
                     . $this->childDirectories[$i]['name']
                     . "</td></tr>\n";
            } else {
                echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_DIRECTORYDESACTIVATION . '&nbsp;:</b>&nbsp;<i>' . $this->childDirectories[$i]['url'] . '</i><hr><b>' . _IC_NAME . '&nbsp;:</b>' . $this->childDirectories[$i]['name'] . "</td></tr>\n";
            }
        }

        echo "</table>\n";
    }

    /**
     * @Purpose : directories desactivation management.
     */
    public function desactivation()
    {
        global $_POST, $_GET;

        if (isset($_POST['childDirectoriesId'])) {
            $size = count($_POST['childDirectoriesId']);

            for ($i = 0; $i < $size; $i++) {
                $this->childDirectories[$i]['id'] = $_POST['childDirectoriesId'][$i];
            }

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->desactivate();
                }
            } else {
                echo "<center>\n"
                     . "  <table class=\"outer\" style=\"width : 50%; text-align: center;\">\n"
                     . '    <tr><th>'
                     . _IC_CONFIRM_DIRECTORIESDESACTIVATION
                     . "&nbsp;</font></th></tr>\n"
                     . "    <tr>\n"
                     . "      <td class=\"even\"><br>\n"
                     . "        <form action='admin.php?op=directoryAdmin&amp;cop=desactivation' method='post'>\n"
                     . "          <input type='hidden' name='operation' value='useBDD'>\n";

                for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
                    echo "          <input type='hidden' name='childDirectoriesId[" . $i . "]' value='" . $this->childDirectories[$i]['id'] . "'>\n";
                }

                echo '          <input type="hidden" name="currentDirectoryId" value="'
                     . $_POST['currentDirectoryId']
                     . "\">\n"
                     . "          <input type='submit' value='"
                     . _IC_DESACTIVATE
                     . "'>&nbsp;\n"
                     . "          <input type='button' value='"
                     . _IC_CANCEL
                     . "' onclick='javascript:history.go(-1);'>\n"
                     . "        </form>\n"
                     . "      </td>\n"
                     . "    </tr>\n"
                     . "  </table>\n"
                     . "</center>\n";
            }
        } else {
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTDIRECTORIES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTODIRECTORYADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : configures specified directoires in DB.
     */
    public function configure()
    {
        global $xoopsConfig, $xoopsDB, $_POST;

        $myts = MyTextSanitizer::getInstance();

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_DIRECTORIESCONFIGURED . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
            $childDirectoryName = htmlspecialchars($_POST['childDirectoriesName'][$i], ENT_QUOTES | ENT_HTML5);

            $varName = 'childDirectory' . $this->childDirectories[$i]['id'] . 'Access';

            if (isset($_POST[$varName])) {
                $childDirectoryAccess = implode(':', $_POST[$varName]);
            } else {
                $childDirectoryAccess = 0;
            }

            $varName = 'childDirectory' . $this->childDirectories[$i]['id'] . 'DeleteHomePage';

            $sql = 'UPDATE `'
                   . $xoopsDB->prefix()
                   . '_'
                   . $this->parentModule
                   . "_directories` SET `name`='"
                   . $childDirectoryName
                   . "',"
                   . (isset($_POST[$varName]) ? ' `homePage`=\'0\', ' : ' ')
                   . "`access`='"
                   . $childDirectoryAccess
                   . "', `hidden`='"
                   . (isset($_POST['childDirectory' . $this->childDirectories[$i]['id'] . 'Hidden']) ? 1 : 0)
                   . "'  WHERE `id`='"
                   . $this->childDirectories[$i]['id']
                   . "'";

            if ($xoopsDB->queryF($sql)) {
                echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_DIRECTORY . '</b>&nbsp;<i>' . $this->childDirectories[$i]['url'] . '</i>&nbsp;<b>' . _IC_CONFIGURED . '</b><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $childDirectoryName . "</td></tr>\n";
            } else {
                echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_DIRECTORYCONFIGURATION . '&nbsp;:</b>&nbsp;<i>' . $this->childDirectories[$i]['url'] . '</i><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $childDirectoryName . "</td></tr>\n";
            }
        }

        echo "</table>\n";
    }

    /**
     * @Purpose : directories configuration management.
     */
    public function configuration()
    {
        global $xoopsDB, $xoopsConfig, $tools, $_POST;

        $myts = MyTextSanitizer::getInstance();

        if (isset($_POST['childDirectoriesId'])) {
            $size = count($_POST['childDirectoriesId']);

            for ($i = 0; $i < $size; $i++) {
                $result = $xoopsDB->queryF('SELECT `name`, `url`, `homePage`, `access`, `hidden` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $_POST['childDirectoriesId'][$i] . "'");

                $item = $xoopsDB->fetchArray($result);

                $this->childDirectories[] = ['id' => $_POST['childDirectoriesId'][$i], 'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'url' => $item['url'], 'homePage' => $item['homePage'], 'access' => explode(':', $item['access']), 'hidden' => $item['hidden']];
            }

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->configure();
                }
            } else {
                // Retrieves xoops groups name and id

                $groups = $tools->getGroups();

                // Shows edition form

                echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                     . '  <tr><th colspan="3">'
                     . _IC_DIRECTORIES
                     . "</th></tr>\n"
                     . "  <tr>\n"
                     . '    <td class="head" style="width: 50%;">'
                     . _IC_NAME
                     . "</td>\n"
                     . '    <td class="head" style="width: 20%;">'
                     . _IC_HOMEPAGE
                     . "</td>\n"
                     . '    <td class="head" style="width: 30%;">'
                     . _IC_ACCESSRIGHTS
                     . "</td>\n"
                     . "  <form method=\"post\" action=\"admin.php?op=directoryAdmin&amp;cop=configuration\">\n";

                for ($i = 0, $iMax = count($this->childDirectories); $i < $iMax; $i++) {
                    echo "  <tr><td style=\"height: 5px;\" colspan=\"3\"></tr>\n"
                         . "  <tr>\n"
                         . "    <td class=\"even\" style=\"width: 45%;\">\n"
                         . '       <input type="text" name="childDirectoriesName[]" maxlenght="255" size="50" value="'
                         . $this->childDirectories[$i]['name']
                         . "\"><br><br>\n"
                         . '       ('
                         . _IC_URL
                         . '&nbsp;:&nbsp;'
                         . $this->childDirectories[$i]['url']
                         . ")\n"
                         . '       <input type="hidden" name="childDirectoriesId[]" value="'
                         . $this->childDirectories[$i]['id']
                         . "\">\n"
                         . "    </td>\n"
                         . "    <td class=\"even\" style=\"width: 10%;\">\n";

                    if (0 != $this->childDirectories[$i]['homePage']) {
                        $result = $xoopsDB->query('SELECT `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->childDirectories[$i]['homePage'] . "'");

                        $item = $xoopsDB->fetchArray($result);

                        echo '      ' . $item['name'] . "<br><br>\n" . '      <input type="checkbox" name="childDirectory' . $this->childDirectories[$i]['id'] . 'DeleteHomePage[]" value="1">&nbsp;' . _IC_DELETE . "\n";
                    } else {
                        echo '      ' . _IC_NOHOMEPAGE . "\n";
                    }

                    echo "    </td>\n" . "    <td class=\"even\" style=\"width: 35%;\">\n";

                    // Checks access for each group

                    // $j begins at 1 because webmasters group has total access and it can't be rectricted

                    for ($j = 1, $jMax = count($groups); $j < $jMax; $j++) {
                        echo '      <input type="checkbox" name="childDirectory' . $this->childDirectories[$i]['id'] . 'Access[]" value="' . $groups[$j]['id'] . '" ';

                        for ($k = 0, $kMax = count($this->childDirectories[$i]['access']); $k < $kMax; $k++) {
                            // Checks if access match with group

                            if ($this->childDirectories[$i]['access'][$k] == $groups[$j]['id']) {
                                echo 'checked="checked"';
                            }
                        }

                        echo '>&nbsp;' . $groups[$j]['name'] . "<br>\n";
                    }

                    echo "    </td>\n"
                         . "  </tr>\n"
                         . "  <tr>\n"
                         . "    <td colspan=\"3\" class=\"head\">\n"
                         . '      <input type="checkbox" name="childDirectory'
                         . $this->childDirectories[$i]['id']
                         . 'Hidden"'
                         . ((1 == $this->childDirectories[$i]['hidden']) ? ' checked=\"checked\" ' : ' ')
                         . '> '
                         . _IC_DIRECTORYHIDDEN
                         . "\n"
                         . "    </td>\n"
                         . "  </tr>\n";
                }

                echo "  <tr><td style=\"height: 5px;\" colspan=\"3\"></tr>\n"
                     . '  <tr><td class="head" colspan="3"><input type="submit" value="'
                     . _IC_CONFIGURE
                     . "\"></td></tr>\n"
                     . "  <input type=\"hidden\" name=\"operation\" value=\"useBDD\">\n"
                     . '  <input type="hidden" name="currentDirectoryId" value="'
                     . $_POST['currentDirectoryId']
                     . "\">\n"
                     . "  </form>\n"
                     . "</table>\n";
            }
        } else {
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTDIRECTORIES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTODIRECTORYADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : creates an home page link for specified directory. When displaying directory with
     * homepage, the hompage is diplayed instead od directory content.
     */
    public function createHomePage()
    {
        global $xoopsConfig, $xoopsDB, $_GET;

        if (isset($_GET['directory']) && isset($_GET['page'])) {
            echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\"><tr>\n";

            if ($xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` SET `homePage`='" . $_GET['page'] . "' WHERE `id`='" . $_GET['directory'] . "'")) {
                echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_HOMEPAGECREATED . "</td>\n";
            } else {
                echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_HOMEPAGECREATION . "</td>\n";
            }

            echo "</table>\n" . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_GET['directory'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
        }
    }

    /**
     * @Purpose : creates online directory (without correspondance on the server) in the DB.
     */
    public function create()
    {
        global $xoopsDB, $_POST;

        $myts = MyTextSanitizer::getInstance();

        $name = $myts->addSlashes($_POST['directoryName']);

        $access = implode(':', $_POST['directoryAccess']);

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\"><tr>\n";

        if ($xoopsDB->queryF(
            'INSERT INTO `'
            . $xoopsDB->prefix()
            . '_'
            . $this->parentModule
            . "_directories` (`pid`, `name`, `url`, `access`, `hidden`) VALUES ('"
            . $_POST['currentDirectoryId']
            . "', '"
            . $name
            . "', 'Online', '"
            . $access
            . "', '"
            . (isset($_POST['directoryHidden']) ? 1 : 0)
            . "')"
        )) {
            echo '  <td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_DIRECTORY . '</b>&nbsp;<i>' . $name . '</i>&nbsp;<b>' . _IC_CREATED . "</b></td></tr>\n";
        } else {
            echo '  <td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_DIRECTORYCREATION . '&nbsp;:</b>&nbsp;<i>' . $name . "</i></td></tr>\n";
        }

        echo "</tr></table>\n" . '<br><center><a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTODIRECTORYADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : online directory creation management.
     */
    public function creation()
    {
        global $tools, $_POST, $_GET;

        if (isset($_POST['operation'])) {
            if ('useBDD' == $_POST['operation']) {
                $this->create();
            }
        } else {
            // Retrieves xoops groups name and id

            $groups = $tools->getGroups();

            // Shows edition form

            echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                 . '  <tr><th colspan="2">'
                 . _IC_DIRECTORYCREATION
                 . "</th></tr>\n"
                 . "  <tr>\n"
                 . '    <td class="head" style="width: 60%;">'
                 . _IC_NAME
                 . "</td>\n"
                 . '    <td class="head" style="width: 40%;">'
                 . _IC_ACCESSRIGHTS
                 . "</td>\n"
                 . "  <form name=\"creationForm\" method=\"post\" action=\"admin.php?op=directoryAdmin&amp;cop=creation\" onSubmit=\"javascript:document.creationForm.submition.disabled=true;\">\n"
                 . "  <tr>\n"
                 . "    <td class=\"even\" style=\"width: 60%;\">\n"
                 . "       <input type=\"text\" name=\"directoryName\" maxlenght=\"255\" size=\"50\"><br><br>\n"
                 . '       ('
                 . _IC_URL
                 . '&nbsp;:&nbsp;'
                 . _IC_ONLINE
                 . ")\n"
                 . "    </td>\n"
                 . "    <td class=\"even\" style=\"width: 40%;\">\n";

            for ($j = 1, $jMax = count($groups); $j < $jMax; $j++) {
                echo '      <input type="checkbox" name="directoryAccess[]" value="' . $groups[$j]['id'] . '" checked="checked">&nbsp;' . $groups[$j]['name'] . "<br>\n";
            }

            echo "    </td>\n"
                 . "  </tr>\n"
                 . '  <tr><td class="odd" colspan="2"><input type="checkbox" name="directoryHidden">&nbsp;'
                 . _IC_DIRECTORYHIDDEN
                 . "</td></tr>\n"
                 . '  <tr><td class="head" colspan="2"><input name="submition" type="submit" value="'
                 . _IC_CREATE
                 . "\"></td></tr>\n"
                 . "  <input type=\"hidden\" name=\"operation\" value=\"useBDD\">\n"
                 . '  <input type="hidden" name="currentDirectoryId" value="'
                 . $_GET['currentDir']
                 . "\">\n"
                 . "  </form>\n"
                 . "</table>\n"
                 . '<br><center><a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir='
                 . $_GET['currentDir']
                 . '">'
                 . _IC_BACKTODIRECTORYADMIN
                 . "</a></center>\n";
        }
    }
}
