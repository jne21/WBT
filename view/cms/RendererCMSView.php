<?php

namespace CMS;

use common\Page;
use common\Registry;
use common\TemplateFile;

/**
 * Description of RendererCMSView
 *
 * @author jne
 */
class RendererCMSView implements \common\iView
{
    static function get($data)
    {
        $registry = Registry::getInstance();
        switch ($data['pageMode']) {
            case Page::MODE_POPUP:
                $templateFileName = 'popup.htm';
                break;
            case Page::MODE_NORMAL:
            default:
                $templateFileName = 'main.htm';
        }
        $tpl = new TemplateFile($registry->get('template_path') . $templateFileName);
        return $tpl->getContent();
    }

}
