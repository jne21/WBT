<?php
namespace CMS;
use \common\Page;

class RendererCMS extends \common\Renderer
{
    function __construct ($pageMode)
    {
        $this->page = new Page();
        $this->page->set('mode', $pageMode);
        $this->content = RendererCMSView::get(['pageMode'=>$pageMode]);
    }

    function output ()
    {
        $this->updateContent([
            'h1' => $this->page->get('h1'),
            'main_menu' => MainMenuView::get(['admin' => TRUE, 'operator' => TRUE]),
            'adminName' => $_SESSION['admin']['name'],
            'adminId' => $_SESSION['admin']['id'],
            // Render Globals
            'year_now' => date('Y')
        ]);
        parent::output();
    }
}
