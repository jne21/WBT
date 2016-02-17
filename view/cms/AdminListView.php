<?php

use common\TemplateFile as Template;
use common\Registry;
use common\Application;
use CMS\I18n;
use WBT\LocaleManager;

/**
 * Description of AdminListView
 *
 * @author jne
 */
class AdminListView implements \common\iView {

    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $i18n = new I18n($registry->get('i18n_path') . 'admin.xml');
        $localeData = LocaleManager::getLocaleData($registry->get('locale'));
        $tpl = new Template($registry->get('template_path') . 'admin.htm');
        $tpli = new template($registry->get('template_path') . 'admin_item.htm');

        $listItems = '';

        foreach ($data['list'] as $item) {
            $listItems .= $tpli->apply([
                'id' => $item->id,
                'description' => $item->description,
                'email' => $item->email,
                'login' => $item->login,
                'name' => $item->name,
                'state' => $i18n->get('state'.$item->state),
                'rights' => $item->rights,
                'dateCreate' => date($localeData['dateFormat'], $item->dateCreate),
                'dateLogin' => ($item->dateLogin ? date($localeData['dateFormat'], $item->dateLogin) : ''),
                'locale' => $item->locale
            ]);
        }

        return $tpl->apply([
            'items' => $listItems,
            'site_root' => $application->siteRoot
        ]);
    }
}
