<?php

namespace WBT;

use \common\Registry;

class LocaleManager {

    private static $locales = array(
        'uk' => array(
            'locale' => array(
                'uk_UA.UTF-8',
                'ukr_UKR.UTF-8',
                'Ukrainian_Ukraine.UTF-8'
            ),
            'name' => 'Українська',
            'dateFormat' => 'd.m.Y',
            'active' => TRUE
        ),
        'en' => array(
            'locale' => array(
                'en_US.UTF-8',
                'eng_USA.UTF-8',
                'English_USA.UTF-8'
            ),
            'name' => 'English',
            'dateFormat' => 'm.d.Y',
            'active' => TRUE
        ),
        'de' => array(
            'locale' => array(
                'de_DE.UTF-8',
                'deu_DEU.UTF-8',
                'German_Germany.UTF-8'
            ),
            'name' => 'Deutch',
            'dateFormat' => 'd.m.Y',
            'active' => TRUE
        ),
        'ru' => array(
            'locale' => array(
                'ru_RU.UTF-8',
                'rus_RUS.UTF-8',
                'Russian_Russia.UTF-8'
            ),
            'name' => 'Русский',
            'dateFormat' => 'd.m.Y',
            'active' => TRUE
        )
    );

    static function setApplicationLocale()
    {
        $registry = Registry::getInstance();

        if (isset($_SESSION['admin']['locale'])) {
            $locale = $_SESSION['admin']['locale'];
        }
        elseif ($userId = intval($_SESSION['user']['locale'])) {
            $locale = $_SESSION['user']['locale'];
        }
        else {
            $locale = $registry->get('setup')->get('locale');
        }

        $registry->set('locale', $locale);
        $localeData = self::getLocaleData($locale);
        setlocale(LC_ALL, $localeData['locale']);
        // echo strftime("%A %d %B %Y", mktime(0, 0, 0, 12, 1, 1968));
    }

    static function getLocaleData($locale)
    {
        return self::$locales[$locale];
    }

    static function getLocales()
    {
        return self::$locales;
    }
}
