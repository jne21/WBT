<?php

namespace WBT;

use common\Registry;
use common\Application;
use common\TemplateFile as Template;

/**
 * Description of CourseListView
 *
 * @author jne
 */
class CourseListView implements \common\iView
{
    static function get($data)
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $localeId = $registry->get('locale');
        $localeData = LocaleManager::getLocaleData($localeId);
        $tpl = new Template($registry->get('template_path') . 'course.htm');
        $tpli = new template($registry->get('template_path') . 'course_item.htm');

        $listItems = '';
        foreach ($data['list'] as $item) {
            $listItems .= $tpli->apply([
                'id' => $item->id,
                'name' => $item->l10n->get('name', $localeId),
                'owner' => $data['ownerList'][$item->ownerId]->name,
                'dateCreate' => date($localeData['dateFormat'], $item->dateCreate),
                'dateUpdate' => $item->dateUpdate ? date($localeData['dateFormat'], $item->dateUpdate) : '',
                'active' => $item->state,
                'rights' => $item->rights
            ]);
        }

        return $tpl->apply([
            'items' => $listItems,
            'site_root' => $application->siteRoot
        ]);
    }
}