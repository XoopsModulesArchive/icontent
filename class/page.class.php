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

// PageCompiler class dependence
if (!isset($pageCompiler)) {
    if (file_exists('class/pageCompiler.class.php')) {
        require_once __DIR__ . '/class/pageCompiler.class.php';

        $pageCompiler = new pageCompiler($parentModule, 'html');
    } else {
        trigger_error('unable to find class <b><i>pageCompiler.class.php</i></b>', E_USER_ERROR);
    }
}

// Shortcut class dependence
if (!isset($shortcut)) {
    if (file_exists('class/shortcut.class.php')) {
        require_once __DIR__ . '/class/shortcut.class.php';

        $shortcut = new shortcut($parentModule);
    } else {
        trigger_error('unable to find class <b><i>shortcut.class.php</i></b>', E_USER_ERROR);
    }
}

//==========================================================================
// PAGE CLASS
//==========================================================================

class page
{
    public $parentModule;

    public $id;

    public $name;

    public $url;

    public $content;

    public $lastUpdate;

    public $isPHP;

    /**
     * @Constructor of this class.
     * @Purpose     : initialises the class.
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->id = 0;

        $this->name = '';

        $this->url = '';

        $this->content = '';

        $this->lastUpdate = 0;

        $this->isPHP = false;
    }

    /**
     * @Purpose : checks if the directory specified by $dirId is hidden. All the parent ones are checked too.
     * @param mixed $dirId
     * @return bool|mixed
     * @return bool|mixed
     */
    public function directoryHidden($dirId)
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->queryF('SELECT `pid`, `hidden` FROM `' . $xoopsDB->prefix('icontent_directories') . "` WHERE `id`='" . $dirId . "'");

        [$parentDirId, $isHidden] = $xoopsDB->fetchRow($result);

        if (1 == $isHidden) {
            $dirHidden = true;
        } elseif (0 != $parentDirId) {
            $dirHidden = $this->directoryHidden($parentDirId);
        } else {
            $dirHidden = false;
        }

        return $dirHidden;
    }

    /**
     * @Purpose : compares the access with the groups the user belongs to. If no
     * group matches the access id denied.
     * @Note    : since icontent 4.5, webmaster access is always granted
     * @param mixed $access
     * @return bool
     * @return bool
     */
    public function userAccess($access)
    {
        global $xoopsUser;

        if (is_object($xoopsUser)) {
            $userGroups = $xoopsUser->getGroups();
        } else {
            $userGroups[] = 3;
        } // Case for anonym user

        $groupAuthorised = explode(':', $access);

        for ($i = 0, $iMax = count($userGroups); $i < $iMax; $i++) {
            for ($j = 0, $jMax = count($groupAuthorised); $j < $jMax; $j++) {
                if (($userGroups[$i] == $groupAuthorised[$j]) || (1 == $userGroups[$i])) {
                    //User access granted

                    $isAllowed = true;

                    //Exits of the 2 "for" loops

                    break 2;
                }

                $isAllowed = false;
            }
        }

        return $isAllowed;
    }

    /**
     * @Purpose : checks if the user cand view the page.
     */
    public function pageAccess()
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->queryF('SELECT `access` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->id . "'");

        $item = $xoopsDB->fetchArray($result);

        $isAllowed = $this->userAccess($item['access']);

        return $isAllowed;
    }

    /**
     * @Purpose : checks if the user has rights to view the content of the directory.
     * @Note    : the rights of the current directory and all his parents are checked.
     * @param mixed $directoryId
     * @return bool|mixed
     * @return bool|mixed
     */
    public function directoryAccess($directoryId)
    {
        global $xoopsDB, $xoopsConfig;

        if (0 != $directoryId) {
            $result = $xoopsDB->queryF('SELECT `pid`, `access` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $directoryId . "'");

            $item = $xoopsDB->fetchArray($result);

            if ($this->userAccess($item['access'])) {
                $dirAccess = $this->directoryAccess($item['pid']);
            } else {
                $dirAccess = false;
            }
        } // The user is on the root, the access is authorised for this directory

        else {
            $dirAccess = true;
        }

        return $dirAccess;
    }

    /**
     * @Purpose : checks all the rigths (directory and page) to know if the user
     * can view the page.
     */
    public function accessAllowed()
    {
        global $xoopsDB, $xoopsConfig;

        if ($this->pageAccess()) {
            $result = $xoopsDB->queryF('SELECT `directory` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->id . "'");

            $item = $xoopsDB->fetchArray($result);

            $isAllowed = $this->directoryAccess($item['directory']);
        } else {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * @Purpose : content management. Loads the content & compiles it if needed etc...
     */
    public function loadContent()
    {
        global $xoopsDB, $xoopsConfig, $pageCompiler, $xoopsModuleConfig, $tools;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->query('SELECT `name`, `url`, `content`, `lastUpdate` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->id . "'");

        if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
            [$this->name, $this->url, $this->content, $this->lastUpdate] = $xoopsDB->fetchRow($result);

            // Is the page online ?

            if ('Online' != $this->url) {
                // Is the page written in php ?

                if (!$tools->is_php($this->url)) {
                    // Is the page compiled ?

                    if ('' == $this->content) {
                        if ($pageCompiler->compile($this->id)) {
                            $this->content = $pageCompiler->content;

                            $isLoaded = true;
                        } else {
                            $isLoaded = false;
                        }
                    } // The page is already compiled

                    else {
                        //More recent pages should be checked on the server ?

                        if ($xoopsModuleConfig['pageUpdate']) {
                            // Is the file on the server more recent ?

                            if ($this->lastUpdate < filemtime($this->url)) {
                                // Recompilation of the page since the original one on the server

                                if ($pageCompiler->compile($this->id)) {
                                    $this->content = $pageCompiler->content;

                                    $isLoaded = true;
                                } else {
                                    $isLoaded = false;
                                }
                            } // The page on the server isn't more recent

                            else {
                                $isLoaded = true;
                            }
                        } // More recent page shouldn't be checked

                        else {
                            $isLoaded = true;
                        }
                    }
                } else {
                    // The page is written in php, no compilation is needed but the page is evalued and

                    // the result is putted is the content var.

                    // Maybe in future versions, the content produced by the evaluation will be compiled or

                    // only corrected by icontent but i may need a lot of time and ressources !

                    ob_start();

                    include $this->url;

                    $this->content = ob_get_contents();

                    ob_end_clean();

                    $this->isPHP = true;

                    $isLoaded = true;
                }
            } else {
                $isLoaded = true;
            } // If the page is online no update system is needed

            // Since version 4.1 the xoops features like smileys and xoopscode are enabled

            $this->content = $myts->displayTarea($this->content, 1, 1, 1, 1, 0);
        } else {
            $isLoaded = false;
        }

        return $isLoaded;
    }

    /**
     * @Purpose : page diplaying management. All the operation needed are done and the data
     * is sent to te template.
     */
    public function display()
    {
        global $xoopsTpl, $xoopsDB, $xoopsConfig, $xoopsModuleConfig, $xoopsUser, $tools, $shortcut, $_GET;

        if (isset($_GET['page'])) {
            $this->id = $_GET['page'];

            if ($this->accessAllowed()) {
                if ($this->loadContent()) {
                    // shortcutsOnPages section

                    $xoopsTpl->assign('shortcutsOnPages', $xoopsModuleConfig['shortcutsOnPages']);

                    if (1 == $xoopsModuleConfig['shortcutsOnPages']) {
                        $shortcut->display();
                    }

                    // Sends page content to the template

                    $xoopsTpl->assign('pageContent', $this->content);

                    // Sends back url to the template

                    $result = $xoopsDB->queryF('SELECT `id`, `directory`, `rating`, `votes`, `submitter`, `commentsEnabled`, `ratingEnabled` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $this->id . "'");

                    $item = $xoopsDB->fetchArray($result);

                    if (0 == $item['directory']) {
                        $backUrl = 'index.php';
                    } else {
                        $result = $xoopsDB->queryF('SELECT `homePage` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` WHERE `id`='" . $item['directory'] . "'");

                        $item2 = $xoopsDB->fetchArray($result);

                        if (0 == $item2['homePage']) {
                            $backUrl = 'index.php?op=explore&amp;currentDir=' . $item['directory'];
                        } else {
                            $backUrl = 'index.php?page=' . $item2['homePage'];

                            // Updates the hits of the directory

                            if ($item2['homePage'] == $item['id']) {
                                $xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_directories` SET hits=hits+1 WHERE `id`='" . $item['directory'] . "'");
                            }
                        }
                    }

                    $xoopsTpl->assign('backUrl', $backUrl);

                    // Sends print url to the template

                    $xoopsTpl->assign('printUrl', 'index.php?op=print&amp;page=' . $this->id);

                    // Sends rate url to the template

                    $xoopsTpl->assign('rateUrl', 'index.php?op=rate&amp;page=' . $this->id);

                    // Sends rating results to the template

                    $xoopsTpl->assign('rating', number_format($item['rating'], 2));

                    $xoopsTpl->assign('votes', $item['votes']);

                    // Sends config items to the template

                    $xoopsTpl->assign('navBar', $xoopsModuleConfig['navBar']);

                    // Sends submitter to the template

                    $xoopsTpl->assign('submitter', $tools->getUnameFromId($item['submitter']));

                    // Modification link for admin

                    if (is_object($xoopsUser)) {
                        if ($xoopsUser->isAdmin() && !$this->isPHP) {
                            $modifyPageLink = 'admin.php?op=pageAdmin&amp;cop=modification&amp;id=' . $this->id;
                        } else {
                            $modifyPageLink = '';
                        }
                    } else {
                        $modifyPageLink = '';
                    }

                    $xoopsTpl->assign('modifyPageLink', $modifyPageLink);

                    // Are comments enabled ?

                    $xoopsTpl->assign('commentsEnabled', $item['commentsEnabled']);

                    // Are rating enabled ?

                    $xoopsTpl->assign('ratingEnabled', $item['ratingEnabled']);

                    // Language items

                    $xoopsTpl->assign('lang_back', _IC_BACK);

                    $xoopsTpl->assign('lang_print', _IC_PRINT);

                    $xoopsTpl->assign('lang_rate', _IC_RATEIT);

                    $xoopsTpl->assign('lang_modifyPage', _IC_MODIFYPAGE);

                    $xoopsTpl->assign('lang_rating', _IC_RATING);

                    $xoopsTpl->assign('lang_votes', _IC_VOTES);

                    $xoopsTpl->assign('lang_pageSubmittedBy', _IC_PAGESUBMITTEDBY);

                    // Updates the hits of the page

                    $xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` SET hits=hits+1 WHERE `id`='" . $this->id . "'");
                } else {
                    redirect_header('index.php', 2, _IC_ERROR_PAGEDISPLAYING);

                    exit();
                }
            } else {
                redirect_header('index.php', 2, _IC_NORIGHT);

                exit();
            }
        }
    }

    /**
     * @Purpose : page diplaying management but in printable format.
     */
    public function printableFormat()
    {
        global $xoopsConfig, $xoopsModule, $_GET;

        if (isset($_GET['page'])) {
            $this->id = $_GET['page'];

            if ($this->accessAllowed()) {
                if ($this->loadContent()) {
                    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n"
                         . "<html>\n"
                         . "<head>\n"
                         . '<meta http-equiv="Content-Type" content="text/html; charset='
                         . _CHARSET
                         . "\">\n"
                         . '<title>'
                         . $xoopsConfig['sitename']
                         . "</title>\n"
                         . '<meta name="AUTHOR" content="'
                         . $xoopsConfig['sitename']
                         . "\">\n"
                         . '<meta name="COPYRIGHT" content="Copyright (c) 2001 by '
                         . $xoopsConfig['sitename']
                         . "\">\n"
                         . '<meta name="DESCRIPTION" content="'
                         . $xoopsConfig['slogan']
                         . "\">\n"
                         . '<meta name="GENERATOR" content="'
                         . XOOPS_VERSION
                         . "\">\n"
                         . '<link rel="stylesheet" type="text/css" media="all" href="'
                         . XOOPS_URL
                         . "/xoops.css\">\n"
                         . '<link rel="stylesheet" type="text/css" media="all" href="'
                         . xoops_getcss($xoopsConfig['theme_set'])
                         . "\">\n"
                         . "</head>\n"
                         . "<body bgcolor=\"#ffffff\" text=\"#000000\" onload=\"window.print()\">\n"
                         . "  <div style=\"width: 640px; padding: 4px;\">\n"
                         . "    <center>\n"
                         . '      <img src="'
                         . XOOPS_URL
                         . "/images/logo.gif\" border=\"0\">\n"
                         . '      <br><br>'
                         . _IC_THISCOMESFROM
                         . '&nbsp;'
                         . $xoopsConfig['sitename']
                         . "\n"
                         . '      <br><a href="'
                         . XOOPS_URL
                         . '/">'
                         . XOOPS_URL
                         . "</a>\n"
                         . "    </center>\n"
                         . '    <br><br><br>'
                         . $this->content
                         . "\n"
                         . "    <center>\n"
                         . '      <br><br><br>'
                         . _IC_URLFORPAGE
                         . "&nbsp;:&nbsp;\n"
                         . '      <br><a href="'
                         . XOOPS_URL
                         . '/modules/'
                         . $xoopsModule->dirname()
                         . '/index.php?page='
                         . $this->id
                         . '">'
                         . XOOPS_URL
                         . '/index.php?page='
                         . $this->id
                         . "</a>\n"
                         . "    </center>\n"
                         . "  </div>\n"
                         . "</body>\n"
                         . "</html>\n";
                } else {
                    redirect_header('index.php', 2, _IC_ERROR_PAGEDISPLAYING);

                    exit();
                }
            } else {
                redirect_header('index.php', 2, _IC_NORIGHT);

                exit();
            }
        }
    }

    /**
     * @Purpose : misc function to display configured message in the page and redirect the user.
     */
    public function message()
    {
        global $_GET;

        switch ($_GET['id']) {
            case 1:
                redirect_header('index.php', 2, _IC_MESS_PAGENOTACTIVATED);
                exit();
                break;
        }
    }

    /**
     * @Purpose : top ten and votes management for the pages.
     */
    public function top10()
    {
        global $xoopsTpl, $xoopsDB, $xoopsConfig, $_GET;

        $myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object

        require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';

        $mytree = new XoopsTree($xoopsDB->prefix('icontent_directories'), 'id', 'pid');

        // Generates top 10 charts by rating and hits for each main category

        if (isset($_GET['rate'])) {
            $sort = _IC_RATING;

            $sortDB = 'rating';
        } else {
            $sort = _IC_HITS;

            $sortDB = 'hits';
        }

        $i = 0;

        $rankings = [];

        $childDirectoriesId = [];

        // Retrives all main directories

        $result = $xoopsDB->query('SELECT id, name FROM ' . $xoopsDB->prefix('icontent_directories') . ' WHERE (pid=0 AND hidden=0)');

        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $rankings[$i]['directoryName'] = sprintf(_IC_TOP10, htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5));

            $query = 'SELECT id, name, directory, hits, rating, votes FROM ' . $xoopsDB->prefix('icontent_pages') . ' WHERE (directory=' . $item['id'];

            // Gets all child cat ids for a given cat id

            $childDirectoriesId = $mytree->getAllChildId($item['id']);

            $size = count($childDirectoriesId);

            for ($j = 0; $j < $size; $j++) {
                $query .= ' or directory=' . $childDirectoriesId[$j] . '';
            }

            $query .= ' AND hidden=0) order by ' . $sortDB . ' DESC';

            $result2 = $xoopsDB->query($query, 10, 0);

            $rank = 1;

            while (false !== ($item2 = $xoopsDB->fetchArray($result2))) {
                if (!$this->directoryHidden($item2['directory'])) {
                    $pagePath = $mytree->getPathFromId($item2['directory'], 'name');

                    $pagePath = mb_substr($pagePath, 1);

                    $pagePath = str_replace('/', " <span class='fg2'>&raquo;&raquo;</span> ", $pagePath);

                    $rankings[$i]['page'][] = [
                        'id' => $item2['id'],
                        'directory' => $item2['directory'],
                        'rank' => $rank,
                        'name' => htmlspecialchars($item2['name'], ENT_QUOTES | ENT_HTML5),
                        'category' => $pagePath,
                        'hits' => $item2['hits'],
                        'rating' => number_format($item2['rating'], 2),
                        'votes' => $item2['votes'],
                    ];

                    $rank++;
                }
            }

            // Even if there is no page in the dir we set the index 'page'

            if (!isset($rankings[$i]['page'])) {
                $rankings[$i]['page'] = [];
            }

            $i++;
        }

        // Rankings for the pages on the root

        $result2 = $xoopsDB->query('SELECT id, name, directory, hits, rating, votes FROM ' . $xoopsDB->prefix('icontent_pages') . ' WHERE (directory=0 and hidden=0)');

        if ($GLOBALS['xoopsDB']->getRowsNum($result2) > 0) {
            $rank = 1;

            $rankings[$i]['directoryName'] = sprintf(_IC_TOP10, _IC_MAINPAGES);

            while (false !== ($item2 = $xoopsDB->fetchArray($result2))) {
                $rankings[$i]['page'][] = ['id' => $item2['id'], 'directory' => $item2['directory'], 'rank' => $rank, 'name' => htmlspecialchars($item2['name'], ENT_QUOTES | ENT_HTML5), 'category' => _IC_NONE, 'hits' => $item2['hits'], 'rating' => number_format($item2['rating'], 2), 'votes' => $item2['votes']];

                $rank++;
            }
        }

        // Sends data to the templates

        $xoopsTpl->assign('rankings', $rankings);

        // Language items

        $xoopsTpl->assign('lang_sortby', $sort);

        $xoopsTpl->assign('lang_rank', _IC_RANK);

        $xoopsTpl->assign('lang_title', _IC_NAME);

        $xoopsTpl->assign('lang_category', _IC_DIRECTORY);

        $xoopsTpl->assign('lang_hits', _IC_HITS);

        $xoopsTpl->assign('lang_rating', _IC_RATING);

        $xoopsTpl->assign('lang_vote', _IC_VOTES);

        $xoopsTpl->assign('lang_pagesTop10', _IC_PAGESTOP10);
    }
}
