<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

//==========================================================================
// CLASS DEPENDANCE
//==========================================================================

// Syntax_hilighting class dependence
if (!isset($syntax_highlighting)) {
    if (file_exists('class/syntax_highlighting.class.php')) {
        require_once __DIR__ . '/class/syntax_highlighting.class.php';

        $syntax_highlighting = new syntax_highlighting();
    } else {
        trigger_error('unable to find class <b><i>syntax_highlighting.class.php</i></b>', E_USER_ERROR);
    }
}

// Url_correction class dependence
if (!isset($url_correction)) {
    if (file_exists('class/url_correction.class.php')) {
        require_once __DIR__ . '/class/url_correction.class.php';

        $url_correction = new url_correction();
    } else {
        trigger_error('unable to find class <b><i>url_correction.class.php</i></b>', E_USER_ERROR);
    }
}

//==========================================================================
// PAGECOMPILER CLASS
//==========================================================================

class pageCompiler
{
    public $parentModule;

    public $type;

    public $source;

    public $source_dir;

    public $content;

    public $makeTime;

    /**
     * @Constructor fo this class.
     * @Purpose     : initialisation of the class. And configuration of the type of data
     * which should be managed (html for example).
     * @param mixed $parentModule
     * @param mixed $type
     */
    public function __construct($parentModule, $type)
    {
        global $url_correction;

        $this->parentModule = $parentModule;

        $url_correction->set_parent_module($parentModule);

        $url_correction->set_url('inPages');

        // Type of compilation. Now only html but maybe pdf in the future. The compiler

        // is prepared for this.

        $this->type = $type;

        // Other vars

        $this->source = '';

        $this->source_dir = '';

        $this->content = '';

        $this->syntax_items = [];

        $this->syntax_items_nb = 0;

        // Used to keep the make time of the files on the server

        $this->makeTime = 0;
    }

    /**
     * @Purpose : lunches the good type of compilation.
     * @param mixed $id
     * @return bool
     * @return bool
     */
    public function compilation($id)
    {
        // Chooses the good compilation fonction

        switch ($this->type) {
            default:
                $isDone = false;
                break;
            case 'html':
                $isDone = $this->htmlCompilation($id);
                break;
        }

        return $isDone;
    }

    /**
     * @Purpose : compile the content specified by $id and returns it.
     * @Note    : if $this->content is assigned, it is directly compiled.
     * @param mixed $id
     * @return bool
     * @return bool
     */
    public function htmlCompilation($id)
    {
        global $xoopsConfig, $xoopsDB;

        if (0 == $id) {
            $this->source = 'Online';

            $this->source_dir = 'inPages';
        } else {
            $result = $xoopsDB->queryF('SELECT `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $id . "'");

            [$this->source] = $xoopsDB->fetchRow($result);

            $this->source_dir = dirname($this->source);
        }

        if (file_exists($this->source) || ('Online' == $this->source)) {
            // Load the default source file if no there is content passed before

            // With this command we can modify a content before compiling

            if (('' == $this->content) && ('Online' != $this->source)) {
                $this->content = implode('', file($this->source));

                $this->makeTime = filemtime($this->source);
            } // Content is altered online, so the current time is retrieved

            else {
                $this->makeTime = time();
            }

            // Modification engine

            global $url_correction, $syntax_highlighting;

            $content_array = preg_preg_split("'(?is)\[(code type=)(?i:\"|\')(.*?)(?i:\"|\')\](.*?)\[/code\]'", $this->content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            $content_array_size = count($content_array);

            $this->content = '';

            for ($i = 0; $i < $content_array_size; $i++) {
                if (0 == strcasecmp($content_array[$i], 'code type=')) {
                    $this->content .= $syntax_highlighting->fetch($content_array[++$i], $content_array[++$i]);
                } else {
                    $this->content .= $url_correction->all($content_array[$i]);
                }
            }

            // Remove blanks before and after the page content

            $this->content = trim($this->content);

            $isDone = true;
        } else {
            $isDone = false;
        }

        return $isDone;
    }

    /**
     * @Purpose : gets the compiled data and puts it in the BDD.
     * @param mixed $id
     * @param mixed $content
     * @param mixed $name
     * @param mixed $url
     * @param mixed $directory
     * @param mixed $access
     * @param mixed $hidden
     * @param mixed $submitter
     * @param mixed $commentsEnabled
     * @param mixed $ratingEnabled
     * @return bool
     * @return bool
     */
    public function compile($id = 0, $content = '', $name = '', $url = 'Online', $directory = 0, $access = '0', $hidden = 0, $submitter = 0, $commentsEnabled = 1, $ratingEnabled = 1)
    {
        global $xoopsConfig, $xoopsDB;

        // External content to compile if exist

        // With this command we can modify a content before compiling

        $this->content = $content;

        if ($this->compilation($id)) {
            // Correction since icontent 4.5 : makeTime() of the file is used instead of time() which is the current server time.

            // This is the solution of the automatic compilation issue.

            if (0 == $id) {
                // Online compilation, no sourcefile on the server

                if ($xoopsDB->queryF(
                    'INSERT INTO `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` (`name`, `url`, `directory`, `content`, `access`, `hidden`, `lastUpdate`, `submitter`, `commentsEnabled`, `ratingEnabled`) VALUES ('" . $name . "', '" . $url . "', '" . $directory . "', '" . addslashes(
                        $this->content
                    ) . "', '" . $access . "', '" . $hidden . "', '" . $this->makeTime . "', '" . $submitter . "', '" . $commentsEnabled . "', '" . $ratingEnabled . "')"
                )) {
                    $isCompiled = true;
                } else {
                    $isCompiled = false;
                }
            } else {
                // Compilation from file on server

                if ($xoopsDB->queryF('UPDATE `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` SET `content`='" . addslashes($this->content) . "', `lastUpdate`='" . $this->makeTime . "'  WHERE `id`=" . $id . '')) {
                    $isCompiled = true;
                } else {
                    $isCompiled = false;
                }
            }
        } else {
            $isCompiled = false;
        }

        return $isCompiled;
    }
}
