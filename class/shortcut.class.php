<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// SHORTCUT CLASS
//==========================================================================

class shortcut
{
    public $parentModule;

    public $listing;

    /**
     * @Constructor fo this class.
     * @Purpose     : initialisation of the class.
     * @param mixed $parentModule
     */
    public function __construct($parentModule)
    {
        $this->parentModule = $parentModule;

        $this->listing = [];
    }

    /**
     * @Purpose : sends shortcut data to the template for displaying.
     */
    public function display()
    {
        global $xoopsDB, $xoopsConfig, $xoopsTpl;

        $myts = MyTextSanitizer::getInstance();

        $result = $xoopsDB->queryF('SELECT `name`, `page` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . '_shortcuts` ORDER BY `name`');

        if ($GLOBALS['xoopsDB']->getRowsNum($result) > 0) {
            while (false !== ($item = $xoopsDB->fetchArray($result))) {
                $result2 = $xoopsDB->queryF('SELECT `hits` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $item['page'] . "'");

                $item2 = $xoopsDB->fetchArray($result2);

                $this->listing[] = ['name' => htmlspecialchars($item['name'], ENT_QUOTES | ENT_HTML5), 'link' => ('index.php?page=' . $item['page']), 'hits' => $item2['hits']];
            }
        }

        // Template configuration

        $xoopsTpl->assign('nbShortcuts', count($this->listing));

        $xoopsTpl->assign('shortcut', $this->listing);

        $xoopsTpl->assign('lang_shortcuts', _IC_SHORTCUTS);

        $xoopsTpl->assign('lang_hits', _IC_HITS);
    }
}
