<?
namespace WBT;

use common\L10n;
use common\Registry;

class CourseL10n extends L10n
{
    const
        DB = 'db',
        TABLE = 'course_l10n'
    ;

    function loadDataFromArray($localeId, $array)
    {
        $this->set('name',        $array['name'],          $localeId)
            ->set('meta',        $array['meta'],          $localeId)
            ->set('description', $array['description'],   $localeId)
            ->set('brief',       $array['brief'],         $localeId)
            ->set('url',         $array['url'],           $localeId)
            ->set('title',       $array['title'],         $localeId)
            ->set('state',       intval($array['state']), $localeId);
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
