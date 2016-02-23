<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
use common\Page;
use CMS\I18n;
use WBT\Lesson;
use WBT\Stage;
use WBT\Material;

/**
 * Управление упражнениями урока
 *
 * @author jne
 */
class StageController {

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
                $this->list();
        }
    }

    function delete()
    {
        Stage::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $locales = LocaleManager::getLocales();

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $lesson = new Lesson(filter_input(INPUT_GET, 'lesson_id', FILTER_VALIDATE_INT));
            $lessonId = $lesson->id;
            if (!$lessonId) {
                header('Location: /cms/course/list');
                exit;
            }
        }

        $stage = new Stage($id);
        if ($_POST['action'] == 'save') { //d($_POST, 1);
            if (!$stage->id) {
                $exercise = new Exercise(filter_input(INPUT_POST, 'exercise_id', FILTER_VALIDATE_INT));
                $exerciseId = $exercise->id;
//die(print_r($_POST));
                if (!$exerciseId) {
                    header('Location: /cms/course/list');
                    exit;
                }
                $stage->lessonId = $lessonId;
                $stage->exerciseId = $exerciseId;
                $stage->settings = $exercise->configTemplate;
            }
            foreach (array_keys($locales) as $localeId) {
                $stage->l10n->loadDataFromArray(
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
            $stage->name = filter_input(INPUT_POST, 'name');
            $stage->save();
            header('Location: /cms/lesson/edit?id='.$stage->lessonId);
            exit;
        }
        else {
            $i18n = new I18n($registry->get('i18n_path').'stage.xml');
            $data['stage'] = $stage;
            $data['materials'] = Material::getList($this->id);
            if ($stage->id) {
                $exercise = new Exercise($stage->exerciseId);
                $data['exerciseName'] = $exercise->name;
            } else {
                $data['exercises'] = Exercise::getList();
            }
            
            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get(
                $lesson->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', StageEditView::get($data));

            $renderer->loadPage();
            $renderer->output();
        }
    }

    function renumber()
    {
        $application = Application::getInstance();
        $lessonId = intval($application->segment[3]);
        if ($lessonId) {
            Stage::renumberAll($_POST['order']);
            echo 'OK';
        }
        else {
            echo 'No LessonID';
        }
        exit;
    }
    
}