<?
namespace WBT;

use common\L10n;
use common\Registry;

class CourseL10n extends L10n {
	const
        DB = 'db',
		TABLE = 'course_l10n'
	;

	function loadDataFromArray($localeId, $array) {
		$this->set('name',        $array['name'],          $localeId)
            ->set('meta',        $array['meta'],          $localeId)
            ->set('description', $array['description'],   $localeId)
            ->set('brief',       $array['brief'],         $localeId)
            ->set('url',         $array['url'],           $localeId)
            ->set('title',       $array['title'],         $localeId)
            ->set('state',       intval($array['state']), $localeId);
	}

	function save() {
		foreach(array_keys(Registry::getInstance()->get('locales')) as $locale) {
			$data[$locale] = [
				'name'        => $this->get('name',        $locale),
				'meta'        => $this->get('meta',        $locale),
				'description' => $this->get('description', $locale),
				'brief'       => $this->get('brief',       $locale),
				'url'         => $this->get('url',         $locale),
				'title'       => $this->get('title',       $locale),
				'state'       => $this->get('state',       $locale)
			];
		}
		parent::saveData($this->parentId, $data);
	}

}
