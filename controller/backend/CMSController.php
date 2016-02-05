<?php

use common\Application;
use common\Admin;

class CMSController {
    function __construct()
    {
        $application = Application::getInstance();
        if ($_SESSION['admin']['id']) {
            // main menu
            switch ($application->segment[1]) {
                case 'admin':
                    $controller = new AdminController;
                    break;
                case 'router':
                    $controller = new RouterController;
                    break;
                case 'redirect':
                    $controller = new RedirectController;
                    break;
                case 'setup':
                    $controller = new SetupController;
                    break;
                case 'logout':
                    unset($_SESSION['admin']);
                    header('Location: /cms');
                    break;
                default:
                    echo 'cms default';
            }
        }
        else {
            // authent
            $controller = new LoginController;
        }
    }
}