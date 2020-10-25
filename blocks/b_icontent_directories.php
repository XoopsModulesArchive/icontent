<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

function b_icontent_directories($options)
{
    global $xoopsDB, $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['directory'] = [];

    $result = $xoopsDB->query('SELECT `id`, `name`, `homePage` FROM `' . $xoopsDB->prefix() . "_icontent_directories` WHERE (`pid`='0' AND `hidden`='0') ORDER BY `name`");

    if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

            if (mb_strlen($item['name']) > $options[0]) {
                $item['name'] = mb_substr($item['name'], 0, $options[0]) . '...';
            }

            if (0 == $item['homePage']) {
                $link = XOOPS_URL . '/modules/icontent/index.php?op=explore&amp;currentDir=' . $item['id'];
            } else {
                $link = XOOPS_URL . '/modules/icontent/index.php?page=' . $item['homePage'];
            }

            $block['directory'][] = ['name' => $item['name'], 'link' => $link];
        }

        $block['content'] = 1;
    } else {
        $block['content'] = 0;

        $block['lang_noDirectory'] = _IC_B_NODIRECTORY;
    }

    return $block;
}

function b_icontent_directories_edit($options)
{
    $form = _IC_B_NAMELENGTH . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _IC_B_CHARS . "\n";

    return $form;
}
