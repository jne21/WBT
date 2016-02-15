<?php

use common\TemplateFile as Template;
use common\Registry;
use common\Application;
use common\iView;
use CMS\I18n;

class RouterListView implements iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $tpl  = new Template($registry->get('template_path').'router.htm');
        $tpli = new Template($registry->get('template_path').'router_item.htm');

        $list = $data['list'];
        $cnt = count($data['list']);
        $listItems = '';
        foreach ($list as $line) {
            $listItems .= $tpli->apply ([
                'id'         => $line->id,
                'name'       => $line->name,
                'url'        => $line->url,
                'controller' => $line->controller
            ]);
        }
        return $tpl->apply([
            'count' => $cnt,
            'items' => $listItems,
            'site_root' => $application->siteRoot
        ]);
    }
}