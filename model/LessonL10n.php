<?
namespace WBT;

use common\L10n;
use common\Registry;

class LessonL10n extends L10n {
	const
        DB = 'db',
		TABLE = 'lesson_l10n'
	;

	function loadDataFromArray($localeId, $array) {
		$this->set('name',        $array['name'],          $localeId)
            ->set('meta',        $array['meta'],          $localeId)
            ->set('description', $array['description'],   $localeId)
            ->set('brief',       $array['brief'],         $localeId)
            ->set('url',         $array['url'],           $localeId)
            ->set('title',       $array['title'],         $localeId);
	}

	function save() {
		foreach(array_keys(Registry::getInstance()->get('locales')) as $locale) {
			$data[$locale] = [
				'name'        => $this->get('name',        $locale),
				'meta'        => $this->get('meta',        $locale),
				'description' => $this->get('description', $locale),
				'brief'       => $this->get('brief',       $locale),
				'url'         => $this->get('url',         $locale),
				'title'       => $this->get('title',       $locale)
			];
		}
		parent::saveData($this->parentId, $data);
	}

}
