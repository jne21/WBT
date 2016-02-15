<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
use common\Admin;
use common\TemplateFile as Template;
use common\Page;
use CMS\I18n;
use WBT\Course;

/**
 * Управление курсами
 *
 * @author jne
 */
class CourseController {

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
            case 'toggle':
                $this->toggle();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'list':
            default:
                $this->getList();
                break;
        }
    }

    function getList()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $i18n = new I18n($registry->get('i18n_path') . 'course.xml');
        $localeId = $registry->get('locale');
        $localeData = LocaleManager::getLocaleData($localeId);
        $tpl = new Template($registry->get('template_path') . 'course.htm');
        $tpli = new template($registry->get('template_path') . 'course_item.htm');

        $listItems = '';
        $ownerList = Admin::getList();
        foreach (Course::getList(Course::ALL) as $item) {
            $listItems .= $tpli->apply([
                'id' => $item->id,
                'name' => $item->l10n->get('name', $localeId),
                'owner' => $ownerList[$item->ownerId]->name,
                'dateCreate' => date($localeData['dateFormat'], $item->dateCreate),
                'dateUpdate' => $item->dateUpdate ? date($localeData['dateFormat'], $item->dateUpdate) : '',
                'active' => $item->state,
                'rights' => $item->rights
            ]);
        }

        $renderer = new Renderer(Page::MODE_NORMAL);
        $pTitle = $i18n->get('title');
        $renderer->page->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content',
                $tpl->apply(
                    array(
                        'items' => $listItems,
                        'site_root' => $application->siteRoot
                    )
                )
            );

        $renderer->loadPage();
        $renderer->output();
    }

    function delete()
    {
        Course::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();

        $course = new Course(intval($_GET['id']));
        $owner =  new Admin($course->ownerId);
        if ($_POST['action'] == 'save') { //d($_POST, 1);
            $course->ownerId     = intval($_SESSION['admin']['id']);
            $course->state       = intval($_POST['state']=='on');
            foreach (array_keys($locales) as $localeId) {
                $course->l10n->loadDataFromArray(
                    $localeId,
                    [
                        'name'        => trim($_POST['name_'        . $localeId]),
                        'meta'        => trim($_POST['meta_'        . $localeId]),
                        'description' => trim($_POST['description_' . $localeId]),
                        'brief'       => trim($_POST['brief_'       . $localeId]),
                        'url'         => trim($_POST['url_'         . $localeId]),
                        'title'       => trim($_POST['title_'       . $localeId]),
                        'state'       => intval($_POST['state_'     . $localeId])
                    ]
                );
            }
            $course->save();
            header('Location: /cms/course/list');
            exit;
        }
        else {
            $i18n = new I18n($registry->get('i18n_path').'course.xml');
            $tpl = new Template($registry->get('template_path').'course_edit.htm');
            $tplTab = new Template($registry->get('template_path').'course_edit_tab.htm');
            $tplTabContent = new Template($registry->get('template_path').'course_edit_tab_content.htm');
            $tplLessonItem = new Template($registry->get('template_path').'lesson_item.htm');

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
            foreach (Lesson::getList($course->id) as $lessonId=>$lesson) {
                $lessonItems .= $tplLessonItem->apply([
                    'id' => $lessonId,
                    'order' => $lesson->order,
                    'name' => $lesson->l10n->get('name', $registry->get('locale'))
                ]);
            }

            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get(
                $course->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content',
                    $tpl->apply(
                        array(
                            'id' => $course->id,
                            'state' => $course->state,
                            'ownerName' => $owner->name ? $owner->name : $_SESSION['admin']['name'],
                            'tabItems' => $tabItems,
                            'tabContentItems' => $tabContentItems,
                            'dateCreate' => $admin->dateCreate ? date($localeData['dateFormat'], $admin->dateCreate) : '',
                            'dateUpdate' => $admin->dateUpdate ? date($localeData['dateFormat'] . ' H:i', $admin->dateUpdate) : '',
                            'lessonItems' => $lessonItems,
                            'site_root' => $application->siteRoot
                        )
                    )
                );

            $renderer->loadPage();
            $renderer->output();
        }
    }

    function toggle()
    {
        Course::toggle(
            intval($_GET['id']),
            intval($_GET['act'])
        );
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function renumber()
    {
        Course::renumberAll($_POST['order']);
        echo 'OK';
    }
    
}