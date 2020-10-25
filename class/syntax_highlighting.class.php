<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// SYNTAX_HIGHLIGHTING CLASS
//==========================================================================

class syntax_highlighting
{
    /**
     * @Private vars
     */

    public $_syntax_item;

    public $_syntax_item_nb;

    public $_pattern;

    /**
     * @Constructor of this class
     */
    public function __construct()
    {
        $this->_syntax_item = [];

        $this->_syntax_item_nb = 0;

        $this->_pattern = '';
    }

    /**
     * @Purpose : loads syntax data from config file.
     * @param mixed $language
     */
    public function _load_syntax_from_file($language)
    {
        $item = file('include/syntax_hilighting_data/' . $language . '.txt');

        $this->_syntax_item_nb = count($item);

        // Syntax items and their associated caracteristics

        for ($i = 0; $i < $this->_syntax_item_nb; $i++) {
            $this->_syntax_item[$i] = explode('=', rtrim($item[$i]));
        }
    }

    /**
     * @Purpose : makes pattern for syntax matching.
     */
    public function _make_pattern()
    {
        // Preparing pattern

        $pattern_item = [];

        for ($i = 0; $i < $this->_syntax_item_nb; $i++) {
            $pattern_item[$i] = $this->_syntax_item[$i][0];
        }

        $this->_pattern = "'(?is)(" . implode('|', $pattern_item) . ")'";
    }

    /**
     * @Purpose : syntax highlighting. It matchs code syntax.
     * @param mixed $language
     * @param mixed $code
     * @return string
     * @return string
     */
    public function fetch($language, $code)
    {
        $code = htmlentities($code, ENT_QUOTES | ENT_HTML5);

        $code = str_replace(' ', '&nbsp;', $code);

        $this->_load_syntax_from_file($language);

        $this->_make_pattern();

        $code = preg_replace_callback($this->_pattern, [$this, '_fetch_callback'], $code);

        $code = '<div class="xoopsCode"><b>' . $language . " CODE</b><hr>\n" . nl2br($code) . "\n</div>\n";

        return $code;
    }

    /**
     * @Purpose : syntax hilighting callback. It returns colorized code.
     * @param mixed $code
     * @return mixed|string
     * @return mixed|string
     */
    public function _fetch_callback($code)
    {
        for ($i = 0; $i < $this->_syntax_item_nb; $i++) {
            if (preg_match("'(?is)" . $this->_syntax_item[$i][0] . "'", $code[1])) {
                return '<font style="' . $this->_syntax_item[$i][1] . '">' . $code[1] . '</font>';
            }
        }

        return $code[1];
    }
}
