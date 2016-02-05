<?php

use common\TemplateFile as Template;
use common\Page;
use common\Registry;
use common\Application;
use common\Admin;
use CMS\I18n;
use CMS\RendererCMS as Renderer;
use WBT\LocaleManager;

class AdminController {

    function __construct()
    {
        $application = Application::getInstance();
        $registry = Registry::getInstance();
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
        $application = Application::getInstance();
        $i18n = new I18n($registry->get('i18n_path') . 'admin.xml');
        $localeData = LocaleManager::getLocaleData($registry->get('locale'));
        $tpl = new Template($registry->get('template_path') . 'admin.htm');
        $tpli = new template($registry->get('template_path') . 'admin_item.htm');

        $listItems = '';

        foreach (Admin::getList() as $item) {
            $listItems .= $tpli->apply(
                array(
                    'id' => $item->id,
                    'description' => $item->description,
                    'email' => $item->email,
                    'login' => $item->login,
                    'name' => $item->name,
                    'state' => $i18n->get('state'.$item->state),
                    'rights' => $item->rights,
                    'dateCreate' => date($localeData['dateFormat'], $item->dateCreate),
                    'dateLogin' => ($item->dateLogin ? date($localeData['dateFormat'], $item->dateLogin) : ''),
                    'locale' => $item->locale
                )
            );
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
        admin::delete(intval($_GET['id']));
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

    function edit()
    {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $admin = new Admin($id = intval($_GET['id']));
        $locale = $registry->get('locale');
        $locales = LocaleManager::getLocales();

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
            $tplso = new Template($registry->get('template_path').'select_option.htm');
            $localeItems = '';
            foreach ($locales as $locale=>$localeData) {
                $localeItems .= $tplso->apply([
                    'name' => $localeData['name'],
                    'value' => $locale,
                    'selected' => $locale==$admin->locale
                ]);
            } 

            $localeData = LocaleManager::getLocaleData($registry->get('locale'));
            $tpl = new Template($registry->get('template_path').'admin_edit.htm');

            $renderer = new Renderer(Page::MODE_NORMAL);

            $pTitle = $i18n->get(
                $admin->id ?  'update_mode' : 'append_mode'
            );
            $renderer->page->set('title', $pTitle)
                ->set('h1', $pTitle)
                ->set('content',
                    $tpl->apply(
                        array(
                            'id' => $admin->id,
                            'description' => htmlspecialchars($admin->description),
                            'email' => htmlspecialchars($admin->email),
                            'login' => htmlspecialchars($admin->login),
                            'name' => htmlspecialchars($admin->name),
                            'rights' => $admin->rights,
                            'state' => $admin->state,
                            'localeItems' => $localeItems,
                            'dateCreate' => date($localeData['dateFormat'], $admin->dateCreate),
                            'dateLogin' => $admin->dateLogin ? date($localeData['dateFormat'] . ' H:i', $admin->dateLogin) : '',
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
        Admin::toggle(
            intval($_GET['id']),
            intval($_GET['act'])
        );
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }
}