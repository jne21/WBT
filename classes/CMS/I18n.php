<?php
namespace CMS;
use \common\Registry;

class I18n extends \common\I18n {
    function __construct($xml) {
        parent::__construct($xml);
        $this->language = Registry::getInstance()->get('locale'); 
    }
}