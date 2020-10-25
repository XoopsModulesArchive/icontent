<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

function b_icontent_shortcuts($options)
{
    global $xoopsDB, $xoopsConfig;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['shortcut'] = [];

    $result = $xoopsDB->query('SELECT `name`, `page` FROM `' . $xoopsDB->prefix() . '_icontent_shortcuts` ORDER BY `name`');

    if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
        while (false !== ($item = $xoopsDB->fetchArray($result))) {
            $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5);

            if (mb_strlen($item['name']) > $options[0]) {
                $item['name'] = mb_substr($item['name'], 0, $options[0]) . '...';
            }

            $block['shortcut'][] = ['name' => $item['name'], 'link' => XOOPS_URL . '/modules/icontent/index.php?page=' . $item['page']];
        }

        $block['content'] = 1;
    } else {
        $block['content'] = 0;

        $block['lang_noShortcut'] = _IC_B_NOSHORTCUT;
    }

    return $block;
}

function b_icontent_shortcuts_edit($options)
{
    $form = _IC_B_NAMELENGTH . "&nbsp;:&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _IC_B_CHARS . "\n";

    return $form;
}
