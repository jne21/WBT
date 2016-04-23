<?php

namespace WBT;

use common\Registry;
use common\Application;
use common\TemplateFile as Template;

/**
 * Description of ExerciseSelectorView
 *
 * @author jne
 */
class StageEditView implements \common\iView
{
    static function get($data)
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $locales = LocaleManager::getLocales();
        $locale = $registry->get('locale');
        $tpl = new Template($registry->get('template_path').'stage_edit.htm');
        $tplTab = new Template($registry->get('template_path').'stage_edit_tab.htm');
        $tplTabContent = new Template($registry->get('template_path').'stage_edit_tab_content.htm');
        $tplMaterialItem = new Template($registry->get('template_path').'material_item.htm');
        $tplMaterialL10hHeaderItem = new Template($registry->get('template_path').'material_l10n_header_item.htm');
        $tplMaterialL10nItem = new Template($registry->get('template_path').'material_l10n_item.htm');
        $tplMaterialAddItem = new Template($registry->get('template_path').'material_add_item.htm');
        $tplMaterialAddL10nItem = new Template($registry->get('template_path').'material_add_l10n_item.htm');
        $tplso = new Template($registry->get('template_path') . 'select_option.htm');

        $stage = $data['stage'];
        $tabItems = '';
        $tabContentItems = '';
        $materialL10nItems = '';

        foreach ($locales as $localeId=>$localeData) {
            $tabItems .= $tplTab->apply([
                'localeId' => $localeId,
                'name' => $localeData['name'],
                #'selected' => $locale==$admin->locale
            ]);
            $tabContentItems .= $tplTabContent->apply([
                'name'        => htmlspecialchars($stage->l10n->get('name', $localeId)),
                'meta'        => htmlspecialchars($stage->l10n->get('meta', $localeId)),
                'description' => htmlspecialchars($stage->l10n->get('description', $localeId)),
                'brief'       => htmlspecialchars($stage->l10n->get('brief', $localeId)),
                'url'         => htmlspecialchars($stage->l10n->get('url', $localeId)),
                'title'       => htmlspecialchars($stage->l10n->get('title', $localeId)),
                'localeId'    => $localeId
            ]);
            $materialL10nHeaderItems .= $tplMaterialL10hHeaderItem->apply ([
                'locale' => $localeData['name']
            ]);
        } 

        $materialItems = '';
        foreach ($data['materials'] as $materialId=>$material) {
            $materialL10nItems = '';
            foreach ($locales as $localeId=>$localeData) {
                $materialL10nItems .= $tplMaterialL10nItem->apply([
                    'localeId' => $localeId,
                    'materialId' => $material->id,
                    'mimeType' => $material->l10n->get('mimeType', $localeId)
                ]);
            }
            $materialItems .= $tplMaterialItem->apply([
                'id' => $materialId,
                'hash' => $material->hash,
                'name' => $material->l10n->get('name', $registry->get('locale')),
                'description' => $material->l10n->get('description', $registry->get('locale')),
                'l10nItems' => $materialL10nItems
            ]);
        }
        $materialAddL10nItems = '';
        foreach ($locales as $localeId=>$localeData) {
            $materialAddL10nItems .= $tplMaterialAddL10nItem->apply([
                'materialId' => 0,
                'localeId' => $localeId
            ]);
        }
        $materialItems = $tplMaterialAddItem->apply([
            'l10nItems' => $materialAddL10nItems
        ]);

        if (!$stage->id) {
            $exerciseItems = '';
            foreach ($data['exercises'] as $exerciseId => $exercise) {
                $exerciseItems .= $tplso->apply([
                    'name' => $exercise->name,
                    'value' => $exerciseId,
                    'selected' => FALSE
                ]);
            }
        }

        return $tpl->apply([
            'id' => $stage->id,
            'lessonId' => $data['lessonId'],
            'name' => $stage->name,
            'config' => $stage->settings,
            'tabItems' => $tabItems,
            'tabContentItems' => $tabContentItems,
            'materialL10nHeaderItems' => $materialL10nHeaderItems,
            'materialItems' => $materialItems,
            'exerciseName' => $data['exerciseName'],
            'exerciseItems' => $exerciseItems,
            'site_root' => $application->siteRoot
        ]);
    }
}
