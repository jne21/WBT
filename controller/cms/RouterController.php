<?php

use common\TemplateFile as Template;
use common\Page;
use common\Registry;
use common\Application;
use common\Router;
use CMS\I18n;
use CMS\RendererCMS as Renderer; 

class RouterController {

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
            case 'renumber':
                $this->renumber();
                break;
            case 'list':
            default:
                $this->getList();
                break;
        }
    }

    function getList() {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $i18n = new i18n($registry->get('i18n_path').'router.xml');

        $renderer = new Renderer(Page::MODE_NORMAL);
        $pTitle = $i18n->get('title');
        $renderer->page
            ->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content', RouterListView::get(['list'=>Router::getList()]));
        $renderer->loadPage();
        $renderer->output();
    }

    function delete()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); //, ['options'=>['default'=>0]]
        if ($id) {
            Router::delete($id);
        }
        header('location: /cms/router');
    }

    function edit()
    {
        $router = new Router(intval($_GET['id']));
        if ($_POST['action']=='save') {
            $router->name  = trim($_POST['name']);
            $router->url   = trim($_POST['url']);
            $router->controller  = trim($_POST['controller']);
            $router->save();
            header('Location: /cms/router');
            exit;
        }
        else {
            $application = Application::getInstance();
            $registry = Registry::getInstance();
            $i18n = new I18n($registry->get('i18n_path').'router.xml');
            $pTitle = $i18n->get( $id ? 'update_mode' : 'append_mode' );

            $renderer = new Renderer(Page::MODE_NORMAL);
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', RouterEditView::get(['router'=>$router]));
            $renderer->loadPage();
            $renderer->output();
        }
    }

    function renumber()
    {
        Router::renumberAll($_POST['order']);
        echo 'OK';
    }

}