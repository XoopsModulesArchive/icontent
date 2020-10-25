<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// USEFULL FUNCTIONS
//==========================================================================

/**
 * @Purpose : checks if the directory specified by $dirId is hidden. All the parent ones are checked too.
 * @param mixed $dirId
 * @return bool|mixed
 * @return bool|mixed
 */
function directoryHidden($dirId)
{
    global $xoopsDB, $xoopsConfig;

    $result = $xoopsDB->queryF('SELECT `pid`, `hidden` FROM `' . $xoopsDB->prefix('icontent_directories') . "` WHERE `id`='" . $dirId . "'");

    [$parentDirId, $isHidden] = $xoopsDB->fetchRow($result);

    if (1 == $isHidden) {
        $dirHidden = true;
    } elseif (0 != $parentDirId) {
        $dirHidden = directoryHidden($parentDirId);
    } else {
        $dirHidden = false;
    }

    return $dirHidden;
}

//==========================================================================
// SEARCH FUNCTIONS
//==========================================================================

function pages_search($queryarray, $andor, $limit, $offset, $userid)
{
    global $xoopsDB, $xoopsModuleConfig;

    $sql = 'SELECT id, name, directory, hidden, lastUpdate, submitter FROM ' . $xoopsDB->prefix('icontent_pages') . ' WHERE  hidden=0';

    if (0 != $userid) {
        $sql .= ' AND submitter=' . $userid;
    }

    // because count() returns 1 even if a supplied variable

    // is not an array, we must check if $querryarray is really an array

    if (is_array($queryarray) && $count = count($queryarray)) {
        $sql .= " AND ((content LIKE '%" . $queryarray[0] . "%' OR name LIKE '%" . $queryarray[0] . "%')"; // Thanks mercibe

        for ($i = 1; $i < $count; $i++) {
            $sql .= ' ' . $andor . ' ';

            $sql .= "(content LIKE '%" . $queryarray[$i] . "%' OR name LIKE '%" . $queryarray[$i] . "%')";
        }

        $sql .= ') ';
    }

    $sql .= ' ORDER BY lastUpdate DESC'; // Thanks mercibe

    $result = $xoopsDB->query($sql, $limit, $offset);

    $ret = [];

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        if (!directoryHidden($myrow['directory'])) {
            $ret[] = ['image' => 'images/page.gif', 'link' => 'index.php?page=' . $myrow['id'], 'title' => $myrow['name'], 'time' => $myrow['lastUpdate'], 'uid' => $myrow['submitter']];
        }
    }

    return $ret;
}
