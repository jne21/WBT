<?php

namespace WBT;

use common\TemplateFile as Template;
use common\Registry;
use common\Application;

class ExerciseListView implements \common\iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $tpl  = new Template($registry->get('template_path').'exercise.htm');
        $tpli = new Template($registry->get('template_path').'exercise_item.htm');

        $list = $data['list'];
        $cnt = count($data['list']);
        $listItems = '';
        foreach ($list as $line) {
            $listItems .= $tpli->apply ([
                'id'             => $line->id,
                'name'           => $line->name,
                'description'    => $line->description,
                'controller'     => $line->controller,
                'configTemplate' => $line->configTemplate
            ]);
        }
        return $tpl->apply([
            'count' => $cnt,
            'items' => $listItems,
            'site_root' => $application->siteRoot
        ]);
    }
}