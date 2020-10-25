<?php

// ------------------------------------------------------------------------- //
// Author: VIVI                                                              //
// email: alban.montaigu@free.fr                                             //
// Site: http://prodilog.com/vivi                                            //
// ------------------------------------------------------------------------- //

class compiler
{
    public $parentModule = '';

    public $type = '';

    public $source = '';

    public $content = '';

    //Precondition : none

    //Input : parameter $param

    //Postcondition : $this->parentModule contains the name of the module which use the class

    //Output : $this->parentModule

    public function __construct($parentModule, $type)
    {
        $this->parentModule = $parentModule;

        //Type of compilation. Now only html but maybe pdf in the future. The compiler

        //is prepared for this.

        $this->type = $type;
    }

    //Precondition : $id contains the id of the page which should be compiled

    //               $this->type contains the type of compilation

    //Input : parameter $id, global var $this->type

    //Postcondition : the good compilation type is launched

    //                $isDone = true if the compilation is done false in the other cases

    //Output : $isDone

    public function compilation($id)
    {
        $isDone = false;

        //Chooses the good compilation fonction

        switch ($this->type) {
            case 'html':
                $isDone = $this->htmlCompilation($id);
                break;
        }

        return $isDone;
    }

    //Precondition : $id contains the id of the page which should be compiled

    //Input : parameter $id

    //Postcondition : $this->content contains the compiled page and $this->source the url of the page which soud be compiled

    //Output : $this->content, $this->source

    public function htmlCompilation($id)
    {
        global $xoopsConfig, $xoopsDB;

        $isDone = false;

        $result = $xoopsDB->queryF('SELECT `url` FROM `' . $xoopsDB->prefix() . '_' . $this->parentModule . "_pages` WHERE `id`='" . $id . "'");

        [$this->source] = $xoopsDB->fetchRow($result);

        if (true === file_exists($this->source)) {
            //Load the default source file if no there is content passed before

            //With this command we can modify a content before compiling

            if ('' == $this->content) {
                $this->content = implode('', file($this->source));
            }

            //Deletes the <html> and </html> tags

            $this->content = preg_replace("'(?is)(<html>)|(</html>)'", '', $this->content);

            //Deletes the header of the page

            $this->content = preg_replace("'(?is)<head>(.*?)</head>'", '', $this->content);

            //Deletes the <body> and </body> tags

            $this->content = preg_replace("'(?is)(<body(.*?)>)|(</body>)'", '', $this->content);

            //Corrects all the relative urls of a picture to put the good urls. The absolutes ones aren't altered

            $this->content = preg_replace_callback("'(?is)src=(\"|\')(?(?=http://|inPages/)[^.]|(.*?))(\"|\')'", create_function('$matches', 'global $compiler; return "src=\"".dirname($compiler->source)."/".$matches[2]."\"";'), $this->content);

            //Corrects all the relative urls of a background picture to put the good urls. The absolutes aren't altered

            $this->content = preg_replace_callback("'(?is)background=(\"|\')(?(?=http://|inPages/)[^.]|(.*?))(\"|\')'", create_function('$matches', 'global $compiler; return "background=\"".dirname($compiler->source)."/".$matches[2]."\"";'), $this->content);

            //Corrects all the links with a relative url to put the good url. The absolutes ones and the emails aren't altered

            $this->content = preg_replace_callback(
                "'(?is)href=(\"|\')(?(?=http://|mailto:|#|inPages/)[^.]|(.*?))(\"|\')'",
                create_function(
                    '$matches',
                    '
                global $xoopsDB, $xoopsConfig, $compiler;

                $result = $xoopsDB->query("SELECT `id` FROM `".$xoopsDB->prefix()."_".$compiler->parentModule."_pages` WHERE `url`=\'".dirname($compiler->source)."/".$matches[2]."\'");
                $item = $xoopsDB->fetcharray($result);
                if(isset($item[\'id\']) === true) $link = "href=\"index.php?page=".$item[\'id\']."\"";
                else $link = "href=\"index.php?op=message&amp;id=1\"";
                return $link;
            '
                ),
                $this->content
            );

            $isDone = true;
        }

        return $isDone;
    }

    //Precondition : $id contains the id of the page which should be compiled

    //               $content, optionnal parameter wich contain the modified content of the page

    //Input : $id, $content

    //Postcondition : the page is compiled in a new file of the 'compiled' directory ($this->content contains the compiled page)

    //                $isCompiled = true if the compilation is done false in the other cases

    //Output : $isCompiled, $this->content

    public function compile($id, $content = '')
    {
        $isCompiled = false;

        //External content to compile if exist

        //With this command we can modify a content before compiling

        $this->content = $content;

        if (true === $this->compilation($id)) {
            //Makes the url of the compiled file (ex: compiled/page6.html)

            $url = 'compiled/page' . $id . '.' . $this->type;

            //Deletes the old file if exists

            if (true === file_exists($url)) {
                unlink($url);
            }

            //Writes data in the file

            $file = @fopen($url, 'wb');

            if (false !== $file) {
                fwrite($file, $this->content);

                fclose($file);

                $isCompiled = true;
            }
        }

        return $isCompiled;
    }
}
