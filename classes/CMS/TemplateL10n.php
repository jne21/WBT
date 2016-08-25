<?php

namespace CMS;

class TemplateL10n extends \common\L10n
{
    const
        TABLE = 'template_l10n'
    ;

    public
        $id,
        $parentId
    ;

    function __construct($parentId=NULL)
    {
        parent::__construct(self::TABLE, $parentId);
    }
    
    function loadDataFromArray($localeId, $array)
    {
        $this->set('html', $array['html'], $localeId);
    }

    static function getListByIds($idList)
    {
        $result = [];
        if (is_array($idList) && count($idList)) {
            $ids = array_map('intval', $idList);
            foreach($l = parent::loadByParentIds(self::TABLE, $ids) as $parentId=>$l10nData) {
                $l10n = new self();
                $l10n->parentId = $parentId;
                foreach ($l10nData as $localeId=>$l10nItem) {
                    $l10n->loadDataFromArray($localeId, $l10nItem);
                }
                $result[$parentId] = $l10n;
            }
        }
        return $result;
    }

    function save()
    {
        foreach(array_keys(Registry::getInstance()->get('locales')) as $locale) {
            $data[$locale] = [
                'html' => $this->get('html', $locale)
            ];
        }
        parent::saveData($this->parentId, self::TABLE, $data);
    }

}
