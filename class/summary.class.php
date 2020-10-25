<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

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
// SUMMARY CLASS
//==========================================================================

class summary
{
    public $parentModule;

    public $directories;

    public $pages;

    /**
     * @Constructor fo this class.
     * @Purpose     : initialisation of the class.
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->directories = [];

        $this->pages = [];
    }

    /**
     * @Purpose : builds the list of all directories oganised with parent - child relation.
     * The result is put in an array $this->directories which contains the indentation for the
     * presentation too.
     * @param mixed $pid
     * @param mixed $indentation
     */
    public function getDirectories($pid = 0, $indentation = 0)
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `homePage`, `hits` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE (`pid`='" . $pid . "' AND `hidden`='0') ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            if (0 == $item['homePage']) {
                $link = 'index.php?op=explore&amp;currentDir=' . $item['id'];
            } else {
                $link = 'index.php?page=' . $item['homePage'];
            }

            $this->directories[] = ['indentation' => str_repeat('&nbsp;&nbsp;&nbsp;', $indentation), 'name' => $item['name'], 'link' => $link, 'hits' => $item['hits']];

            $this->getDirectories($item['id'], $indentation + 1);
        }
    }

    /**
     * @Purpose : gets all the activated pages in the root and puts them in an array ($this->pages).
     */
    public function getPages()
    {
        global $xoopsDB, $xoopsConfig, $tools;

        $result = $xoopsDB->queryF('SELECT `id`, `name`, `comments`, `hits`, `lastUpdate`, `rating`, `votes`, `submitter`, `commentsEnabled`, `ratingEnabled` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE (`directory`='0' AND `hidden`='0') ORDER BY `name`");

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $this->pages[] = [
'name' => $item['name'],
'link' => ('index.php?page=' . $item['id']),
'hits' => $item['hits'],
'comments' => $item['comments'],
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
     * @Purpose : builds the summary and sends it to the templates.
     */
    public function display()
    {
        global $xoopsConfig, $xoopsTpl;

        // Gets all the activated pages in the "inPages" directory

        $this->getPages();

        // Gets all the directories activated

        $this->getDirectories();

        // Template configuration

        $xoopsTpl->assign('directories', $this->directories);

        $xoopsTpl->assign('pages', $this->pages);

        $xoopsTpl->assign('lang_summary', _IC_SUMMARY);

        $xoopsTpl->assign('lang_hits', _IC_HITS);

        $xoopsTpl->assign('lang_comments', _IC_COMMENTS);

        $xoopsTpl->assign('lang_lastUpdate', _IC_LASTUPDATE);

        $xoopsTpl->assign('lang_pageSubmittedBy', _IC_PAGESUBMITTEDBY);

        $xoopsTpl->assign('lang_rating', _IC_RATING);

        $xoopsTpl->assign('lang_votes', _IC_VOTES);
    }
}
