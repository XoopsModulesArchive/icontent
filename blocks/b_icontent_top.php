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
function icontent_topDirectoryHidden($dirId)
{
    global $xoopsDB, $xoopsConfig;

    $result = $xoopsDB->queryF('SELECT `pid`, `hidden` FROM `' . $xoopsDB->prefix('icontent_directories') . "` WHERE `id`='" . $dirId . "'");

    [$parentDirId, $isHidden] = $xoopsDB->fetchRow($result);

    if (1 == $isHidden) {
        $dirHidden = true;
    } elseif (0 != $parentDirId) {
        $dirHidden = icontent_topDirectoryHidden($parentDirId);
    } else {
        $dirHidden = false;
    }

    return $dirHidden;
}

//==========================================================================
// BLOCKS FUNCTIONS
//==========================================================================

function b_icontent_topPages($options)
{
    global $xoopsDB, $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['top'] = [];

    $block['lang_hits'] = _IC_B_HITS;

    $result = $xoopsDB->query('SELECT `id`, `directory`, `name`, `hits` FROM `' . $xoopsDB->prefix() . "_icontent_pages` WHERE `hidden`='0' ORDER BY `hits` DESC LIMIT 0, " . $options[1]);

    if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            // A page is showed only if her parent dirs aren't hidden

            if (!icontent_topDirectoryHidden($item['directory'])) {
                $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                if (mb_strlen($item['name']) > $options[0]) {
                    $item['name'] = mb_substr($item['name'], 0, $options[0]) . '...';
                }

                $block['top'][] = ['name' => $item['name'], 'link' => XOOPS_URL . '/modules/icontent/index.php?page=' . $item['id'], 'hits' => $item['hits']];
            }
        }

        if (count($block['top']) > 0) {
            $block['content'] = 1;
        } else {
            $block['content'] = 0;
        }
    } else {
        $block['content'] = 0;

        $block['lang_noTop'] = _IC_B_NOPAGE;
    }

    return $block;
}

function b_icontent_topPages_edit($options)
{
    $form = _IC_B_NAMELENGTH . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _IC_B_CHARS . "<br>\n" . _IC_B_PAGESNUMBER . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[1] . "'><br>\n";

    return $form;
}

function b_icontent_topDirectories($options)
{
    global $xoopsDB, $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['top'] = [];

    $block['lang_hits'] = _IC_B_HITS;

    $result = $xoopsDB->query('SELECT `id`, `pid`, `name`, `homePage`, `hits` FROM `' . $xoopsDB->prefix() . "_icontent_directories` WHERE `hidden`='0' ORDER BY `hits` DESC LIMIT 0, " . $options[1]);

    if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            // A directory is showed only if her parent dirs aren't hidden

            if (!icontent_topDirectoryHidden($item['pid'])) {
                $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                if (mb_strlen($item['name']) > $options[0]) {
                    $item['name'] = mb_substr($item['name'], 0, $options[0]) . '...';
                }

                if (0 == $item['homePage']) {
                    $link = XOOPS_URL . '/modules/icontent/index.php?op=explore&amp;currentDir=' . $item['id'];
                } else {
                    $link = XOOPS_URL . '/modules/icontent/index.php?page=' . $item['homePage'];
                }

                $block['top'][] = ['name' => $item['name'], 'link' => $link, 'hits' => $item['hits']];
            }
        }

        if (count($block['top']) > 0) {
            $block['content'] = 1;
        } else {
            $block['content'] = 0;
        }
    } else {
        $block['content'] = 0;

        $block['lang_noTop'] = _IC_B_NODIRECTORY;
    }

    return $block;
}

function b_icontent_topDirectories_edit($options)
{
    $form = _IC_B_NAMELENGTH . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _IC_B_CHARS . "<br>\n" . _IC_B_DIRECTORIESNUMBER . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[1] . "'><br>\n";

    return $form;
}
