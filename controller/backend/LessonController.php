<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
use common\TemplateFile as Template;
use common\Page;
use CMS\I18n;
use WBT\Course;
use WBT\Lesson;
use WBT\Stage;

/**
 * Управление уроками курса
 *
 * @author jne
 */
class LessonController {

    function __construct()
    {
        $application = Application::getInstance();
#        $registry = Registry::getInstance();
        switch ($application->segment[2]) {
            case 'edit':
                $this->edit();
                break;
            case 'renumber':
                $this->renumber();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
        }
    }

    function delete()
    {
        Lesson::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();
        if ($id = intval($_GET['id']) || $courseId = intval($_GET['course_id'])) {
            if (!intval($_GET['id'])) {
                $course = new Course($courseId);
                if (!$courseId = $course->id) {
                    header('Location: /cms/course/list');
                    exit;
                }
            }
            $lesson = new Lesson($id);
            if ($_POST['action'] == 'save') { //d($_POST, 1);
                if (!$lesson->id) {
                    $lesson->courseId = $courseId;
                }
                foreach (array_keys($locales) as $localeId) {
                    $lesson->l10n->loadDataFromArray(
                        $localeId,
                        [
                            'name'        => trim($_POST['name_'        . $localeId]),
                            'meta'        => trim($_POST['meta_'        . $localeId]),
                            'description' => trim($_POST['description_' . $localeId]),
                            'brief'       => trim($_POST['brief_'       . $localeId]),
                            'url'         => trim($_POST['url_'         . $localeId]),
                            'title'       => trim($_POST['title_'       . $localeId])
                        ]
                    );
                }
                $lesson->save();
                header('Location: /cms/course/edit?id='.$lesson->courseId);
                exit;
            }
            else {
                $i18n = new I18n($registry->get('i18n_path').'lesson.xml');
                $tpl = new Template($registry->get('template_path').'lesson_edit.htm');
                $tplTab = new Template($registry->get('template_path').'lesson_edit_tab.htm');
                $tplTabContent = new Template($registry->get('template_path').'lesson_edit_tab_content.htm');
                $tplStageItem = new Template($registry->get('template_path').'stage_item.htm');

                $tabItems = '';
                $tabContentItems = '';
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
                $stageItems = '';
                foreach (Stage::getList($this->id) as $stageId=>$stage) {
                    $stageItems .= $tplStageItem->apply([
                        'id' => $stageId,
                        'order' => $stage->order,
                        'name' => $stage->l10n->get('name', $registry->get('locale'))
                    ]);
                }

                $renderer = new Renderer(Page::MODE_NORMAL);
                $pTitle = $i18n->get(
                    $lesson->id ?  'update_mode' : 'append_mode'
                );
                $renderer->page->set('title', $pTitle)
                    ->set('h1', $pTitle)
                    ->set('content',
                        $tpl->apply(
                            array(
                                'id' => $lesson->id,
                                'courseId' => $courseId,
                                'name' => $lesson->l10n->get('name', $locale),
                                'tabItems' => $tabItems,
                                'tabContentItems' => $tabContentItems,
                                'stageItems' => $stageItems,
                                'site_root' => $application->siteRoot
                            )
                        )
                    );

                $renderer->loadPage();
                $renderer->output();
            }
        }
        else {
            header('Location: /cms/course/list');
            exit;
        }
    }

    function renumber()
    {
        Stage::renumberAll($_POST['order'], NULL, NULL, '`lesson_id`='.$lesson->id);
        echo 'OK';
    }
    
}