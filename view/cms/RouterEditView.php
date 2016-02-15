<?php

use common\TemplateFile as Template;
use common\Registry;
use common\Application;
use CMS\I18n;

class RouterEditView implements common\iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();

        $router = $data['router'];
        $tpl = new Template($registry->get('template_path').'router_edit.htm');
        return $tpl->apply([
            'id'         => $router->id,
            'name'       => htmlspecialchars($router->name),
            'url'        => htmlspecialchars($router->url),
            'controller' => $router->controller,
            'site_root'  => $application->siteRooot
        ]);
    }
}