<?php

namespace CMS;

use common\Registry;
use common\TemplateFile;
/**
 * Description of MainMenuView,php
 *
 * @author jne
 */
class MainMenuView implements \common\iView
{
    static function get($data) {
        $registry = Registry::getInstance();
        $tplMainMenu = new TemplateFile($registry->get('template_path') . 'main_menu.htm');
        return $tplMainMenu->apply([
            'admin' => $data['admin'],
            'operator' => $data['operator']
        ]);
    }
}
