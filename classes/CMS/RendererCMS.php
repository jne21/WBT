<?php
namespace CMS;
use \common\Registry;
use \common\TemplateFile;
use \common\Template;
use \common\Page;

class RendererCMS extends \common\Renderer
{

    function __construct ($pageMode)
    {
        $registry = Registry::getInstance();
        $this->page = new Page();
        $this->page->set('mode', $pageMode);
        switch ($pageMode) {
            case Page::MODE_POPUP:
                $templateFileName = 'popup.htm';
                break;
            case Page::MODE_NORMAL:
            default:
                $templateFileName = 'main.htm';
        }
        $tpl = new TemplateFile(
                $registry->get('template_path') . $templateFileName);
        $this->content = $tpl->getContent();
    }

    function output ()
    {
        $registry = Registry::getInstance();
        $tplMainMenu = new TemplateFile(
                $registry->get('template_path') . 'main_menu.htm');
        $this->updateContent(
                [
                        'h1' => $this->page->get('h1'),
                        'main_menu' => $tplMainMenu->apply(
                                [
                                        'admin' => TRUE,
                                        'operator' => TRUE
                                ]),
                                'adminName' => $_SESSION['admin']['name'],
                                'adminId' => $_SESSION['admin']['id'],
                        // Render Globals
                        'year_now' => date('Y')
                ]);
        parent::output();
    }
}
