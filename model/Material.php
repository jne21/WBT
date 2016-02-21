<?php

namespace WBT;

use common\Registry;
use WBT\MaterialL10n;

class Material extends \common\SimpleObject {

    public
        /** @var int Идентификатор материала */
        $id,

        /** @var int Идентификатор части урока */
        $stageId,

        /** @var string Название  материала */
        $name,

        /** @var string Описание материала */
        $description,

        /** @var string Хэш материала */
        $hash,

        /** @var string Локализация */
        $l10n
    ;

    const
        DB    = 'db',
        TABLE = 'material'
    ;

    /**
     * Создание экземпляра класса Material
     * @param string $stageId - код Материала. Необязательно.
     */
    function __construct($materialId = NULL) {
        parent::__construct($materialId);
        $this->l10n  = new MaterialL10n($this->id);
    }

    /**
     * Загрузка свойств из массива
     * @param unknown $data
     */
    function loadDataFromArray($data) {
        $this->id          = intval($data['id']);
        $this->stageId     = intval($data['stage_id']);
        $this->name        = $data['name'];
        $this->description = $data['description'];
        $this->hash        = $data['hash'];
    }

    /**
     * Получение списка материалов заданного этапа
     * @param int $stageId
     * @return arrays
     */
    static function getList($stageId=NULL) {
        $result = parent::getList("SELECT * FROM `".self::TABLE."` WHERE `stage_id`=".intval($stageId));
        $l10nList = MaterialL10n::getListByIds(array_keys($result));
        foreach (array_keys($result) as $materialId) {
            $result[$materialId]->l10n = $l10nList[$materialId];
        }
        return $result;
    }

    /**
     * Сохранение объекта в БД при добавлении или редактировании
     */
    function save() {
        $db = Registry::getInstance()->get(self::DB);
        $properties = [
            'stage_id'    => $this->stageId,
            'name'        => $this->name,
            'description' => $this->description
        ];
        if ($this->id) {
            $db->update (self::TABLE, $properties, "`id`=".intval($this->id));
        }
        else {
            $properties['hash'] = sha1(time());
            $db->insert(self::TABLE, $properties) or die($db->lastError);
            $this->id = $db->insertId();
            $this->l10n->parentId = $this->id;
        }
        $this->l10n->save();
    }

    static function delete($materialId) {
        MaterialL10n::deleteAll($materialId);
        parent::delete($materialId);
    }

}