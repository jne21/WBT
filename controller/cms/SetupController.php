<?php

use common\Setup;
use common\SetupItem;
use common\Page;
use common\Registry;
use common\Application;
use common\TemplateFile as Template;
use CMS\RendererCMS as Renderer;
use CMS\I18n;

class SetupController {

    function __construct()
    {
        $application = Application::getInstance();
        switch ($application->segment[2]) {
            case 'delete':
                $this->delete();
                break;
            case 'save':
                $this->save();
                break;
            case 'list':
            default:
                $this->getList();
                break;
        }
    }

    function getList() {
        $registry = Registry::getInstance();
        $application = Application::getInstance();
        $i18n = new I18n($registry->get('i18n_path') . 'setup.xml');
        $tpl = new Template($registry->get('template_path').'setup.htm');
        $tpli = new Template($registry->get('template_path').'setup_item.htm');

        $listItems = '';
        $setup = new Setup;
        foreach ($setup->getList() as $variable) {
            $listItems .= $tpli->apply ([
                'name'  => htmlspecialchars($variable->getProperty('name')),
                'value' => htmlspecialchars($variable->getProperty('value')),
                'desc'  => $variable->getProperty('description')
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

    function save() {
        if ($name = $_GET['name']) {
            $value = $_GET['value'].'';
            Registry::getInstance()->get('setup')->updateValue($name, $value);
        }
        echo $value;
    }
}