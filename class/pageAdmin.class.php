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
// PAGE ADMIN CLASS
//==========================================================================

class pageAdmin
{
    public $parentModule;

    public $directories;

    public $parentDirectory;

    public $currentDirectory;

    public $childDirectories;

    public $pageContent;

    public $childPages;

    /**
     * @Constructor of this class
     * @Purpose     : class initialisation
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->directories = [];

        $this->parentDirectory = [];

        $this->currentDirectory = ['id' => 0, 'pid' => -1, 'name' => 'inPages', 'url' => 'inPages'];

        $this->getChildDirectories = [];

        $this->pageContent = '';

        $this->childPages = [];
    }

    /**
     * @Purpose : builds page access from $_POST['pageAccess'] and returns it
     * @param mixed $option
     * @return int|string
     * @return int|string
     */
    public function getAccess($option = 'fromPostData')
    {
        global $_POST, $tools;

        switch ($option) {
            case 'fromPostData':
                // Configuration of the page access rights
                // Puts the value to 0 is no acces is passed
                if (isset($_POST['pageAccess'])) {
                    $access = implode(':', $_POST['pageAccess']);
                } else {
                    $access = 0;
                }
                break;
            case 'forAllGroups':
                $access = implode(':', $tools->getGroups('id'));
                break;
        }

        return $access;
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
     * @Purpose : returns the name of the page without the extension
     * @param mixed $url
     * @return string|string[]
     * @return string|string[]
     */
    public function getPageName($url)
    {
        $result = explode('.', basename($url));

        $name = $result[0];

        $size = count($result);

        if ($size > 2) {
            for ($i = 1; $i < ($size - 1); $i++) {
                $name .= '.' . $result[$i];
            }
        }

        $name = str_replace('_', ' ', $name);

        return $name;
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
            $this->directories[] = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $item['id'])];

            $this->getDirectories($item['pid']);
        } else {
            $this->directories[] = ['name' => 'inPages', 'link' => 'admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=0'];

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

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `pid`='" . $this->currentDirectory['id'] . "' ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $this->getChildDirectories[] = ['id' => $item['id'], 'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'url' => $item['url'], 'link' => ('admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $item['id'])];
        }
    }

    /**
     * @Purpose : gets all the child pages of $this->currentDirectory and puts it in $this->childPages
     */
    public function getChildPages()
    {
        global $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `url`, `directory`, `lastUpdate` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `directory`='" . $this->currentDirectory['id'] . "' ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $this->childPages[] = ['id' => $item['id'], 'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'url' => $item['url'], 'directory' => $item['directory'], 'lastUpdate' => $item['lastUpdate']];
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
     * @Purpose : displays the main page of "Pages administration".
     */
    public function display()
    {
        global $xoopsDB, $xoopsConfig, $_GET, $tools;

        $myts = MyTextSanitizer::getInstance();

        if (isset($_GET['currentDir'])) {
            $this->currentDirectory['id'] = $_GET['currentDir'];
        }

        // Shows chosen directories

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

                $this->parentDirectory = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $this->currentDirectory['pid'])];
            } else {
                $this->parentDirectory = ['name' => 'inPages', 'link' => 'admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=0'];
            }
        }

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n";

        // Displays the name of current directory

        echo '  <tr><th colspan="3">' . _IC_CURRENTDIRECTORY . '&nbsp;:&nbsp;' . $this->currentDirectory['name'] . "</th></tr>\n";

        // Displays the parent directory link if exists

        if (isset($this->parentDirectory['name'])) {
            echo '  <tr><td class="head" colspan="3"><img src="images/back.gif">&nbsp;<a href="' . $this->parentDirectory['link'] . '">' . $this->parentDirectory['name'] . "</a></td></tr>\n";
        }

        // Recuperation and displaying of child directories

        $this->getChildDirectories();

        for ($i = 0, $iMax = count($this->getChildDirectories); $i < $iMax; $i++) {
            echo "  <tr>\n"
                 . "    <td class=\"even\" style=\"width: 60%;\">\n"
                 . '      <img src="images/directory.gif">&nbsp;<a href="'
                 . $this->getChildDirectories[$i]['link']
                 . '" title="'
                 . $this->getChildDirectories[$i]['url']
                 . '">'
                 . $this->getChildDirectories[$i]['name']
                 . "</a>\n"
                 . "    </td>\n"
                 . '    <td class="odd" style="width: 40%;" colspan="2"><b>'
                 . _IC_CONTAINS
                 . "&nbsp;\n"
                 . '      '
                 . $this->getNbChildDirectories($this->getChildDirectories[$i]['id'])
                 . '&nbsp;'
                 . _IC_DIRECTORIES
                 . '&nbsp;'
                 . _IC_AND
                 . "&nbsp;\n"
                 . '      '
                 . $this->getNbChildPages($this->getChildDirectories[$i]['id'])
                 . '&nbsp;'
                 . _IC_PAGES
                 . "</b>\n"
                 . '      <br>'
                 . _IC_REALNAME
                 . '&nbsp;:&nbsp;'
                 . $this->getDirName($this->getChildDirectories[$i]['url'])
                 . "\n"
                 . "    </td>\n"
                 . "  </tr>\n";
        }

        echo "</table><br>\n";

        // Recuperation and displaying of child pages

        $this->getChildPages();

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="3">' . _IC_PAGES . "</th></tr>\n" . "  <tr>\n";

        if (isset($_POST['childPageNumber']) && (-1 != $_POST['childPageNumber'])) {
            echo "    <td class=\"even\" style=\"width: 75%;\">\n";

            if ($tools->is_php($this->childPages[$_POST['childPageNumber']]['url'])) {
                echo '<font style="color: #FF0000; font-weight: bold;">[PHP]</font>&nbsp;' . $this->childPages[$_POST['childPageNumber']]['name'] . "\n";
            } else {
                echo '<font style="color: #0000FF; font-weight: bold;">[HTML]</font>&nbsp;<a href="admin.php?op=pageAdmin&amp;cop=download&amp;page='
                     . $this->childPages[$_POST['childPageNumber']]['id']
                     . '" target="_blank" title="'
                     . _IC_DOWNLOADPAGE
                     . '">'
                     . $this->childPages[$_POST['childPageNumber']]['name']
                     . "</a>\n";
            }

            echo "    </td>\n" . "    <td style=\"width: 25%;\">\n" . "      <table class=\"outer\" style=\"width: 100%; text-align: left;\">\n";

            if (!$tools->is_php($this->childPages[$_POST['childPageNumber']]['url'])) {
                echo '        <tr><td class="head"><a href="admin.php?op=pageAdmin&amp;cop=modification&amp;id=' . $this->childPages[$_POST['childPageNumber']]['id'] . '">' . _IC_MODIFY . "</td></tr>\n";
            }

            echo '        <tr><td class="odd"><a href="admin.php?op=shortcutAdmin&amp;cop=creation&amp;page=' . $this->childPages[$_POST['childPageNumber']]['id'] . '">' . _IC_SHORTCUT . "</a></td></tr>\n";

            if (0 != $this->currentDirectory['id']) {
                echo '        <tr><td class="head"><a href="admin.php?op=directoryAdmin&amp;cop=createHomePage&amp;directory=' . $this->currentDirectory['id'] . '&amp;page=' . $this->childPages[$_POST['childPageNumber']]['id'] . '">' . _IC_HOMEPAGE . "</a></td></tr>\n";
            }

            if (!$tools->is_php($this->childPages[$_POST['childPageNumber']]['url'])) {
                echo '        <tr><td class="odd">';

                if ('Online' == $this->childPages[$_POST['childPageNumber']]['url']) {
                    echo _IC_NOPAGEUPDATE;
                } else {
                    echo '<a href="admin.php?op=pageAdmin&amp;cop=update&amp;page=' . $this->childPages[$_POST['childPageNumber']]['id'] . '">' . _IC_UPDATETHEPAGE . '</a>';
                }

                echo "</td></tr>\n";
            }

            echo '        <tr><td class="head"><b>' . _IC_LASTUPDATE . '</b>&nbsp;:&nbsp;<br><i>' . formatTimestamp($this->childPages[$i]['lastUpdate']) . "</i></td></tr>\n" . "      </table>\n" . "    </td>\n";
        } else {
            echo '    <td class="even" style="width: 98%;"><br><b>' . _IC_CHOOSEPAGE . "</b><br><br></td>\n" . "    <td class=\"odd\"style=\"width: 2%;\"><font style=\"color: #FF0000; font-weight: bold;\">[X]</font></td>\n";
        }

        echo "  </tr>\n"
             . "  <tr>\n"
             . "    <td class=\"odd\" colspan=\"3\"><br>\n"
             . '      <form method="post" action="admin.php?op=pageAdmin&amp;cop=display&currentDir='
             . $this->currentDirectory['id']
             . "\">\n"
             . "        <select name=\"childPageNumber\">\n"
             . '          <option value="-1">'
             . _IC_SELECTPAGE
             . "</option>\n"
             . "          <option value=\"-1\">--------------------------------------------------</option>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            echo '<option value="' . $i . '">' . $this->childPages[$i]['name'] . "</option>\n";
        }

        echo "        </select>\n" . '        <input type="submit" value="' . _IC_DISPLAYPAGE . "\">\n" . "      </form>\n" . "    </td>\n" . "  </tr>\n" . "</table><br>\n";

        // Actions part

        echo '<font style="color: #0000FF; font-weight: bold;">[X]</font>&nbsp;<a href="admin.php?op=directoryAdmin&amp;cop=display&amp;currentDir='
             . $this->currentDirectory['id']
             . '">'
             . _IC_GOTODIRECTORYADMIN
             . "</a><br><br>\n"
             . '<font style="color: #FF0000; font-weight: bold;">[X]</font>&nbsp;<a href="admin.php?op=pageAdmin&amp;cop=creation&amp;currentDir='
             . $this->currentDirectory['id']
             . '">'
             . _IC_CREATEPAGEINDIRECTORY
             . '&nbsp;<i>'
             . $this->currentDirectory['name']
             . "</i></a><br>\n";

        echo $this->upload('directCreation');

        echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\">\n" . '  <tr><th colspan="3">' . _IC_ACTIONS . "</th></tr>\n" . "  <tr>\n" . "    <td class=\"head\" style=\"width : 33%;\">\n";

        // Only physical dir could contain activable pages !

        if ('Online' != $this->currentDirectory['url']) {
            // Initialisation of the explorer

            $explorer = opendir($this->currentDirectory['url']);

            echo "      <img src=\"images/desactivated.gif\"><br><br>\n"
                 . '      '
                 . _IC_PAGESNOTACTIVATED
                 . "&nbsp;:<br><br>\n"
                 . "      <form name=\"activationForm\" method=\"post\" action=\"admin.php?op=pageAdmin&amp;cop=activate\" onSubmit=\"javascript:document.activationForm.submition.disabled=true;\">\n"
                 . "        <select name=\"childPagesUrl[]\" size=\"10\" multiple=\"multiple\">\n";

            // Recuperation of non activated files in the activation list

            while ($item = readdir($explorer)) {
                $childPageUrl = $this->currentDirectory['url'] . '/' . $item;

                if (('.' != $item) && ('..' != $item) && ($tools->is_html($childPageUrl) || $tools->is_php($childPageUrl))) {
                    // Checks if the page is already activated (in the DB)

                    $size = count($this->childPages);

                    if ($size > 0) {
                        $i = 0;

                        do {
                            $isActivated = ($childPageUrl == $this->childPages[$i]['url']);

                            $i++;
                        } while (($i < $size) && (!$isActivated));
                    } else {
                        $isActivated = false;
                    }

                    // The file isn't activated, we put it in the activation list

                    if (!$isActivated) {
                        echo '          <option value="' . $childPageUrl . '">' . $this->getPageName($childPageUrl) . "</option>\n";
                    }
                }
            }

            echo "        </select>\n" . '        <input type="hidden" name="currentDirectoryId" value="' . $this->currentDirectory['id'] . "\"><br><br>\n" . '        <input name="submition" type="submit" value="' . _IC_ACTIVATEPAGES . "\">\n" . "      </form>\n";
        } else {
            echo _IC_NOACTIVATION;
        }

        echo "    </td>\n"
             . "    <td class=\"odd\" style=\"width : 33%;\">\n"
             . "      <img src=\"images/edit.gif\"><br><br>\n"
             . '      <b>'
             . _IC_CONFIGURABLEPAGES
             . "&nbsp;:</b><br><br>\n"
             . "      <form method=\"post\" action=\"admin.php?op=pageAdmin&amp;cop=configuration\">\n"
             . "        <select name=\"childPagesId[]\" size=\"10\" multiple=\"multiple\">\n";

        $size = count($this->childPages);

        for ($i = 0; $i < $size; $i++) {
            echo '        <option value="' . $this->childPages[$i]['id'] . '">' . $this->childPages[$i]['name'] . "</option>\n";
        }

        echo "        </select>\n"
             . '        <input type="hidden" name="currentDirectoryId" value="'
             . $this->currentDirectory['id']
             . "\"><br><br>\n"
             . '        <input type="submit" value="'
             . _IC_CONFIGUREPAGES
             . "\">\n"
             . "      </form>\n"
             . "    </td>\n"
             . "    <td class=\"head\" style=\"width : 33%;\">\n"
             . "      <img src=\"images/desactivate.gif\"><br><br>\n"
             . '      '
             . _IC_DESACTIVABLEPAGES
             . "&nbsp;:<br><br>\n"
             . "      <form method=\"post\" action=\"admin.php?op=pageAdmin&amp;cop=desactivation\">\n"
             . "        <select name=\"childPagesId[]\" size=\"10\" multiple=\"multiple\">\n";

        $size = count($this->childPages);

        for ($i = 0; $i < $size; $i++) {
            echo '        <option value="' . $this->childPages[$i]['id'] . '">' . $this->childPages[$i]['name'] . "</option>\n";
        }

        echo "        </select>\n"
             . '        <input type="hidden" name="currentDirectoryId" value="'
             . $this->currentDirectory['id']
             . "\"><br><br>\n"
             . '        <input type="submit" value="'
             . _IC_DESACTIVATEPAGES
             . "\">\n"
             . "      </form>\n"
             . "    </td>\n"
             . "  </tr>\n"
             . "</table>\n";
    }

    /**
     * @Purpose : activates a list of child pages specified in $_POST
     */
    public function activate()
    {
        global $xoopsDB, $xoopsConfig, $xoopsUser, $_POST;

        if (isset($_POST['childPagesUrl'])) {
            // Recuperation of the current directory url

            $this->currentDirectory['id'] = $_POST['currentDirectoryId'];

            // Recuperation of the the child files

            for ($i = 0, $iMax = count($_POST['childPagesUrl']); $i < $iMax; $i++) {
                $this->childPages[$i] = ['id' => '', 'name' => $this->getPageName($_POST['childPagesUrl'][$i]), 'url' => $_POST['childPagesUrl'][$i], 'directory' => $this->currentDirectory['id']];
            }

            // Makes the page access for all the groups

            $childPageAccess = $this->getAccess('forAllGroups');

            // Save the files in the BDD

            echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_PAGESACTIVATION . "</th></tr>\n";

            for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
                if (true === $xoopsDB->queryF(
                    'INSERT INTO `'
                        . $xoopsDB->prefix()
                        . '_'
                        . $this->parentModule
                        . "_pages` (`name`, `url`, `directory`, `access`, `lastUpdate`, `submitter`) VALUES ('"
                        . $this->childPages[$i]['name']
                        . "', '"
                        . $this->childPages[$i]['url']
                        . "', '"
                        . $this->childPages[$i]['directory']
                        . "', '"
                        . $childPageAccess
                        . "', '"
                        . filemtime($this->childPages[$i]['url'])
                        . "', '"
                        . $xoopsUser->uid()
                        . "')"
                )) {
                    echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_PAGE . '</b>&nbsp;<i>' . $this->childPages[$i]['url'] . '</i>&nbsp;<b>' . _IC_ACTIVATED . "</b></td></tr>\n";
                } else {
                    echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_PAGEACTIVATION . '&nbsp;:</b>&nbsp;<i>' . $this->childPages[$i]['url'] . "</i></td></tr>\n";
                }
            }

            echo "</table>\n";
        } else {
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTPAGES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : descativates a list of child pages specified in HTTP_POST_VARS.
     */
    public function desactivate()
    {
        global $xoopsConfig, $xoopsDB, $xoopsModule;

        // Recuperation of the page url from the DB to display them

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            $result = $xoopsDB->queryF('SELECT `name`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->childPages[$i]['id'] . "'");

            $item = $xoopsDB->fetchArray($result);

            $this->childPages[$i]['name'] = $item['name'];

            $this->childPages[$i]['url'] = $item['url'];
        }

        // Desactivates the page in the DB

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_PAGESDESACTIVATION . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->childPages[$i]['id'] . "'")) {
                echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_PAGE . '</b>&nbsp;<i>' . $this->childPages[$i]['url'] . '</i>&nbsp;<b>' . _IC_DESACTIVATED . '</b><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $this->childPages[$i]['name'] . "</td></tr>\n";
            } else {
                echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_PAGEDESACTIVATION . '&nbsp;:</b>&nbsp;<i>' . $this->childPages[$i]['url'] . '</i><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $this->childPages[$i]['name'] . "</td></tr>\n";
            }
        }

        echo "</table><br><br>\n";

        // Deletes related shortcuts in DB

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th>' . _IC_RELATEDSHORTCUTSDELETED . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            if ($GLOBALS['xoopsDB']->getRowsNum($xoopsDB->queryF('SELECT `id` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` WHERE `page`='" . $this->childPages[$i]['id'] . "'")) > 0) {
                if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` WHERE `page`='" . $this->childPages[$i]['id'] . "'")) {
                    echo '  <tr><td class="even">' . _IC_SHORTCUTTO . '&nbsp;<b><i>' . $this->childPages[$i]['url'] . '</i></b>&nbsp;' . _IC_DELETED . "</td></tr>\n";
                } else {
                    echo '  <tr><td class="even">' . _IC_ERROR_SHORTCUTDELETING . '&nbsp;:&nbsp;<b><i>' . $this->childPages[$i]['url'] . "</i></b></td></tr>\n";
                }
            }
        }

        echo "</table><br><br>\n";

        // Deletes related comments in DB

        $modId = $xoopsModule->mid();

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th>' . _IC_RELATEDCOMMENTSDELETED . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            if ($GLOBALS['xoopsDB']->getRowsNum($xoopsDB->queryF('SELECT `com_id` FROM `' . $xoopsDB->prefix() . "_xoopscomments` WHERE (`com_itemid`='" . $this->childPages[$i]['id'] . "' AND `com_modid`='" . $modId . "')")) > 0) {
                if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . "_xoopscomments` WHERE (`com_itemid`='" . $this->childPages[$i]['id'] . "' AND `com_modid`='" . $modId . "')")) {
                    echo '  <tr><td class="even">' . _IC_COMMENTSOF . '&nbsp;<b><i>' . $this->childPages[$i]['url'] . '</i></b>&nbsp;' . _IC_DELETED . "</td></tr>\n";
                } else {
                    echo '  <tr><td class="even">' . _IC_ERROR_COMMENTSDELETING . '&nbsp;:&nbsp;<b><i>' . $this->childPages[$i]['url'] . "</i></b></td></tr>\n";
                }
            }
        }

        echo "</table><br><br>\n";

        // Deletes related votes in DB

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th>' . _IC_RELATEDVOTESDELETED . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            if ($GLOBALS['xoopsDB']->getRowsNum($xoopsDB->queryF('SELECT `page` FROM `' . $xoopsDB->prefix() . "_icontent_votedata` WHERE `page`='" . $this->childPages[$i]['id'] . "'")) > 0) {
                if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . "_icontent_votedata` WHERE `page`='" . $this->childPages[$i]['id'] . "'")) {
                    echo '  <tr><td class="even">' . _IC_VOTESOF . '&nbsp;<b><i>' . $this->childPages[$i]['url'] . '</i></b>&nbsp;' . _IC_DELETED . "</td></tr>\n";
                } else {
                    echo '  <tr><td class="even">' . _IC_ERROR_VOTESDELETING . '&nbsp;:&nbsp;<b><i>' . $this->childPages[$i]['url'] . "</i></b></td></tr>\n";
                }
            }
        }

        echo "</table>\n";
    }

    /**
     * @Purpose : page descativation management.
     */
    public function desactivation()
    {
        global $_POST, $_GET;

        if (isset($_POST['childPagesId'])) {
            for ($i = 0, $iMax = count($_POST['childPagesId']); $i < $iMax; $i++) {
                $this->childPages[$i]['id'] = $_POST['childPagesId'][$i];
            }

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->desactivate();
                }
            } else {
                echo "<center>\n"
                     . "  <table class=\"outer\" style=\"width : 50%; text-align: center;\">\n"
                     . '    <tr><th>'
                     . _IC_CONFIRM_PAGESDESACTIVATION
                     . "&nbsp;</font></th></tr>\n"
                     . "    <tr>\n"
                     . "      <td class=\"even\"><br>\n"
                     . "        <form action='admin.php?op=pageAdmin&amp;cop=desactivation' method='post'>\n"
                     . "          <input type='hidden' name='operation' value='useBDD'>\n";

                for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
                    echo "          <input type='hidden' name='childPagesId[" . $i . "]' value='" . $this->childPages[$i]['id'] . "'>\n";
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
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTPAGES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : configuration for a list of pages.
     */
    public function configure()
    {
        global $xoopsConfig, $xoopsDB, $_POST;

        $myts = MyTextSanitizer::getInstance();

        echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n" . '  <tr><th colspan="2">' . _IC_PAGESCONFIGURED . "</th></tr>\n";

        for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
            $varName = 'childPage' . $this->childPages[$i]['id'] . 'Access';

            // Retrieves the page access, puts no access (0) if no acces right is passed

            if (isset($_POST[$varName])) {
                $childPageAccess = implode(':', $_POST[$varName]);
            } else {
                $childPageAccess = 0;
            }

            $childPageName = $myts->addSlashes($_POST['childPagesName'][$i]);

            $childPageHidden = isset($_POST['childPage' . $this->childPages[$i]['id'] . 'Hidden']) ? 1 : 0;

            $childPageCommentsEnabled = isset($_POST['childPage' . $this->childPages[$i]['id'] . 'CommentsEnabled']) ? 1 : 0;

            $childPageRatingEnabled = isset($_POST['childPage' . $this->childPages[$i]['id'] . 'RatingEnabled']) ? 1 : 0;

            if ($xoopsDB->queryF(
                'UPDATE `'
                . $xoopsDB->prefix()
                . '_'
                . $this->parentModule
                . "_pages` SET `name`='"
                . $childPageName
                . "', `access`='"
                . $childPageAccess
                . "', `hidden`='"
                . $childPageHidden
                . "', `commentsEnabled`='"
                . $childPageCommentsEnabled
                . "', `ratingEnabled`='"
                . $childPageRatingEnabled
                . "'  WHERE `id`='"
                . $this->childPages[$i]['id']
                . "'"
            )) {
                echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even"><b>' . _IC_PAGE . '</b>&nbsp;<i>' . $this->childPages[$i]['url'] . '</i>&nbsp;<b>' . _IC_CONFIGURED . '</b><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $childPageName . "</td></tr>\n";
            } else {
                echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td class="even"><b>' . _IC_ERROR_PAGECONFIGURATION . '&nbsp;:</b>&nbsp;<i>' . $this->childPages[$i]['url'] . '</i><hr><b>' . _IC_NAME . '&nbsp;:</b>&nbsp;' . $childPageName . "</td></tr>\n";
            }
        }

        echo "</table>\n";
    }

    /**
     * @Purpose : Pages configuration management.
     */
    public function configuration()
    {
        global $xoopsDB, $xoopsConfig, $tools, $_POST;

        $myts = MyTextSanitizer::getInstance();

        if (isset($_POST['childPagesId'])) {
            for ($i = 0, $iMax = count($_POST['childPagesId']); $i < $iMax; $i++) {
                $result = $xoopsDB->queryF('SELECT `id`, `name`, `url`, `access`, `hidden`, `commentsEnabled`, `ratingEnabled` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $_POST['childPagesId'][$i] . "'");

                $item = $xoopsDB->fetchArray($result);

                $this->childPages[] = [
                    'id' => $_POST['childPagesId'][$i],
                    'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5),
                    'url' => $item['url'],
                    'access' => explode(':', $item['access']),
                    'hidden' => $item['hidden'],
                    'commentsEnabled' => $item['commentsEnabled'],
                    'ratingEnabled' => $item['ratingEnabled'],
                ];
            }

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->configure();
                }
            } else {
                // Loads the xoops groups into $groups

                $groups = $tools->getGroups();

                // Shows the edition form

                echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                     . '  <tr><th colspan="2">'
                     . _IC_PAGES
                     . "</th></tr>\n"
                     . "  <tr>\n"
                     . '    <td class="head" style="width: 70%;">'
                     . _IC_NAME
                     . "</td>\n"
                     . '    <td class="head" style="width: 30%;">'
                     . _IC_ACCESSRIGHTS
                     . "</td>\n"
                     . "  <form method=\"post\" action=\"admin.php?op=pageAdmin&amp;cop=configuration\">\n";

                for ($i = 0, $iMax = count($this->childPages); $i < $iMax; $i++) {
                    echo "  <tr><td style=\"height: 5px;\" colspan=\"3\"></tr>\n"
                         . "  <tr>\n"
                         . "    <td class=\"even\" style=\"width: 45%;\">\n"
                         . '       <input type="text" name="childPagesName[]" maxlenght="255" size="50" value="'
                         . $this->childPages[$i]['name']
                         . "\"><br><br>\n"
                         . '       ('
                         . _IC_URL
                         . '&nbsp;:&nbsp;'
                         . $this->childPages[$i]['url']
                         . ")\n"
                         . '       <input type="hidden" name="childPagesId[]" value="'
                         . $this->childPages[$i]['id']
                         . "\">\n"
                         . "    </td>\n"
                         . "    <td class=\"even\" style=\"width: 45%;\">\n";

                    // Checks access for each group

                    // $j begins at 1 because webmasters group has total access and it can't be rectricted

                    for ($j = 1, $jMax = count($groups); $j < $jMax; $j++) {
                        echo '      <input type="checkbox" name="childPage' . $this->childPages[$i]['id'] . 'Access[]" value="' . $groups[$j]['id'] . '" ';

                        for ($k = 0, $kMax = count($this->childPages[$i]['access']); $k < $kMax; $k++) {
                            if ($this->childPages[$i]['access'][$k] == $groups[$j]['id']) {
                                echo 'checked="checked"';
                            }
                        }

                        echo '>&nbsp;' . $groups[$j]['name'] . "<br>\n";
                    }

                    echo "    </td>\n"
                         . "  </tr>\n"
                         . "  <tr>\n"
                         . "    <td colspan=\"2\" class=\"head\">\n"
                         . '      <input type="checkbox" name="childPage'
                         . $this->childPages[$i]['id']
                         . 'Hidden"'
                         . ((1 == $this->childPages[$i]['hidden']) ? ' checked=\"checked\" ' : ' ')
                         . '> '
                         . _IC_PAGEHIDDEN
                         . "&nbsp;&nbsp;&nbsp;&nbsp;\n"
                         . '      <input type="checkbox" name="childPage'
                         . $this->childPages[$i]['id']
                         . 'CommentsEnabled"'
                         . ((1 == $this->childPages[$i]['commentsEnabled']) ? ' checked=\"checked\" ' : ' ')
                         . '> '
                         . _IC_COMMENTSENABLED
                         . "&nbsp;&nbsp;&nbsp;&nbsp;\n"
                         . '      <input type="checkbox" name="childPage'
                         . $this->childPages[$i]['id']
                         . 'RatingEnabled"'
                         . ((1 == $this->childPages[$i]['ratingEnabled']) ? ' checked=\"checked\" ' : ' ')
                         . '> '
                         . _IC_RATINGENABLED
                         . "\n"
                         . "    </td>\n"
                         . "  </tr>\n";
                }

                echo "  <tr><td style=\"height: 5px;\" colspan=\"2\"></tr>\n"
                     . '  <tr><td class="head" colspan="2"><input type="submit" value="'
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
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SELECTPAGES . "</td></tr></table>\n";
        }

        echo '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_POST['currentDirectoryId'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : loads and compiles the page content in $this->pageContent.
     * @param mixed $pageId
     * @return bool
     * @return bool
     */
    public function loadPageContent($pageId)
    {
        global $xoopsConfig, $xoopsDB, $pageCompiler;

        $myts = MyTextSanitizer::getInstance();

        $isLoaded = false;

        $result = $xoopsDB->query('SELECT * FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $pageId . "'");

        if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
            $item = $xoopsDB->fetchArray($result);

            if ('' == $item['content']) {
                $pageCompiler->htmlCompilation($pageId);

                $this->pageContent = $myts->displayTarea($pageCompiler->content, 1, 1, 1, 1, 0);

                $isLoaded = true;
            } else {
                // Since version 4.1 the xoops features like smileys and xoopscode are enabled

                $this->pageContent = $myts->displayTarea($item['content'], 1, 1, 1, 1, 0);

                $isLoaded = true;
            }
        }

        return $isLoaded;
    }

    /**
     * @Purpose : modifys a page and lastupdate specified by the id $pageId.
     * @param mixed $pageId
     */
    public function modify($pageId)
    {
        global $xoopsConfig, $xoopsDB, $pageCompiler, $_POST, $_GET;

        echo '<br><br><table class="outer" style="width: 100%; text-align: center;"><tr>';

        if ($pageCompiler->compile($_GET['id'], stripslashes($_POST['pageContent']))) {
            echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_PAGEMODIFIED . "</td>\n";
        } else {
            echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_PAGEMODIFICATION . "</td>\n";
        }

        echo "</table>\n";
    }

    /**
     * @Purpose : page modification management with wysiwyg editor.
     * @param mixed $pageId
     */
    public function modification($pageId = 0)
    {
        global $xoopsConfig, $xoopsDB, $_GET, $_POST, $tools;

        if ((true === isset($_GET['id'])) || (0 != $pageId)) {
            // Allows to pass parmeter since the url or the function call

            if (true === isset($_GET['id'])) {
                $pageId = $_GET['id'];
            }

            $result = $xoopsDB->query('SELECT `name`, `url`, `directory` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $pageId . "'");

            [$pageName, $pageUrl, $pageDirectory] = $xoopsDB->fetchRow($result);

            // PHP pages can't be edited !

            if (!$tools->is_php($pageUrl)) {
                echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                     . '  <tr><th>'
                     . _IC_PAGEMODIFICATION
                     . "</th></tr>\n"
                     . '  <tr><td class="even"><b>'
                     . _IC_NAME
                     . '&nbsp;:</b>&nbsp;'
                     . $pageName
                     . "</td></tr>\n"
                     . '  <tr><td class="even"><b>'
                     . _IC_URL
                     . '&nbsp;:</b>&nbsp;'
                     . $pageUrl
                     . "</td></tr>\n"
                     . "</table>\n";

                if (true === isset($_POST['operation'])) {
                    if ('useBDD' == $_POST['operation']) {
                        $this->modify($pageId);
                    }
                } else {
                    if ($this->loadPageContent($pageId)) {
                        $wysiwyg = new SPAW_Wysiwyg('pageContent', $this->pageContent, $xoopsConfig['language'], 'default', 'default', '100%', '600px');

                        echo '<form name="wysiwygForm" method="post" action="admin.php?op=pageAdmin&amp;cop=modification&amp;id=' . $pageId . "\" onSubmit=\"javascript:document.wysiwygForm.submition.disabled=true;\">\n";

                        $wysiwyg->show();

                        echo "<input type=\"hidden\" name=\"operation\" value=\"useBDD\">\n" . '<input type="submit" name="submition" value="' . _IC_MODIFY . "\">\n" . '<input type="button" value="' . _IC_CANCEL . "\" onclick=\"javascript:history.go(-1);\">\n" . "</form>\n";
                    } else {
                        echo "<br><br>\n" . "<table class=\"outer\" style=\"width: 100%; text-align: center;\">\n" . '  <tr><th>' . _IC_ERROR_PAGELOADING . "</th></tr>\n" . "</table>\n";
                    }
                }
            }
        }

        echo '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $pageDirectory . '">' . _IC_BACKTOPAGEADMIN . "<center>\n";
    }

    /**
     * @Purpose   : deletes the BDD compilation of the page specified in $_GET['page']. Then the page will be
     * recompiled in the next diplaying.
     * @Important note : online pages can't be updated because there is no original on the server.
     */
    public function update()
    {
        global $xoopsconfig, $xoopsDB, $_GET;

        if (isset($_GET['page'])) {
            $result = $xoopsDB->query('SELECT `directory`, `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $_GET['page'] . "'");

            if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
                [$backDirectory, $url] = $xoopsDB->fetchRow($result);

                echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\">\n";

                if ('Online' != $url) {
                    if ($xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` SET `content`=''  WHERE `id`=" . $_GET['page'] . '')) {
                        echo '  <tr><td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_PAGEUPDATED . "</td></tr>\n";
                    } else {
                        echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td>' . _IC_ERROR_PAGEUPDATE . "</td></tr>\n";
                    }
                } else {
                    echo '  <tr><td style="background: #FF0000;">&nbsp;</td><td>' . _IC_NOPAGEUPDATE . "</td></tr>\n";
                }

                echo "</table>\n" . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $backDirectory . '">' . _IC_BACKTOPAGEADMIN . "<center>\n";
            } else {
                echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\">\n"
                     . '  <tr><td style="background: #FF0000;">&nbsp;</td><td>'
                     . _IC_ERROR_PAGENOTACTIVATED
                     . "</td></tr>\n"
                     . "</table>\n"
                     . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=0">'
                     . _IC_BACKTOPAGEADMIN
                     . "<center>\n";
            }
        }
    }

    /**
     * @Purpose : allows to download a page from the compilation in the BDD. If the page hasn't been compiled yet,
     * its compiled before downloading.
     */
    public function download()
    {
        global $xoopsConfig, $xoopsDB, $pageCompiler, $HTTP_USER_AGENT, $_GET;

        if (true === isset($_GET['page'])) {
            // Data is retrived

            $result = $xoopsDB->query('SELECT `name`, `content` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $_GET['page'] . "'");

            $item = $xoopsDB->fetchArray($result);

            // Makes the good header to download the file

            header('Content-Type: text/html');

            if (true === preg_match("/MSIE ([0-9]\.[0-9]{1,2})/", $HTTP_USER_AGENT)) {
                header('Content-Disposition: attachment; filename="' . $item['name'] . '.html"');

                header('Expires: 0');

                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

                header('Pragma: public');
            } else {
                header('Content-Disposition: attachment; filename="' . $item['name'] . '.html"');

                header('Expires: 0');

                header('Pragma: no-cache');
            }

            // Puts the page content

            if ('' == $item['content']) {
                if ($pageCompiler->compile($_GET['page'])) {
                    echo $pageCompiler->content;
                } else {
                    echo '<table class="outer" style="width: 100%; text-align: left;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_PAGECOMPILATION . '</td></tr></table>';
                }
            } else {
                echo $item['content'];
            }
        }
    }

    /**
     * @Purpose   : Page upload management for page creation.
     * @Important note : this should be used only from pageAdmin navigation for directCreation
     * and after configuration in the other cases.
     * @param mixed $option
     */
    public function upload($option = 'noDirectCreation')
    {
        global $xoopsConfig, $xoopsDB, $xoopsUser, $pageCompiler, $_POST, $_GET, $HTTP_POST_FILES;

        if (isset($_POST['uploadOption'])) {
            $option = $_POST['uploadOption'];
        }

        if (isset($HTTP_POST_FILES['page'])) {
            echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\"><tr>\n";

            if ('' != $HTTP_POST_FILES['page']['tmp_name']) {
                switch ($option) {
                    // The page is directly created with default name extracted from the filename.
                    // Moreover the current directory id is taken from $this->currentDirectory['id'].
                    case 'directCreation':
                        if ($pageCompiler->compile(
                            0,
                            implode('', file($HTTP_POST_FILES['page']['tmp_name'])),
                            $this->getPageName($HTTP_POST_FILES['page']['name']),
                            'Online',
                            $_POST['pageDirectory'],
                            $this->getAccess('forAllGroups'),
                            0,
                            $xoopsUser->uid(),
                            $_POST['commentsEnabled'],
                            $_POST['ratingEnabled']
                        )) {
                            $error = false;
                        } else {
                            $error = true;
                        }
                        break;
                    // The user can alter the name, parms and content before finishing.
                    case 'noDirectCreation':
                        if ($pageCompiler->compile(
                            0,
                            implode('', file($HTTP_POST_FILES['page']['tmp_name'])),
                            $_POST['pageName'],
                            'Online',
                            $_POST['pageDirectory'],
                            $this->getAccess('fromPostData'),
                            $_POST['pageHidden'],
                            $_POST['pageSubmitter'],
                            $_POST['commentsEnabled'],
                            $_POST['ratingEnabled']
                        )) {
                            $error = false;
                        } else {
                            $error = true;
                        }
                        break;
                    // Bad option, an error message is showed.
                    default:
                        $error = true;
                        break;
                }

                if ($error) {
                    echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_PAGECREATION . "</td>\n";
                } else {
                    echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_PAGECREATED . "</td>\n";
                }
            } else {
                echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_PAGECREATION . "</td>\n";
            }

            echo "</tr></table>\n" . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_POST['pageDirectory'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
        } else {
            if ('noDirectCreation' == $option) {
                echo "<table class=\"outer\" style=\"width: 100%; text-align: center;\"><tr><td class=\"even\">\n";
            }

            echo "<br>\n"
                 . "<form enctype=\"multipart/form-data\" action=\"admin.php?op=pageAdmin&amp;cop=upload\" method=\"POST\">\n"
                 . "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"102400\">\n"
                 . '    <font style="color: #00AA00; font-weight: bold;">[X]</font>&nbsp;'
                 . _IC_PAGETOUPLOAD
                 . "&nbsp;:&nbsp;<input name=\"page\" type=\"file\">\n"
                 . '    <input type="submit" value="'
                 . _IC_PAGEUPLOAD
                 . "\">\n";

            if ('noDirectCreation' == $option) {
                echo '    <input type="hidden" name="pageName" value="' . $_POST['pageName'] . "\">\n" . '    <input type="hidden" name="pageDirectory" value="' . $_POST['pageDirectory'] . "\">\n";

                for ($i = 0, $iMax = count($_POST['pageAccess']); $i < $iMax; $i++) {
                    echo '    <input type="hidden" name="pageAccess[]" value="' . $_POST['pageAccess'][$i] . "\">\n";
                }

                echo '    <input type="hidden" name="pageHidden" value="'
                     . (isset($_POST['pageHidden']) ? 1 : 0)
                     . "\">\n"
                     . '    <input type="hidden" name="commentsEnabled" value="'
                     . (isset($_POST['commentsEnabled']) ? 1 : 0)
                     . "\">\n"
                     . '    <input type="hidden" name="ratingEnabled" value="'
                     . (isset($_POST['ratingEnabled']) ? 1 : 0)
                     . "\">\n"
                     . '    <input type="hidden" name="pageSubmitter" value="'
                     . $_POST['pageSubmitter']
                     . "\">\n"
                     . "    <input type=\"hidden\" name=\"uploadOption\" value=\"noDirectCreation\">\n";
            } else {
                echo '    <input type="hidden" name="pageDirectory" value="' . $this->currentDirectory['id'] . "\">\n" . "    <input type=\"hidden\" name=\"uploadOption\" value=\"directCreation\">\n";
            }

            echo "  </form>\n";

            if ('noDirectCreation' == $option) {
                echo "</td></tr></table>\n" . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir=' . $_POST['pageDirectory'] . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
            }
        }
    }

    /**
     * @Purpose : creates an online page in the BDD with $_POST data.
     */
    public function create()
    {
        global $xoopsConfig, $xoopsDB, $_GET, $_POST;

        $myts = MyTextSanitizer::getInstance();

        $name = $myts->addSlashes($_POST['pageName']);

        $lastUpdate = time();

        $sql = 'INSERT INTO `'
                      . $xoopsDB->prefix()
                      . '_'
                      . $this->parentModule
                      . '_pages` (`name`, `url`, `directory`, `access`, `hidden`, `lastUpdate`, `submitter`, `commentsEnabled`, `ratingEnabled`) '
                      . "VALUES ('"
                      . $name
                      . "', 'Online', '"
                      . $_POST['pageDirectory']
                      . "', '"
                      . $this->getAccess()
                      . "', '"
                      . (isset($_POST['pageHidden']) ? 1 : 0)
                      . "', '"
                      . $lastUpdate
                      . "', '"
                      . $_POST['pageSubmitter']
                      . "', '"
                      . (isset($_POST['commentsEnabled']) ? 1 : 0)
                      . "', '"
                      . (isset($_POST['ratingEnabled']) ? 1 : 0)
                      . "')";

        if ($xoopsDB->queryF($sql)) {
            $result = $xoopsDB->queryF('SELECT `id` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `name`='" . $name . "' AND `url`='Online' AND `directory`='" . $_POST['pageDirectory'] . "' AND `lastUpdate`='" . $lastUpdate . "'");

            [$pageId] = $xoopsDB->fetchRow($result);

            $this->modification($pageId);
        } else {
            echo '<br><table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_PAGECREATION . "</td></tr></table>\n";
        }
    }

    /**
     * @Purpose : page creation management. A page could be created and its content could be edited since
     * the wysiwyg editor. There is an other solution : the page could be uploaded from a file on the user's
     * HDD.
     */
    public function creation()
    {
        global $xoopsConfig, $xoopsDB, $xoopsUser, $tools, $_POST, $_GET;

        if (isset($_POST['step2'])) {
            if ('allowed' == $_POST['step2']) {
                if (isset($_POST['pageUpload'])) {
                    $this->upload();
                } else {
                    $this->create();
                }
            }
        } else {
            // Loads the xoops groups into $groups

            $groups = $tools->getGroups();

            // Shows the edition form

            echo "<table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                 . '  <tr><th colspan="2">'
                 . _IC_PAGECREATION
                 . "</th></tr>\n"
                 . "  <tr>\n"
                 . '    <td class="head" style="width: 60%;">'
                 . _IC_NAME
                 . "</td>\n"
                 . '    <td class="head" style="width: 40%;">'
                 . _IC_ACCESSRIGHTS
                 . "</td>\n"
                 . "  <form name=\"creationForm\" method=\"post\" action=\"admin.php?op=pageAdmin&amp;cop=creation\" onSubmit=\"javascript:document.creationForm.submition.disabled=true;\">\n"
                 . "  <tr>\n"
                 . "    <td class=\"even\" style=\"width: 60%;\">\n"
                 . "       <input type=\"text\" name=\"pageName\" maxlenght=\"255\" size=\"50\" value=\"\"><br><br>\n"
                 . "    </td>\n"
                 . "    <td class=\"even\" style=\"width: 40%;\">\n";

            for ($j = 1, $jMax = count($groups); $j < $jMax; $j++) {
                echo '      <input type="checkbox" name="pageAccess[]" value="' . $groups[$j]['id'] . '" checked="checked">&nbsp;' . $groups[$j]['name'] . "<br>\n";
            }

            echo "    </td>\n"
                 . "  </tr>\n"
                 . "  <tr>\n"
                 . "    <td colspan=\"2\" class=\"odd\">\n"
                 . '    <input type="checkbox" name="pageUpload">&nbsp;'
                 . _IC_PAGEUPLOAD
                 . "&nbsp;&nbsp;&nbsp;&nbsp;\n"
                 . '    <input type="checkbox" name="pageHidden">&nbsp;'
                 . _IC_PAGEHIDDEN
                 . "&nbsp;&nbsp;&nbsp;&nbsp;\n"
                 . '    <input type="checkbox" name="commentsEnabled" checked="checked">&nbsp;'
                 . _IC_COMMENTSENABLED
                 . "&nbsp;&nbsp;&nbsp;&nbsp;\n"
                 . '    <input type="checkbox" name="ratingEnabled" checked="checked">&nbsp;'
                 . _IC_RATINGENABLED
                 . "\n"
                 . "    </td>\n"
                 . "  </tr>\n"
                 . '  <tr><td class="head" colspan="2"><input name="submition" type="submit" value="'
                 . _IC_CREATE
                 . "\"></td></tr>\n"
                 . "  <input type=\"hidden\" name=\"step2\" value=\"allowed\">\n"
                 . '  <input type="hidden" name="pageDirectory" value="'
                 . $_GET['currentDir']
                 . "\">\n"
                 . '  <input type="hidden" name="pageSubmitter" value="'
                 . $xoopsUser->uid()
                 . "\">\n"
                 . "  </form>\n"
                 . "</table>\n"
                 . '<br><center><a href="admin.php?op=pageAdmin&amp;cop=display&amp;currentDir='
                 . $_GET['currentDir']
                 . '">'
                 . _IC_BACKTOPAGEADMIN
                 . "</a></center>\n";
        }
    }
}
