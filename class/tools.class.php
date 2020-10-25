<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// TOOLS CLASS
//==========================================================================

class tools
{
    /**
     * @Purpose : gets the user name from an uid even if he isn't connected in opposition of
     * $xoopsUser class. If the user isn't connected "anonymous" is returned.
     * @param mixed $uid
     * @return mixed
     * @return mixed
     */
    public function getUnameFromId($uid)
    {
        global $xoopsDB, $xoopsConfig;

        if ($uid <= 0) {
            $uname = $xoopsConfig['anonymous'];
        } else {
            $result = $xoopsDB->queryF('SELECT `uname` FROM `' . $xoopsDB->prefix('users') . '` WHERE `uid`=' . $uid . '');

            [$uname] = $xoopsDB->fetchRow($result);
        }

        return $uname;
    }

    /**
     * @Purpose : gets all the xoops groups in an array with the id and the name of the group.
     * @param mixed $option
     * @return array
     * @return array
     */
    public function getGroups($option = 'all')
    {
        global $xoopsConfig, $xoopsDB;

        // Retrieves the xoops groups name and id

        $groups = [];

        switch ($option) {
            case 'all':
                $result = $xoopsDB->queryF('SELECT `groupid`, `name` FROM `' . $xoopsDB->prefix('groups') . '`');
                while (false !== ($item = $xoopsDB->fetchArray($result))) {
                    $groups[] = ['id' => $item['groupid'], 'name' => $item['name']];
                }
                break;
            case 'id':
                $result = $xoopsDB->queryF('SELECT `groupid` FROM `' . $xoopsDB->prefix('groups') . '`');
                while (list($groups[]) = $xoopsDB->fetchRow($result)) {
                }
                break;
            case 'name':
                $result = $xoopsDB->queryF('SELECT `name` FROM `' . $xoopsDB->prefix('groups') . '`');
                while (list($groups[]) = $xoopsDB->fetchRow($result)) {
                }
                break;
        }

        return $groups;
    }

    /**
     * @Purpose : checks the extension of the file sepcified in url. Returns true if .htm or .html
     * false in the other cases.
     * @param mixed $url
     * @return bool
     * @return bool
     */
    public function is_html($url)
    {
        if (!is_dir($url)) {
            $urlData = explode('.', $url);

            $ext = $urlData[count($urlData) - 1];

            if (('htm' == $ext) || ('html' == $ext)) {
                $html = true;
            } else {
                $html = false;
            }
        } else {
            $html = false;
        }

        return $html;
    }

    /**
     * @Purpose : checks the extension of the file sepcified in url. Returns true if .php or .php3
     * false in the other cases.
     * @param mixed $url
     * @return bool
     * @return bool
     */
    public function is_php($url)
    {
        if (!is_dir($url)) {
            $urlData = explode('.', $url);

            $ext = $urlData[count($urlData) - 1];

            if (('php' == $ext) || ('php3' == $ext)) {
                $php = true;
            } else {
                $php = false;
            }
        } else {
            $php = false;
        }

        return $php;
    }
}
