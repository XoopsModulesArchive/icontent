<?php

// -------------------------------------------------------------------------
// Author: VIVI
// email: alban.montaigu@wanadoo.fr
// Site: http://www.vivihome.net
// -------------------------------------------------------------------------

// Inclusion of the xoops's parameters and admin config
require_once __DIR__ . '/include/cp_header.php';

// iContent general conficuration
$parentModule = 'icontent';

// iContent admin configuration
require_once __DIR__ . '/class/admin.class.php';
$admin = new admin($parentModule);

// Page management
// A completly different page is builded according to the operation
// It allows to include only the needed libraries and functions to execute the operation
if (isset($_GET['op'])) {
    switch ($_GET['op']) {
        default:
            $admin->header('all_header');
            $admin->footer();
            break;
        case 'directoryAdmin':
            // Page header
            $admin->header();

            // iContent configuration
            require_once __DIR__ . '/class/directoryAdmin.class.php';
            $directoryAdmin = new directoryAdmin($parentModule);

            // Admin page title
            echo '<h4>' . _IC_DIRECTORIESADMIN . "</h4>\n";

            if (isset($_GET['cop'])) {
                // Admin page content

                switch ($_GET['cop']) {
                    case 'display':
                        $directoryAdmin->display();
                        break;
                    case 'activate':
                        $directoryAdmin->activate();
                        break;
                    case 'desactivation':
                        $directoryAdmin->desactivation();
                        break;
                    case 'configuration':
                        $directoryAdmin->configuration();
                        break;
                    case 'createHomePage':
                        $directoryAdmin->createHomePage();
                        break;
                    case 'creation':
                        $directoryAdmin->creation();
                        break;
                }
            }

            // Page footer
            $admin->footer();
            break;
        case 'pageAdmin':
            // iContent configuration
            require_once __DIR__ . '/class/pageCompiler.class.php';
            require_once __DIR__ . '/class/pageAdmin.class.php';
            $pageCompiler = new pageCompiler($parentModule, 'html');
            $pageAdmin = new pageAdmin($parentModule);

            if (isset($_GET['cop'])) {
                // Admin page content

                switch ($_GET['cop']) {
                    default:
                        $admin->header();
                        $admin->footer();
                        break;
                    case 'display':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->display();
                        $admin->footer();
                        break;
                    case 'activate':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->activate();
                        $admin->footer();
                        break;
                    case 'desactivation':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->desactivation();
                        $admin->footer();
                        break;
                    case 'configuration':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->configuration();
                        $admin->footer();
                        break;
                    case 'modification':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        // Wysiwig editor library
                        $spaw_root = 'include/wysiwyg/';
                        require_once __DIR__ . '/include/wysiwyg/spaw_control.class.php';

                        // Page content
                        $pageAdmin->modification();
                        $admin->footer();
                        break;
                    case 'creation':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        // Wysiwig editor library
                        $spaw_root = 'include/wysiwyg/';
                        require_once __DIR__ . '/include/wysiwyg/spaw_control.class.php';

                        // Page content
                        $pageAdmin->creation();
                        $admin->footer();
                        break;
                    case 'update':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->update();
                        $admin->footer();
                        break;
                    case 'download':
                        $pageAdmin->download();
                        break;
                    case 'upload':
                        $admin->header();

                        // Admin page title
                        echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                        $pageAdmin->upload();
                        $admin->footer();
                        break;
                }
            } else {
                $admin->header();

                // Admin page title

                echo '<h4>' . _IC_PAGESADMIN . "</h4>\n";

                $admin->footer();
            }
            break;
        case 'shortcutAdmin':
            // Page header
            $admin->header();

            // iContent configuration
            require_once __DIR__ . '/class/shortcutAdmin.class.php';
            $shortcutAdmin = new shortcutAdmin($parentModule);

            // Admin page title
            echo '<h4>' . _IC_SHORTCUTSADMIN . "</h4>\n";

            if (isset($_GET['cop'])) {
                // Admin page content

                switch ($_GET['cop']) {
                    case 'display':
                        $shortcutAdmin->display();
                        break;
                    case 'creation':
                        $shortcutAdmin->creation();
                        break;
                    case 'configuration':
                        $shortcutAdmin->configuration();
                        break;
                    case 'deleting':
                        $shortcutAdmin->deleting();
                        break;
                }
            }

            // Page footer
            $admin->footer();
            break;
    }
} else {
    $admin->header('all_header');

    $admin->footer();
}
