<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
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

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        $lesson = new Lesson($id);
        if (!$lesson->id) {
            $course = new Course(filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT));
        }
        else {
            $course = new Course($lesson->courseId);
        }
        if (!$course->id) {
            header('Location: /cms/course/list');
            exit;
        }
        if ('save' == filter_input(INPUT_POST, 'action')) {
            if (!$lesson->id) {
                $lesson->courseId = $course->id;
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
            $data['lesson'] = $lesson;
            $data['exercises'] = Exercise::getList();
            $data['stages'] = Stage::getList($lesson->id);
            
            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get(
                $lesson->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', LessonEditView::get($data));

            $renderer->loadPage();
            $renderer->output();
        }
    }

    function renumber()
    {
        $application = Application::getInstance();
        $courseId = intval($application->segment[3]);
        if ($courseId) {
            Lesson::renumberAll($_POST['order']);
//            Lesson::renumberAll($_POST['order'], NULL, NULL, '`course_id`='.$courseId);
            echo 'OK';
        }
        else {
            echo 'No CourseID';
        }
        exit;
    }
    
}