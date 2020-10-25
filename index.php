<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

// Inclusion of the xoops's parameters
require_once dirname(__DIR__, 2) . '/mainfile.php';

// iContent conficuration
$parentModule = 'icontent';

// Page management
// A completly different page is builded according to the operation
// It allows to include only the needed libraries and functions to execute the operation
if (isset($_GET['op'])) {
    switch ($_GET['op']) {
        default:
            // Error redirection
            redirect_header('index.php', 2, _IC_ERROR_NOVALIDOPERATION);
            break;
        case 'explore':
            // Xoops configuration
            $GLOBALS['xoopsOption']['template_main'] = 'icontent_explorer.html';

            // iContent configuration
            require_once __DIR__ . '/class/explorer.class.php';
            $explorer = new explorer($parentModule);

            // Construction of the complete page
            require_once XOOPS_ROOT_PATH . '/header.php';
            $explorer->display();
            require_once XOOPS_ROOT_PATH . '/footer.php';
            break;
        case 'message':
            // Xoops configuration
            $GLOBALS['xoopsOption']['template_main'] = 'icontent_message.html';

            // iContent configuration
            require_once __DIR__ . '/class/page.class.php';
            $page = new page($parentModule);

            // Construction of the complete page
            require_once XOOPS_ROOT_PATH . '/header.php';
            $page->message();
            require_once XOOPS_ROOT_PATH . '/footer.php';
            break;
        case 'print':
            // iContent configuration
            require_once __DIR__ . '/class/page.class.php';
            $page = new page($parentModule);

            // Construction of the complete page
            $page->printableFormat();
            break;
        case 'rate':
            // Xoops configuration
            $GLOBALS['xoopsOption']['template_main'] = 'icontent_rate.html';
            require_once __DIR__ . '/include/functions.php';

            require_once XOOPS_ROOT_PATH . '/header.php';
            require_once __DIR__ . '/include/rate.php';
            require_once XOOPS_ROOT_PATH . '/footer.php';
            break;
        case 'top10':
            // Xoops configuration
            $GLOBALS['xoopsOption']['template_main'] = 'icontent_top10.html';

            // iContent configuration
            require_once __DIR__ . '/class/page.class.php';
            $page = new page($parentModule);

            require_once XOOPS_ROOT_PATH . '/header.php';
            $page->top10();
            require_once XOOPS_ROOT_PATH . '/footer.php';
            break;
    }
} else {
    // Allows to dipplay a page with a short url with only the page id like this : index.php?page=1

    // Especially usefull for the comments

    if (isset($_GET['page'])) {
        // Xoops configuration

        $GLOBALS['xoopsOption']['template_main'] = 'icontent_page.html';

        // iContent configuration

        require_once __DIR__ . '/class/page.class.php';

        $page = new page($parentModule);

        // Construction of the complete page

        require_once XOOPS_ROOT_PATH . '/header.php';

        $page->display();

        require_once XOOPS_ROOT_PATH . '/include/comment_view.php';

        require_once XOOPS_ROOT_PATH . '/footer.php';
    } else {
        // Builds the defaut page when there is no incontent used vars in the url

        // Xoops configuration

        $GLOBALS['xoopsOption']['template_main'] = 'icontent_index.html';

        // iContent configuration

        require_once __DIR__ . '/class/summary.class.php';

        require_once __DIR__ . '/class/shortcut.class.php';

        $summary = new summary($parentModule);

        $shortcut = new shortcut($parentModule);

        // Construction of the complete page

        require_once XOOPS_ROOT_PATH . '/header.php';

        $summary->display();

        $shortcut->display();

        require_once XOOPS_ROOT_PATH . '/footer.php';
    }
}
