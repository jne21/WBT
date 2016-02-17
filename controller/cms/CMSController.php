<?php

use common\Admin;
use common\Application;
use common\Page;
use common\Registry;
use CMS\RendererCMS as Renderer;
use CMS\I18n;
use WBT\CourseController;
use WBT\LessonController;

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
                case 'course':
                    $controller = new CourseController;
                    break;
                case 'lesson':
                    $controller = new LessonController;
                    break;
                case 'logout':
                    unset($_SESSION['admin']);
                    header('Location: /cms');
                    break;
                default:
                    $registry = Registry::getInstance();
                    $i18n = new I18n($registry->get('i18n_path') . 'cms.xml');

                    $renderer = new Renderer(Page::MODE_NORMAL);

                    $pTitle = $i18n->get('title');
                    $renderer->page
                        ->set('title', $pTitle)
                        ->set('h1', $pTitle)
                        ->set('content', '');

                    $renderer->loadPage();
                    $renderer->output();
            }
        }
        else {
            // authent
            $controller = new LoginController;
        }
    }
}