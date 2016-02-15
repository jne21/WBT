<?php

use common\TemplateFile as Template;
use common\Page;
use common\Registry;
use common\Application;
use common\Admin;
use CMS\I18n;
use CMS\RendererCMS as Renderer;
use system\LoginError;

class LoginController
{
    function __construct()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $i18n = new I18n($registry->get('i18n_path') . 'admin.xml');

        if ($_POST['action']=='login') {
            if ($_POST['login'].$_POST['password']) {
                if (LoginError::isBlocked()) {
                    $message = $i18n->get('error_limit_exceeded');
                }
                else {
                    $admin = Admin::getInstance($_POST['login'], $_POST['password']);
                    if ($admin->id) {
                        Admin::setProperty($admin->id, 'date_login', date('Y-m-d H:i:s'));
                        $_SESSION['admin'] = [
                            'id' => $admin->id,
                            'locale' => $admin->locale,
                            'name' => $admin->name
                        ];
                        unset($_SESSION['login_error']);
                        header("Location: /cms");
                    }
                    else {
                        LoginError::register($_POST['login'], $_POST['password']);
                        $message = $i18n->get('login_error');
                        $_SESSION['login_error'] = 1;
                    }
                }
            }
            else {
                $message = $i18n->get('empty_login_of_password');
            }
        }
        else {
            $tpl = new Template($registry->get('template_path').'login.htm');
            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get('login_title');
            $renderer->page->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content',
                    $tpl->apply(
                        array(
                            'items' => $listItems,
                            'message' => $message,
                            'loginError' => $_SESSION['login_error'],
                            'site_root' => $application->siteRoot
                    )
                )
            );

            $renderer->loadPage();
            $renderer->output();
        }
    }

}
