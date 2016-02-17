<?php

use common\Page;
use common\Registry;
use common\Application;
use common\Admin;
use CMS\I18n;
use CMS\RendererCMS as Renderer;

class AdminController {

    function __construct()
    {
        $application = Application::getInstance();
        switch ($application->segment[2]) {
            case 'delete':
                $this->delete();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'toggle':
                $this->toggle();
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
        $i18n = new I18n($registry->get('i18n_path') . 'admin.xml');

        $renderer = new Renderer(Page::MODE_NORMAL);

        $pTitle = $i18n->get('title');
        $renderer->page
            ->set('title', $pTitle)
            ->set('h1', $pTitle)
            ->set('content', AdminListView::get(['list'=>Admin::getList()]));

        $renderer->loadPage();
        $renderer->output();
    }

    function delete()
    {
        Admin::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $admin = new Admin($id = intval($_GET['id']));

        if ($_POST['action'] == 'save') { //d($_POST, 1);
            $admin->description  = trim($_POST['description']);
            $admin->email        = trim($_POST['email']);
            $admin->login        = trim($_POST['login']);
            $admin->name         = trim($_POST['name']);
            $admin->rights       = intval($_POST['rights']);
            $admin->state        = intval($_POST['state']=='on');
            $admin->locale       = trim($_POST['locale']);
            if ($_POST['password']) {
                $admin->setNewPassword($_POST['password']);
            }
            $admin->save();
            header('Location: /cms/admin/list');
            exit;
        }
        else {
            $i18n = new I18n($registry->get('i18n_path').'admin.xml');
            $pTitle = $i18n->get(
                $admin->id ?  'update_mode' : 'append_mode'
            );

            $renderer = new Renderer(Page::MODE_NORMAL);
            $renderer->page
                ->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content', AdminEditView::get(['admin'=>$admin]));

            $renderer->loadPage();
            $renderer->output();
        }
    }

    function toggle()
    {
        Admin::toggle(
            intval($_GET['id']),
            intval($_GET['act'])
        );
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }
}