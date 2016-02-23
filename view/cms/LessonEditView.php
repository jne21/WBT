<?php

namespace WBT;

use common\Registry;
use common\Application;
use common\TemplateFile as Template;

/**
 * Description of LessonEditView
 *
 * @author jne
 */
class LessonEditView implements \common\iView
{
    static function get($data)
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $tpl = new Template($registry->get('template_path').'lesson_edit.htm');
        $tplTab = new Template($registry->get('template_path').'lesson_edit_tab.htm');
        $tplTabContent = new Template($registry->get('template_path').'lesson_edit_tab_content.htm');
        $tplStageItem = new Template($registry->get('template_path').'stage_item.htm');
        $locales = LocaleManager::getLocales();
        $locale = $registry->get('locale');

        $tabItems = '';
        $tabContentItems = '';

        $lesson = $data['lesson'];
        foreach ($locales as $localeId=>$localeData) {
            $tabItems .= $tplTab->apply([
                'localeId' => $localeId,
                'name' => $localeData['name'],
                #'selected' => $locale==$admin->locale
            ]);
            $tabContentItems .= $tplTabContent->apply([
                'name'        => htmlspecialchars($lesson->l10n->get('name', $localeId)),
                'meta'        => htmlspecialchars($lesson->l10n->get('meta', $localeId)),
                'description' => htmlspecialchars($lesson->l10n->get('description', $localeId)),
                'brief'       => htmlspecialchars($lesson->l10n->get('brief', $localeId)),
                'url'         => htmlspecialchars($lesson->l10n->get('url', $localeId)),
                'title'       => htmlspecialchars($lesson->l10n->get('title', $localeId)),
                'localeId'    => $localeId
            ]);
        } 

        $exercises = $data['exercises'];
        $stageItems = '';
        foreach ($data['stages'] as $stageId=>$stage) {
            $stageItems .= $tplStageItem->apply([
                'id' => $stageId,
                'order' => $stage->order,
                'name' => $stage->name,
                'exerciseName' => $exercises[$stage->exerciseId]->name
            ]);
        }

        return $tpl->apply([
            'id' => $lesson->id,
            'courseId' => $data['courseId'],
            'name' => $lesson->l10n->get('name', $locale),
            'tabItems' => $tabItems,
            'tabContentItems' => $tabContentItems,
            'stageItems' => $stageItems,
            'site_root' => $application->siteRoot
        ]);
    }
}

