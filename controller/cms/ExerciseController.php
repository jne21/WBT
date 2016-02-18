<?php

namespace WBT;

use common\Registry;
use CMS\RendererCMS as Renderer;
use common\Application;
use common\Page;
use CMS\I18n;
use WBT\Exercise;

/**
 * Управление типовыми упражнениями
 *
 * @author jne
 */
class ExerciseController {

    function __construct()
    {
        $application = Application::getInstance();
        switch ($application->segment[2]) {
            case 'edit':
                $this->edit();
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
        $i18n = new I18n($registry->get('i18n_path') . 'exercise.xml');

        $data = [
            'list' => Exercise::getList()
        ];

        $renderer = new Renderer(Page::MODE_NORMAL);
        $pTitle = $i18n->get('title');
        $renderer->page
            ->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content', ExerciseListView::get($data));

        $renderer->loadPage();
        $renderer->output();
    }

    function delete()
    {
        Exercise::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();

        $exercise = new Exercise(intval($_GET['id']));
        if ($_POST['action'] == 'save') { //d($_POST, 1);
            $exercise->name           = trim($_POST['name']);
            $exercise->description    = trim($_POST['description']);
            $exercise->controller     = trim($_POST['controller']);
            $exercise->configTemplate = trim($_POST['config_template']);
            $exercise->save();
            header('Location: /cms/exercise/list');
            exit;
        }
        else {
            $i18n = new I18n($registry->get('i18n_path').'exercise.xml');

            $data = [
                'exercise' => $exercise
            ];

            $renderer = new Renderer(Page::MODE_NORMAL);
            $pTitle = $i18n->get(
                $exercise->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', ExerciseEditView::get($data));

            $renderer->loadPage();
            $renderer->output();
        }
    }

}