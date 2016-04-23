<?php

namespace WBT;

use common\L10n;
use common\Registry;

class CourseL10n extends L10n
{
    const
        DB = 'db',
        TABLE = 'course_l10n'
    ;

    function load($localeId, $data)
    {
        $this->set('name',        $data->name,          $localeId)
            ->set('meta',        $data->meta,          $localeId)
            ->set('description', $data->description,   $localeId)
            ->set('brief',       $data->brief,         $localeId)
            ->set('url',         $data->url,           $localeId)
            ->set('title',       $data->title,         $localeId)
            ->set('state',       intval($data->state), $localeId);
    }

    function save()
    {
        foreach(array_keys($this->getLocales()) as $localeId) {
            $data[$localeId] = [
                'name'        => $this->get('name',        $localeId),
                'meta'        => $this->get('meta',        $localeId),
                'description' => $this->get('description', $localeId),
                'brief'       => $this->get('brief',       $localeId),
                'url'         => $this->get('url',         $localeId),
                'title'       => $this->get('title',       $localeId),
                'state'       => $this->get('state',       $localeId)
            ];
        }
        $this->saveData($data);
    }

    function getLocales()
    {
        return LocaleManager::getLocales();
    }
}
