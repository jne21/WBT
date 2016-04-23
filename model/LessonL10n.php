<?php
namespace WBT;

use common\L10n;
use common\Registry;

class LessonL10n extends L10n
{
    const
        DB = 'db',
        TABLE = 'lesson_l10n'
    ;

    function load($localeId, $data) {
        $this->set('name',        $data->name,          $localeId)
            ->set('meta',        $data->meta,          $localeId)
            ->set('description', $data->description,   $localeId)
            ->set('brief',       $data->brief,         $localeId)
            ->set('url',         $data->url,           $localeId)
            ->set('title',       $data->title,         $localeId);
    }

    function save()
    {
        foreach(array_keys($this->getLocales()) as $locale) {
            $data[$locale] = [
                'name'        => $this->get('name',        $locale),
                'meta'        => $this->get('meta',        $locale),
                'description' => $this->get('description', $locale),
                'brief'       => $this->get('brief',       $locale),
                'url'         => $this->get('url',         $locale),
                'title'       => $this->get('title',       $locale)
            ];
        }
        $this->saveData($data);
    }

    function getLocales()
    {
        return LocaleManager::getLocales();
    }
}
