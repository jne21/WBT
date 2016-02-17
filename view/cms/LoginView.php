<?php

use common\TemplateFile as Template;
use common\Registry;
use common\Application;

/**
 * Description of LoginView
 *
 * @author jne
 */
class LoginView {
    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $tpl = new Template($registry->get('template_path').'login.htm');
        return $tpl->apply([
            'message' => $data['message'],
            'loginError' => $_SESSION['login_error'],
            'site_root' => $application->siteRoot
        ]);
    }
}
