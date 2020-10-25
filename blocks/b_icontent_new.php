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
function icontent_directoryHidden($dirId)
{
    global $xoopsDB, $xoopsConfig;

    $result = $xoopsDB->queryF('SELECT `pid`, `hidden` FROM `' . $xoopsDB->prefix('icontent_directories') . "` WHERE `id`='" . $dirId . "'");

    [$parentDirId, $isHidden] = $xoopsDB->fetchRow($result);

    if (1 == $isHidden) {
        $dirHidden = true;
    } elseif (0 != $parentDirId) {
        $dirHidden = icontent_directoryHidden($parentDirId);
    } else {
        $dirHidden = false;
    }

    return $dirHidden;
}

//==========================================================================
// BLOCKS FUNCTIONS
//==========================================================================

function b_icontent_newPages($options)
{
    global $xoopsDB, $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['new'] = [];

    $result = $xoopsDB->query('SELECT `id`, `directory`, `name`, `lastUpdate` FROM `' . $xoopsDB->prefix() . "_icontent_pages` WHERE `hidden`='0' ORDER BY `lastUpdate` DESC LIMIT 0, " . $options[1]);

    if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            // A page is showed only if her parent dirs aren't hidden

            if (!icontent_directoryHidden($item['directory'])) {
                $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

                if (mb_strlen($item['name']) > $options[0]) {
                    $item['name'] = mb_substr($item['name'], 0, $options[0]) . '...';
                }

                $block['new'][] = ['name' => $item['name'], 'link' => XOOPS_URL . '/modules/icontent/index.php?page=' . $item['id'], 'lastUpdate' => formatTimestamp($item['lastUpdate'])];
            }
        }

        if (count($block['new']) > 0) {
            $block['content'] = 1;
        } else {
            $block['content'] = 0;
        }
    } else {
        $block['content'] = 0;

        $block['lang_noNew'] = _IC_B_NOPAGE;
    }

    return $block;
}

function b_icontent_newPages_edit($options)
{
    $form = _IC_B_NAMELENGTH . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _IC_B_CHARS . "<br>\n" . _IC_B_PAGESNUMBER . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[1] . "'><br>\n";

    return $form;
}
