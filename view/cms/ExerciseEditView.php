<?php

namespace WBT;

use common\TemplateFile as Template;
use common\Registry;
use common\Application;

class ExerciseEditView implements \common\iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();

        $exercise = $data['exercise'];
        $tpl = new Template($registry->get('template_path').'exercise_edit.htm');
        return $tpl->apply([
            'id'              => $exercise->id,
            'name'            => htmlspecialchars($exercise->name),
            'description'     => htmlspecialchars($exercise->description),
            'controller'      => htmlspecialchars($exercise->controller),
            'config_template' => htmlspecialchars($exercise->configTemplate),
            'site_root'       => $application->siteRooot
        ]);
    }
}