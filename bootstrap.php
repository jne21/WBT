<?php

$classPath = __DIR__ . "/classes/";
$controllersPath = __DIR__ . "/controller/";
$frontendPath = $controllersPath . 'frontend/';
$backendPath = $controllersPath . 'backend/';

require_once($frontendPath . 'Index.php');

require_once($backendPath . 'CMSController.php');
require_once($backendPath . 'CMSController.php');
#require_once($backendPath . 'AdminController.php');
require_once($backendPath . 'LoginController.php');
require_once($backendPath . 'AdminController.php');
require_once($backendPath . 'RouterController.php');
require_once($backendPath . 'RedirectController.php');
require_once($backendPath . 'SetupController.php');

require_once($classPath . 'CMS/I18n.php');
require_once($classPath . 'WBT/LocaleManager.php');
require_once($classPath . 'CMS/RendererCMS.php');

