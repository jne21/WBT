<?php

//include_once(__DIR__ . implode(DIRECTORY_SEPARATOR, array('', 'vendor', 'autoload.php')));

$classPath = __DIR__ . "/classes/";

$modelPath = __DIR__ . "/model/";
$viewPath = __DIR__ . "/view/";
$controllerPath = __DIR__ . "/controller/";

$cmsViewPath = $viewPath . 'cms/';
$wwwViewPath = $viewPath . 'www/';

$cmsControllerPath = $controllerPath . 'cms/';
$wwwControllerPath = $controllerPath . 'www/';

require($classPath . 'iView.php');

require_once($wwwControllerPath . 'Index.php');

require_once($cmsControllerPath . 'CMSController.php');
require_once($cmsControllerPath . 'LoginController.php');
require_once($cmsControllerPath . 'AdminController.php');
require_once($cmsControllerPath . 'RouterController.php');
require_once($cmsControllerPath . 'RedirectController.php');
require_once($cmsControllerPath . 'SetupController.php');

require_once($cmsControllerPath . 'CourseController.php');
require_once($cmsControllerPath . 'LessonController.php');

require_once($classPath . 'CMS/I18n.php');
require_once($classPath . 'CMS/RendererCMS.php');
require_once($classPath . 'WBT/LocaleManager.php');

require_once($modelPath . 'Course.php');
require_once($modelPath . 'CourseL10n.php');
require_once($modelPath . 'Exercise.php');
require_once($modelPath . 'Lesson.php');
require_once($modelPath . 'LessonL10n.php');
require_once($modelPath . 'Stage.php');
require_once($modelPath . 'StageL10n.php');

require_once($cmsViewPath . 'RouterEditView.php');
require_once($cmsViewPath . 'RouterListView.php');
