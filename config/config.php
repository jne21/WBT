<?php

use WBT\LocaleManager;
use common\Registry;
use DB\Mysqli as db;
use common\Setup;

session_start();


ini_set ('error_reporting', E_ALL & ~E_NOTICE);
//ini_set ('display_errors', 1);

if (php_sapi_name()=='cli') {
    $site_root_absolute = realpath(__DIR__ . DIRECTORY_SEPARATOR .'..') . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR;
}
else {
    $site_root_absolute = $_SERVER["DOCUMENT_ROOT"];
}
#die($site_root_absolute);
require($site_root_absolute . implode(DIRECTORY_SEPARATOR, array('..', 'vendor', 'autoload.php')));
include(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR. 'bootstrap.php');

use system\ExceptionHandler;

$site_include_path  = $site_root_absolute.'common' . DIRECTORY_SEPARATOR;

$errorHandler = new ExceptionHandler;
#$errorHandler->setupHandlers();
$errorHandler->debug = true;

date_default_timezone_set('Europe/Kiev');

$registry = Registry::getInstance();

$db_TZ = date('I') ? '+03:00' : '+02:00';

//--------------------------------------
$DB_SERVER   = 'localhost';
$DB_NAME     = 'WBT';
$DB_USER     = 'wbt';
$DB_PASSWORD = 'qyHFvQ';
//--------------------------------------

$db  = new db($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($db->lastError) die($db->lastError);
$db->query('SET NAMES utf8');
$db->query("SET time_zone = '$db_TZ'");
$registry->set('db',  $db);

$registry->set('setup', new Setup());

$basePathComponents = explode(DIRECTORY_SEPARATOR, realpath($site_root_absolute));
array_pop($basePathComponents);
$basePath = implode(DIRECTORY_SEPARATOR, $basePathComponents) . DIRECTORY_SEPARATOR;

$registry->set('base_path', $basePath);
$registry->set('i18n_path', $basePath . 'i18n' . DIRECTORY_SEPARATOR);

$registry->set('site_root_absolute',   $site_root_absolute);
$registry->set('template_path',        $site_root_absolute.'tpl' . DIRECTORY_SEPARATOR);
$registry->set('site_image_path',      $site_root_absolute.'img' . DIRECTORY_SEPARATOR);

$registry->set('material_path', $basePath . 'material' . DIRECTORY_SEPARATOR);

$registry->set('site_attachment_root', $site_root_absolute.'attachments' . DIRECTORY_SEPARATOR);

$registry->set('site_css_root', $site_root_absolute.'css' . DIRECTORY_SEPARATOR);
$registry->set('site_js_root', $site_root_absolute.'js' . DIRECTORY_SEPARATOR);

$registry->set('counters_enabled',     true);


$registry->set('i18n_path', $basePath . 'i18n/');

LocaleManager::setApplicationLocale();

$registry->set(
    'template_path',
    $registry->get('i18n_path') . 'templates' . DIRECTORY_SEPARATOR . $registry->get('locale') . DIRECTORY_SEPARATOR
);

@include_once('variables.local.php');

$registry->set('site_name', 'wbt.com');

$registry->set('site_protocol', $site_protocol);
$registry->set('site_root', $site_protocol . $_SERVER['HTTP_HOST'] . '/');
$registry->set('site_attachment_path', 'attachments/');

$registry->set('site_i18n_root', $site_root_absolute . 'i18n/');



parse_str($_SERVER["REDIRECT_QUERY_STRING"], $__GET);
if (get_magic_quotes_gpc()) {

    function stripslashes_deep ($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes(
                $value);
        return $value;
    }
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
//    $__GET = array_map('stripslashes_deep', $__GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}


$registry->set('attachment_settings', 
        array(
                'news' => array(
                        'name' => 'Новости',
                        'list_page' => 'news.php',
                        'edit_page' => 'news_edit.php',
                        'path' => 'news/'
                ),
                'block' => array(
                        'name' => 'Блоки',
                        'list_page' => 'block.php',
                        'edit_page' => 'block_edit.php',
                        'path' => 'block/'
                ),
                'page' => array(
                        'name' => 'Страницы',
                        'list_page' => 'page.php',
                        'edit_page' => 'page_edit.php',
                        'path' => 'page/'
                ),
                'tape' => array(
                        'name' => 'Ленты',
                        'list_page' => 'tape.php',
                        'edit_page' => 'tape_edit.php',
                        'path' => 'tape/'
                ),
                'template' => array(
                        'name' => 'Шаблоны страниц',
                        'list_page' => 'template.php',
                        'edit_page' => 'template_edit.php',
                        'path' => 'template/'
                ),
                'email_template' => array(
                        'name' => 'Шаблоны уведомлений',
                        'list_page' => 'email_template.php',
                        'edit_page' => 'email_template_edit.php',
                        'path' => 'email_template/'
                )
        ));


define('SITE_ROOT_SIGN', '{site_root}');

function is_valid_attachment_parent_table ($parent_table, $attachment_settings)
{
    return $parent_table &&
             (FALSE !==
             strpos(implode('|', array_keys($attachment_settings)), 
                    $parent_table));
}

function d ($var, $stop = false)
{
    echo '<pre>' . print_r($var, true) . '</pre>';
    if ($stop) {
        die();
    }
}
