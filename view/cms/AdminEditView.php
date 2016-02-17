<?php

use common\TemplateFile as Template;
use common\Application;
use common\Registry;
use WBT\LocaleManager;

/**
 * Description of AdminEditView
 *
 * @author jne
 */
class AdminEditView implements \common\iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();

        $tplso = new Template($registry->get('template_path').'select_option.htm');
        $admin = $data['admin'];
        $localeItems = '';
        foreach ($locales as $locale=>$localeData) {
            $localeItems .= $tplso->apply([
                'name' => $localeData['name'],
                'value' => $locale,
                'selected' => $locale==$admin->locale
            ]);
        } 

        $localeData = LocaleManager::getLocaleData($registry->get('locale'));
        $tpl = new Template($registry->get('template_path').'admin_edit.htm');

        return $tpl->apply([
            'id' => $admin->id,
            'description' => htmlspecialchars($admin->description),
            'email' => htmlspecialchars($admin->email),
            'login' => htmlspecialchars($admin->login),
            'name' => htmlspecialchars($admin->name),
            'rights' => $admin->rights,
            'state' => $admin->state,
            'localeItems' => $localeItems,
            'dateCreate' => date($localeData['dateFormat'], $admin->dateCreate),
            'dateLogin' => $admin->dateLogin ? date($localeData['dateFormat'] . ' H:i', $admin->dateLogin) : '',
            'site_root' => $application->siteRoot
        ]);
    }
}
