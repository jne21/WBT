<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
use common\Admin;
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
        $i18n = new I18n($registry->get('i18n_path') . 'course.xml');

        $data = [
            'ownerList' => Admin::getList(),
            'list' => Course::getList(Course::ALL)
        ];

        $renderer = new Renderer(Page::MODE_NORMAL);
        $pTitle = $i18n->get('title');
        $renderer->page
            ->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content', CourseListView::get($data));

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

            $data = [
                'course' => $course,
                'lessons' => Lesson::getList($course->id),
                'owner' => $owner,
            ];

            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get(
                $course->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', CourseEditView::get($data));

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