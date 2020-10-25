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
// EXPLORER CLASS
//==========================================================================

class explorer
{
    public $parentModule;

    public $parentDirectory;

    public $currentDirectory;

    public $childDirectories;

    public $childPages;

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

        $this->currentDirectory = ['id' => 0, 'pid' => -1, 'name' => _IC_HOME];

        $this->childDirectories = [];

        $this->childPages = [];

        $this->directories = [];
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
            $this->directories[] = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('index.php?op=explore&amp;currentDir=' . $item['id'])];

            $this->getDirectories($item['pid']);
        } else {
            // Puts the sections in the goog order

            $this->directories = array_reverse($this->directories);

            // Puts the indentations fort the sections

            for ($i = 0, $iMax = count($this->directories); $i < $iMax; $i++) {
                $this->directories[$i]['indentation'] = str_repeat('&nbsp;&nbsp;', $i);
            }
        }
    }

    /**
     * @Purpose : gets all the direct child directories of the directory specified by
     * $this->currentDirectory.
     */
    public function getChildDirectories()
    {
        global $xoopsDB, $xoopsConfig;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `homePage`, `hits` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE (`pid`='" . $this->currentDirectory['id'] . "' AND `hidden`='0') ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            if (0 == $item['homePage']) {
                $link = 'index.php?op=explore&amp;currentDir=' . $item['id'];
            } else {
                $link = 'index.php?page=' . $item['homePage'];
            }

            $this->childDirectories[] = ['id' => $item['id'], 'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => $link, 'hits' => $item['hits']];
        }
    }

    /**
     * @Purpose : gets all the direct child pages of the directory specified by
     * $this->currentDirectory.
     */
    public function getChildPages()
    {
        global $xoopsDB, $xoopsConfig, $tools;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF(
            'SELECT `id`, `name`, `comments`, `hits`, `lastUpdate`, `rating`, `votes`, `submitter`, `commentsEnabled`, `ratingEnabled` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE (`directory`='" . $this->currentDirectory['id'] . "' AND `hidden`='0') ORDER BY `name`"
        );

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $this->childPages[] = [
                'id' => $item['id'],
                'name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5),
                'link' => ('index.php?page=' . $item['id']),
                'comments' => $item['comments'],
                'hits' => $item['hits'],
                'lastUpdate' => formatTimestamp($item['lastUpdate']),
                'rating' => number_format($item['rating'], 2),
                'votes' => $item['votes'],
                'submitter' => $tools->getUnameFromId($item['submitter']),
                'commentsEnabled' => $item['commentsEnabled'],
                'ratingEnabled' => $item['ratingEnabled'],
            ];
        }
    }

    /**
     * @Purpose : explorer management and then data is sent to the template.
     */
    public function display()
    {
        global $xoopsDB, $xoopsConfig, $xoopsTpl, $_GET;

        $myts = MyTextSanitizer::getInstance();

        if (isset($_GET['currentDir'])) {
            $this->currentDirectory['id'] = $_GET['currentDir'];
        }

        if (0 != $this->currentDirectory['id']) {
            $result = $xoopsDB->queryF('SELECT `pid`, `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->currentDirectory['id'] . "'");

            $item = $xoopsDB->fetchArray($result);

            $this->currentDirectory['pid'] = $item['pid'];

            $this->currentDirectory['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

            //Updates the hits of the directory

            $xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` SET hits=hits+1 WHERE `id`='" . $this->currentDirectory['id'] . "'");
        }

        if (-1 != $this->currentDirectory['pid']) {
            if (0 != $this->currentDirectory['pid']) {
                $result = $xoopsDB->queryF('SELECT `name` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $this->currentDirectory['pid'] . "'");

                $item = $xoopsDB->fetchArray($result);

                $this->parentDirectory = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('index.php?op=explore&amp;currentDir=' . $this->currentDirectory['pid'])];
            } else {
                $this->parentDirectory = ['name' => _IC_HOME, 'link' => 'index.php'];
            }
        }

        // Gets some data

        $this->getChildDirectories();

        $this->getChildPages();

        $this->getDirectories($this->currentDirectory['id']);

        // Template configuration

        $xoopsTpl->assign('parentDirectory', $this->parentDirectory);

        $xoopsTpl->assign('currentDirectory', $this->currentDirectory);

        $xoopsTpl->assign('childDirectory', $this->childDirectories);

        $xoopsTpl->assign('childPage', $this->childPages);

        $xoopsTpl->assign('directory', $this->directories);

        $xoopsTpl->assign('directory', $this->directories);

        // Language items

        $xoopsTpl->assign('lang_directory', _IC_DIRECTORY);

        $xoopsTpl->assign('lang_chosenDirectories', _IC_CHOSENDIRECTORIES);

        $xoopsTpl->assign('lang_comments', _IC_COMMENTS);

        $xoopsTpl->assign('lang_hits', _IC_HITS);

        $xoopsTpl->assign('lang_lastUpdate', _IC_LASTUPDATE);

        $xoopsTpl->assign('lang_rating', _IC_RATING);

        $xoopsTpl->assign('lang_votes', _IC_VOTES);

        $xoopsTpl->assign('lang_pageSubmittedBy', _IC_PAGESUBMITTEDBY);
    }
}
