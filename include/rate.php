<?php

// $Id: rate.php,v 1.1 2006/03/27 15:49:39 mikhail Exp $
//  --------------------------------------------------------------------------------------------------
//                XOOPS - PHP Content Management System
//                    Copyright (c) 2000 XOOPS.org
//                       <https://www.xoops.org>
// ---------------------------------------------------------------------------------------------------
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  You may not change or alter any portion of this comment or credits
//  of supporting developers from this source code or any supporting
//  source code which is considered copyrighted (c) material of the
//  original comment or credit authors.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
//  --------------------------------------------------------------------------------------------------

require_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object

if (!empty($_POST['submit'])) {
    $eh = new ErrorHandler(); //ErrorHandler object

    if (empty($xoopsUser)) {
        $ratinguser = 0;
    } else {
        $ratinguser = $xoopsUser->getVar('uid');
    }

    //Make sure only 1 anonymous from an IP in a single day.

    $anonwaitdays = 1;

    $ip = getenv('REMOTE_ADDR');

    $page = (int)$_POST['page'];

    $rating = (int)$_POST['rating'];

    // Check if Rating is Null

    if ('--' == $rating) {
        redirect_header('index.php?op=rate&amp;page=' . $page, 4, _IC_NORATING);

        exit();
    }

    // Check if Download POSTER is voting (UNLESS Anonymous users allowed to post)

    if (0 != $ratinguser) {
        $result = $xoopsDB->query('SELECT submitter FROM ' . $xoopsDB->prefix('icontent_pages') . ' WHERE id=' . $page . '');

        while (list($ratinguserDB) = $xoopsDB->fetchRow($result)) {
            if ($ratinguserDB == $ratinguser) {
                redirect_header('index.php', 4, _IC_CANTVOTEOWN);

                exit();
            }
        }

        // Check if REG user is trying to vote twice.

        $result = $xoopsDB->query('SELECT ratinguser FROM ' . $xoopsDB->prefix('icontent_votedata') . ' WHERE page=' . $page . '');

        while (list($ratinguserDB) = $xoopsDB->fetchRow($result)) {
            if ($ratinguserDB == $ratinguser) {
                redirect_header('index.php?page=' . $page, 4, _IC_VOTEONCE);

                exit();
            }
        }
    } else {
        // Check if ANONYMOUS user is trying to vote more than once per day.

        $yesterday = (time() - (86400 * $anonwaitdays));

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('icontent_votedata') . ' WHERE page=' . $page . " AND ratinguser=0 AND ratinghostname = '" . $ip . "'  AND ratingtimestamp > " . $yesterday . '');

        [$anonvotecount] = $xoopsDB->fetchRow($result);

        if ($anonvotecount >= 1) {
            redirect_header('index.php?page=' . $page, 4, _IC_VOTEONCE);

            exit();
        }
    }

    //All is well.  Add to Line Item Rate to DB.

    $newid = $xoopsDB->genId($xoopsDB->prefix('icontent_votedata') . '_ratingid_seq');

    $datetime = time();

    $sql = sprintf("INSERT INTO %s (ratingid, page, ratinguser, rating, ratinghostname, ratingtimestamp) VALUES (%u, %u, %u, %u, '%s', %u)", $xoopsDB->prefix('icontent_votedata'), $newid, $page, $ratinguser, $rating, $ip, $datetime);

    $xoopsDB->query($sql) or $eh('0013');

    //All is well.  Calculate Score & Add to Summary (for quick retrieval & sorting) to DB.

    updaterating($page);

    $ratemessage = _IC_VOTEAPPRE . '<br>' . sprintf(_IC_THANKYOU, $xoopsConfig['sitename']);

    redirect_header('index.php?page=' . $page, 4, $ratemessage);

    exit();
}
    $page = (int)$_GET['page'];
    $result = $xoopsDB->query('SELECT `name`, `ratingEnabled` FROM ' . $xoopsDB->prefix('icontent_pages') . ' WHERE id=' . $page . '');
    [$name, $ratingEnabled] = $xoopsDB->fetchRow($result);
    $name = htmlspecialchars($name, ENT_QUOTES | ENT_HTML5);
    // Now rating activation is checked for people who love playing with url params ;-)
    if (1 == $ratingEnabled) {
        $xoopsTpl->assign('page', ['id' => $page, 'name' => htmlspecialchars($name, ENT_QUOTES | ENT_HTML5)]);

        $xoopsTpl->assign('lang_voteonce', _IC_VOTEONCE);

        $xoopsTpl->assign('lang_ratingscale', _IC_RATINGSCALE);

        $xoopsTpl->assign('lang_beobjective', _IC_BEOBJECTIVE);

        $xoopsTpl->assign('lang_donotvote', _IC_DONOTVOTE);

        $xoopsTpl->assign('lang_rateit', _IC_RATEIT);

        $xoopsTpl->assign('lang_cancel', _IC_CANCEL);
    } else {
        redirect_header('index.php?page=' . $page, 4, _IC_ERROR_RATINGNOTENABLED . ' ' . $name);
    }
