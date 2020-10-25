<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// URL_CORRECTION CLASS
//==========================================================================

class url_correction
{
    public $url;

    public $parent_module;

    /**
     * @Purpose : url correction constructor.
     */
    public function __construct()
    {
        $this->parent_module = '';

        $this->url = '';
    }

    public function set_url($url)
    {
        $this->url = $url;
    }

    public function set_parent_module($parent_module)
    {
        $this->parent_module = $parent_module;
    }

    /**
     * @Purpose : runs url correction on all available items.
     * @param mixed $content
     * @return string|string[]|null
     * @return string|string[]|null
     */
    public function all($content)
    {
        $content = $this->flash($content);

        $content = $this->src($content);

        $content = $this->background($content);

        $content = $this->href($content);

        return $content;
    }

    /**
     * @Purpose : runs url correcton on flash objects.
     * @param mixed $content
     * @return string|string[]|null
     * @return string|string[]|null
     */
    public function flash($content)
    {
        // Corrects flash plugins param

        $pattern = "'(?is)param(.*?)name=[\"\']movie[\"\'](.*?)value=[\"\'](?(?=http://|inPages/)|(.*?))[\"\']'";

        return preg_replace_callback($pattern, [$this, '_flash_callback'], $content);
    }

    /**
     * @Purpose : returns corrected flash object.
     * @param mixed $content
     * @return string
     * @return string
     */
    public function _flash_callback($content)
    {
        return 'param' . $content[1] . 'name="movie"' . $content[2] . 'value="' . $this->url . '/' . $content[3] . '"';
    }

    /**
     * @Purpose : runs url correcton on src items.
     * @param mixed $content
     * @return string|string[]|null
     * @return string|string[]|null
     */
    public function src($content)
    {
        // Corrects all the relative urls of a picture to put the good urls. The absolutes ones aren't altered

        $pattern = "'(?is)src=[\"\'](?(?=http://|inPages/)|(.*?))[\"\']'";

        return preg_replace_callback($pattern, [$this, '_src_callback'], $content);
    }

    /**
     * @Purpose : returns corrected src item.
     * @param mixed $content
     * @return string
     * @return string
     */
    public function _src_callback($content)
    {
        return 'src="' . $this->url . '/' . $content[1] . '"';
    }

    /**
     * @Purpose : runs url correcton on background item.
     * @param mixed $content
     * @return string|string[]|null
     * @return string|string[]|null
     */
    public function background($content)
    {
        // Corrects all the relative urls of a background picture to put the good urls. The absolutes aren't altered

        $pattern = "'(?is)background=[\"\'](?(?=http://|inPages/)|(.*?))[\"\']'";

        return preg_replace_callback($pattern, [$this, '_background_callback'], $content);
    }

    /**
     * @Purpose : returns corrected background item.
     * @param mixed $content
     * @return string
     * @return string
     */
    public function _background_callback($content)
    {
        return 'background="' . $this->url . '/' . $content[1] . '"';
    }

    /**
     * @Purpose : runs url correcton on href item.
     * @param mixed $content
     * @return string|string[]|null
     * @return string|string[]|null
     */
    public function href($content)
    {
        // Corrects all the links with a relative url to put the good url. The absolutes ones and the emails aren't altered

        $pattern = "'(?is)href=[\"\'](?(?=http://|mailto:|#|inPages/)|(.*?)(?i:(#[0-9]+?){1}))[\"\']'";

        return preg_replace_callback($pattern, [$this, '_href_callback'], $content);
    }

    /**
     * @Purpose : returns corrected href item.
     * @param mixed $content
     * @return string
     * @return string
     */
    public function _href_callback($content)
    {
        global $xoopsDB, $xoopsConfig;

        $result = $xoopsDB->query('SELECT `id` FROM `' . $xoopsDB->prefix($this->parent_module . '_pages') . "` WHERE `url`='" . $this->url . '/' . $content[1] . "'");

        $item = $xoopsDB->fetchArray($result);

        if (isset($item['id'])) {
            $link = 'href="index.php?page=' . $item['id'] . $content[2] . '"';
        } else {
            $link = 'href="index.php?op=message&amp;id=1"';
        }

        return $link;
    }
}
