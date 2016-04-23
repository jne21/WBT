<?php
namespace WBT;
use common\Registry;
use WBT\StageL10n;

class Stage extends \common\SimpleObject {

    public
        /** @var int Идентификатор этапа */
        $id,

        /** @var int Идентификатор урока */
        $lessonId,

        /** @var int Идентификатор упражнения */
        $exerciseId,

        /** @var int Порядковый номер для ручной сортировки */
        $order,

        /** @var string Настроечные параметры для упражнения */
        $settings,

        /** @var string Локализация */
        $l10n
    ;

    const
        DB    = 'db',
        TABLE = 'stage',

        ORDER_FIELD_NAME = 'order'
    ;

use \common\entity;

    /**
     * Создание экземпляра класса Stage
     * @param string $stageId - код Этапа. Необязательно.
     */
    function __construct($stageId = NULL)
    {
        parent::__construct($stageId);
        $this->l10n  = new StageL10n($this->id);
    }

    /**
     * Загрузка свойств из массива
     * @param unknown $data
     */
    function load($data)
    {
        $this->id         = intval($data->id);
        $this->lessonId   = intval($data->lesson_id);
        $this->exerciseId = intval($data->exercise_id);
        $this->name       = $data->name;
        $this->order      = intval($data->{self::ORDER_FIELD_NAME});
        $this->settings   = $data->settings;
    }

    /**
     * Получение списка этапов заданного урока
     * @param unknown $lessonId
     * @return array
     */
    static function getList($lessonId=NULL)
    {
        $result = parent::getList("SELECT * FROM `".self::TABLE."` WHERE `lesson_id`=".intval($lessonId)." ORDER BY `".self::ORDER_FIELD_NAME."`");
        $l10nList = StageL10n::getListByIds(array_keys($result));
        foreach ($result as $stageId=>$stage) {
            $result[$stageId]->l10n = $l10nList[$stageId];
        }
        return $result;
    }

    /**
     * Сохранение объекта в БД при добавлении или редактировании
     */
    function save()
    {
        $db = Registry::getInstance()->get(self::DB);
        $properties = [
            'lesson_id' => $this->lessonId,
            'exercise_id' => $this->exerciseId,
            'name'        => $this->name,
            'settings'    => $this->settings
        ];
        if ($this->id) {
            $db->update (self::TABLE, $properties, "`id`=".intval($this->id));
        }
        else {
            $this->order = self::getNextOrderIndex();
            $properties[self::ORDER_FIELD_NAME] = $this->order;
            $db->insert(self::TABLE, $properties) or die($db->lastError);
            $this->id = $db->insertId();
            $this->l10n->parentId = $this->id;
        }
        $this->l10n->save();
    }

    static function delete($stageId)
    {
        StageL10n::deleteAll($stageId);
        parent::delete($stageId);
    }
}
