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
                $this->getList();eclipse
                break;
        }
    }

    function getList() {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
        $i18n = new i18n($registry->get('i18n_path').'router.xml');
        $tpl  = new Template($registry->get('template_path').'router.htm');
        $tpli = new Template($registry->get('template_path').'router_item.htm');

        $list = Router::getList();
        $cnt = count($list);
        $listItems = '';
        foreach ($list as $line) {
            $listItems .= $tpli->apply ([
                'id'         => $line->id,
                'name'       => $line->name,
                'url'        => $line->url,
                'controller' => $line->controller
            ]);
        }

        $renderer = new Renderer(Page::MODE_NORMAL);
        $pTitle = $i18n->get('title');
        $renderer->page->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content',
                $tpl->apply([
                    'items' => $listItems,
                    'site_root' => $application->siteRoot
                ])
            );
        $renderer->loadPage();
        $renderer->output();
    }

    function delete()
    {
        Router::delete(intval($_GET['id']));
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
            $tpl = new Template($registry->get('template_path').'router_edit.htm');

            $renderer = new Renderer(Page::MODE_NORMAL);
            $renderer->page->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content',
                    $tpl->apply([
                        'id'    => $router->id,
                        'name'  => htmlspecialchars($router->name),
                        'url'   => htmlspecialchars($router->url),
                        'controller'  => $router->controller,
                        'site_root' => $application->siteRooot
                    ])
                );
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