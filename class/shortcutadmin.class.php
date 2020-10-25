<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// SHORTCUTS ADMIN CLASS
//==========================================================================

class shortcutAdmin
{
    public $parentModule;

    public $id;

    public $name;

    public $page;

    /**
     * @Constructor fo this class.
     * @Purpose     : initialisation of the class.
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->id = 0;

        $this->name = '';

        $this->page = 0;
    }

    /**
     * @Purpose : creates the shortcut in the BDD.
     */
    public function create()
    {
        global $xoopsDB, $xoopsConfig, $_POST;

        $myts = MyTextSanitizer::getInstance();

        echo '<table class="outer" style="width: 100%; text-align: center;"><tr>';

        $this->name = $myts->addSlashes($this->name);

        if ($xoopsDB->queryF('INSERT INTO `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` (`name`, `page`, `submenu`) VALUES ('" . $this->name . "', '" . $this->page . "', '" . (isset($_POST['submenu']) ? 1 : 0) . "')")) {
            echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_SHORTCUTCREATED . "</td>\n";
        } else {
            echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SHORTCUTCREATION . "</td>\n";
        }

        echo "</tr></table>\n";
    }

    /**
     * @Purpose : shortcut management.
     */
    public function creation()
    {
        global $xoopsConfig, $xoopsDB, $_POST, $_GET;

        if (isset($_GET['page'])) {
            $myts = MyTextSanitizer::getInstance();

            $this->page = $_GET['page'];

            $result = $xoopsDB->queryF('SELECT `id`, `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` WHERE `page`='" . $this->page . "'");

            if (0 == $GLOBALS['xoopsDB']->getRowsNum($result)) {
                if (true === isset($_POST['operation'])) {
                    if ('useBDD' == $_POST['operation']) {
                        $this->name = $_POST['name'];

                        $this->create();
                    }
                } else {
                    $result = $xoopsDB->queryF('SELECT `name` , `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->page . "'");

                    $item = $xoopsDB->fetchArray($result);

                    $this->name = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                    echo '<form action="admin.php?op=shortcutAdmin&amp;cop=creation&amp;page='
                         . $this->page
                         . "\" method=\"post\">\n"
                         . "  <table class=\"outer\" style=\"width: 100%; text-align: left;\">\n"
                         . '    <tr><th colspan="2">'
                         . _IC_SHORTCUTCREATION
                         . "</th></tr>\n"
                         . "    <tr>\n"
                         . '      <td class="head" style="width: 20%;"><img src="images/item.gif">&nbsp;'
                         . _IC_NAME
                         . "&nbsp;:</td>\n"
                         . "      <td class=\"even\" style=\"width: 80%;\">\n"
                         . '        <input type="text" name="name" size="50" value="'
                         . $this->name
                         . "\" maxlength=\"255\">\n"
                         . "        <input type=\"hidden\" name=\"operation\" value=\"useBDD\">\n"
                         . "      </td>\n"
                         . "    </tr>\n"
                         . "    <tr>\n"
                         . '      <td class="head" style="width: 20%;"><img src="images/item.gif">&nbsp;'
                         . _IC_PAGEURL
                         . "&nbsp;:</td>\n"
                         . '      <td class="odd" style="width: 80%;">'
                         . $item['url']
                         . "</td>\n"
                         . "    </tr>\n"
                         . "    <tr>\n"
                         . '      <td class="head"><img src="images/item.gif">&nbsp;'
                         . _IC_SUBMENU
                         . "&nbsp;:</td>\n"
                         . "      <td class=\"even\"><input type=\"checkbox\" name=\"submenu\"></td>\n"
                         . "    </tr>\n"
                         . "    <tr>\n"
                         . '      <td class="head" style="width: 20%;"><img src="images/item.gif">&nbsp;'
                         . _IC_ACTIONS
                         . "&nbsp;:</td>\n"
                         . "      <td class=\"head\" style=\"width: 80%;\">\n"
                         . '        <input type="submit" value="'
                         . _IC_CREATE
                         . "\">\n"
                         . "        <input type='button' value='"
                         . _IC_CANCEL
                         . "' onclick='javascript:history.go(-1);'>\n"
                         . "      </td>\n"
                         . "    </tr>\n"
                         . "  </table>\n"
                         . "</form>\n";
                }
            } else {
                echo "<table class=\"outer\" style=\"width : 100%; text-align: left;\">\n"
                     . '  <tr><th colspan="3">'
                     . _IC_SHORTCUTALREADYCREATED
                     . "</th></tr>\n"
                     . "  <tr>\n"
                     . '    <td class="head" width="20%"><i>'
                     . _IC_NAME
                     . "</i></td>\n"
                     . '    <td class="head" width="60%"><i>'
                     . _IC_PAGEURL
                     . "</i></td>\n"
                     . '    <td class="head" width="20%"><i>'
                     . _IC_ACTIONS;

                "</i></td>\n" . "  </tr>\n";

                $item = $xoopsDB->fetchArray($result);

                $this->name = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                $this->id = $item['id'];

                $result = $xoopsDB->queryF('SELECT `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->page . "'");

                $item = $xoopsDB->fetchArray($result);

                echo "  <tr>\n"
                     . '    <td class="even" width="20%">'
                     . $this->name
                     . "</td>\n"
                     . '    <td class="odd" width="60%">'
                     . $item['url']
                     . "</td>\n"
                     . "    <td class=\"even\" width=\"20%\">\n"
                     . '      <a href="admin.php?op=shortcutAdmin&amp;cop=configuration&amp;id='
                     . $this->id
                     . '">'
                     . _IC_CONFIGURE
                     . "</a>&nbsp;&nbsp;\n"
                     . '      <a href="admin.php?op=shortcutAdmin&amp;cop=deleting&amp;id='
                     . $this->id
                     . '">'
                     . _IC_DELETE
                     . "</a>\n"
                     . "    </td>\n"
                     . "  </tr>\n"
                     . "</table>\n";
            }

            $result = $xoopsDB->queryF('SELECT `directory` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->page . "'");

            [$currentDirectory] = $xoopsDB->fetchRow($result);

            echo '<br><center><a href="admin.php?op=pageAdmin&cop=display&currentDir=' . $currentDirectory . '">' . _IC_BACKTOPAGEADMIN . "</a></center>\n";
        }
    }

    /**
     * @Purpose : configures a shortcut in the BDD.
     */
    public function configure()
    {
        global $xoopsDB, $xoopsConfig, $_POST;

        $myts = MyTextSanitizer::getInstance();

        echo '<table class="outer" style="width: 100%; text-align: center;"><tr>';

        $this->name = $myts->addSlashes($this->name);

        if ($xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` SET `name`='" . $this->name . "', `submenu`='" . (isset($_POST['submenu']) ? 1 : 0) . "' WHERE `id`=" . $this->id . '')) {
            echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_SHORTCUTCONFIGURED . "</td>\n";
        } else {
            echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SHORTCUTCONFIGURATION . "</td>\n";
        }

        echo "</tr></table>\n";
    }

    /**
     * @Purpose : shortcut configuration management.
     */
    public function configuration()
    {
        global $_POST, $_GET, $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        if (isset($_GET['id'])) {
            $this->id = $_GET['id'];

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->name = $_POST['name'];

                    $this->configure();
                }
            } else {
                $result = $xoopsDB->queryF('SELECT `name`, `page`, `submenu` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_shortcuts` WHERE `id`='" . $this->id . "'");

                $item = $xoopsDB->fetchArray($result);

                $this->name = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                $this->page = $item['page'];

                $checked = (1 == $item['submenu']) ? ' checked ' : ' ';

                $result = $xoopsDB->queryF('SELECT `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->page . "'");

                $item = $xoopsDB->fetchArray($result);

                echo '<form action="admin.php?op=shortcutAdmin&amp;cop=configuration&amp;id='
                     . $this->id
                     . "\" method=\"post\">\n"
                     . "  <table class=\"outer\" style=\"width : 100%; text-align: left;\">\n"
                     . '    <tr><th colspan="2">'
                     . _IC_SHORTCUTCONFIGURATION
                     . "</th></tr>\n"
                     . "    <tr>\n"
                     . '      <td class="head"><img src="images/item.gif">&nbsp;'
                     . _IC_NAME
                     . "&nbsp;:</td>\n"
                     . "      <td class=\"even\">\n"
                     . '        <input type="text" name="name" size="50" value="'
                     . $this->name
                     . "\" maxlength=\"255\">\n"
                     . "        <input type=\"hidden\" name=\"operation\" value=\"useBDD\">\n"
                     . "      </td>\n"
                     . "    </tr>\n"
                     . "    <tr>\n"
                     . '      <td class="head"><img src="images/item.gif">&nbsp;'
                     . _IC_PAGEURL
                     . "&nbsp;:</td>\n"
                     . '      <td class="odd">'
                     . $item['url']
                     . "</td>\n"
                     . "    </tr>\n"
                     . "    <tr>\n"
                     . '      <td class="head"><img src="images/item.gif">&nbsp;'
                     . _IC_SUBMENU
                     . "&nbsp;:</td>\n"
                     . '      <td class="even"><input type="checkbox" name="submenu"'
                     . $checked
                     . "></td>\n"
                     . "    </tr>\n"
                     . "    <tr>\n"
                     . '      <td class="head"><img src="images/item.gif">&nbsp;'
                     . _IC_ACTIONS
                     . "&nbsp;:</td>\n"
                     . "      <td class=\"head\">\n"
                     . '        <input type="submit" value="'
                     . _IC_CONFIGURE
                     . "\">\n"
                     . "        <input type='button' value='"
                     . _IC_CANCEL
                     . "' onclick='javascript:history.go(-1);'>\n"
                     . "      </td>\n"
                     . "    </tr>\n"
                     . " </table>\n"
                     . "</form>\n";
            }
        }

        echo '<br><center><a href="admin.php?op=shortcutAdmin&cop=display">' . _IC_BACKTOSHORTCUTSADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : deletes a shortcut in the bdd.
     */
    public function delete()
    {
        global $xoopsDB, $xoopsConfig;

        echo '<table class="outer" style="width: 100%; text-align: center;"><tr>';

        if ($xoopsDB->queryF('DELETE FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . '_shortcuts` WHERE `id`=' . $this->id . '')) {
            echo '<td style="background: #00FF00;">&nbsp;</td><td class="even">' . _IC_SHORTCUTDELETED . "</td>\n";
        } else {
            echo '<td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_ERROR_SHORTCUTDELETING . "</td>\n";
        }

        echo "</tr></table>\n" . '<br><center><a href="admin.php?op=shortcutAdmin&cop=display">' . _IC_BACKTOSHORTCUTSADMIN . "</a></center>\n";
    }

    /**
     * @Purpose : shortcut deleting management.
     */
    public function deleting()
    {
        global $_POST, $_GET;

        if (isset($_GET['id'])) {
            $this->id = $_GET['id'];

            if (isset($_POST['operation'])) {
                if ('useBDD' == $_POST['operation']) {
                    $this->delete();
                }
            } else {
                echo "<center>\n"
                     . "  <table class=\"outer\" style=\"width : 50%; text-align: center;\">\n"
                     . '    <tr><th>'
                     . _IC_CONFIRM_SHORTCUTDELETING
                     . "&nbsp;</font></th></tr>\n"
                     . "    <tr>\n"
                     . "      <td class=\"even\"><br>\n"
                     . "        <form action='admin.php?op=shortcutAdmin&amp;cop=deleting&amp;id="
                     . $this->id
                     . "' method='post'>\n"
                     . "          <input type='hidden' name='operation' value='useBDD'>\n"
                     . "          <input type='submit' value='"
                     . _IC_DELETE
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
        }
    }

    /**
     * @Purpose : displays the shortcut admin management.
     */
    public function display()
    {
        global $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `page` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . '_shortcuts` order by `name`');

        if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
            echo "<table class=\"outer\" style=\"width : 100%; text-align: left;\">\n"
                 . '  <tr><th colspan="3">'
                 . _IC_SHORTCUTSLIST
                 . "</th></tr>\n"
                 . "  <tr>\n"
                 . '    <td class="head" width="20%"><i>'
                 . _IC_NAME
                 . "</i></td>\n"
                 . '    <td class="head" width="60%"><i>'
                 . _IC_PAGEURL
                 . "</i></td>\n"
                 . '    <td class="head" width="20%"><i>'
                 . _IC_ACTIONS;

            "</i></td>\n" . "  </tr>\n";

            while (false !== ($item = $xoopsDB->fetchArray($result))) {
                $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                $result2 = $xoopsDB->queryF('SELECT `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $item['page'] . "'");

                $item2 = $xoopsDB->fetchArray($result2);

                echo "  <tr>\n"
                     . '    <td class="even" width="20%">'
                     . $item['name']
                     . "</td>\n"
                     . '    <td class="odd" width="60%">'
                     . $item2['url']
                     . "</td>\n"
                     . "    <td class=\"even\" width=\"20%\">\n"
                     . "      <ul>\n"
                     . '        <li><a href="admin.php?op=shortcutAdmin&amp;cop=configuration&amp;id='
                     . $item['id']
                     . '">'
                     . _IC_CONFIGURE
                     . "</a>\n"
                     . '        <li><a href="admin.php?op=shortcutAdmin&amp;cop=deleting&amp;id='
                     . $item['id']
                     . '">'
                     . _IC_DELETE
                     . "</a>\n"
                     . "      </ul>\n"
                     . "    </td>\n"
                     . "  </tr>\n";
            }

            echo "</table>\n";
        } else {
            echo '<table class="outer" style="width: 100%; text-align: center;"><tr><td style="background: #FF0000;">&nbsp;</td><td class="even">' . _IC_NORECORD . "</td></tr></table>\n";
        }
    }
}
