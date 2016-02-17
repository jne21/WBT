<?php

namespace WBT;

use common\Registry;
use common\Application;
use common\TemplateFile as Template;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CourseEditView
 *
 * @author jne
 */
class CourseEditView implements \common\iView
{
    static function get($data)
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();
        $tpl = new Template($registry->get('template_path').'course_edit.htm');
        $tplTab = new Template($registry->get('template_path').'course_edit_tab.htm');
        $tplTabContent = new Template($registry->get('template_path').'course_edit_tab_content.htm');
        $tplLessonItem = new Template($registry->get('template_path').'lesson_item.htm');
        $course = $data['course'];

        $tabItems = '';
        $tabContentItems = '';
        foreach ($locales as $localeId=>$localeData) {
            $tabItems .= $tplTab->apply([
                'localeId' => $localeId,
                'name' => $localeData['name'],
                #'selected' => $locale==$admin->locale
            ]);
            $tabContentItems .= $tplTabContent->apply([
                'name'        => htmlspecialchars($course->l10n->get('name', $localeId)),
                'meta'        => htmlspecialchars($course->l10n->get('meta', $localeId)),
                'description' => htmlspecialchars($course->l10n->get('description', $localeId)),
                'brief'       => htmlspecialchars($course->l10n->get('brief', $localeId)),
                'url'         => htmlspecialchars($course->l10n->get('url', $localeId)),
                'title'       => htmlspecialchars($course->l10n->get('title', $localeId)),
                'state'       => $course->l10n->get('state', $localeId),
                'localeId'    => $localeId
            ]);
        } 
        $lessonItems = '';
        foreach ($data['lessons'] as $lessonId=>$lesson) {
            $lessonItems .= $tplLessonItem->apply([
                'id' => $lessonId,
                'order' => $lesson->order,
                'name' => $lesson->l10n->get('name', $locale)
            ]);
        }

        return $tpl->apply([
            'id' => $course->id,
            'courseName' => $course->l10n->get('name', $locale),
            'state' => $course->state,
            'ownerName' => $data['owner']->name ? $data['owner']->name : $_SESSION['admin']['name'],
            'tabItems' => $tabItems,
            'tabContentItems' => $tabContentItems,
            'dateCreate' => $course->dateCreate ? date($localeData['dateFormat'], $course->dateCreate) : '',
            'dateUpdate' => $course->dateUpdate ? date($localeData['dateFormat'] . ' H:i', $course->dateUpdate) : '',
            'lessonItems' => $lessonItems,
            'site_root' => $application->siteRoot
        ]);
    }
}
