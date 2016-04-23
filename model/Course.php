<?php

namespace WBT;

use \common\Registry;
use WBT\CourseL10n;
#use Lesson;
#use Stage;
#use Stage;
#use Module;
#use Exercise;

final class Course extends \common\SimpleObject
{
    const
        TABLE = 'course',
        DB    = 'db',

        HIDDEN = 0,
        VISIBLE = 1,
        ALL = 255,

        ORDER_FIELD_NAME = 'order'
    ;

    public
        $id, $ownerId, $dateCreate, $dateUpdate, $state, $order, $l10n;

    use \common\entity;

    function __construct($courseId=NULL)
    {
        parent::__construct($courseId);
        $this->l10n  = new CourseL10n($this->id);
    }

    function load($data)
    {
        $this->id         = intval($data->id);
        $this->ownerId    = intval($data->owner_id);
        $this->dateCreate = strtotime($data->date_create);
        $this->dateUpdate = strtotime($data->date_update);
        $this->state      = intval($data->state);
        $this->rights     = intval($data->rights);
        $this->order      = intval($data->{self::ORDER_FIELD_NAME});
    }

    function save()
    {
        $db = Registry::getInstance()->get(self::DB);
        if ($this->id) {
            $this->dateUpdate = time();
            $db->update (
                self::TABLE,
                [
                    'date_update' => date('Y-m-d H:i:s', $this->dateUpdate),
                    'state'       => $this->state,
                ],
                "`id`=".intval($this->id)
            );
        }
        else {
            $this->num = self::getNextOrderIndex();
            $this->dateCreate = time();
            $db->insert(
                self::TABLE,
                [
                    'owner_id'              => $this->ownerId,
                    'date_create'           => date('Y-m-d H:i:s', $this->dateCreate),
                    self::ORDER_FIELD_NAME  => self::getNextOrderIndex(),
                    'state'                 => $this->state,
                ]
            ) or die($db->lastError);
            $this->id = $db->insertId();
            $this->l10n->parentId = $this->id;
        }
        $this->l10n->save();
    }

    static function getList($mode=self::VISIBLE)
    {
        $db = Registry::getInstance()->get(self::DB);
        if ($mode == self::VISIBLE) {
            $sql = "SELECT * FROM `".self::TABLE."` WHERE `state`=".self::VISIBLE." ORDER BY `".self::ORDER_FIELD_NAME."`";
        }
        else {
            $sql = "SELECT * FROM `".self::TABLE."` ORDER BY `".self::ORDER_FIELD_NAME."`";
        }
        $result = parent::getList($sql);
        $l10nList = CourseL10n::getListByIds(array_keys($result));
        foreach ($result as $courseId=>$course) {
            if (isset($l10nList[$courseId])) {
                $l10n = $l10nList[$courseId];
            }
            else {
                $l10n = new CourseL10n($l10nList[$courseId]->id);
            }
            $result[$courseId]->l10n = $l10n;
        }
        return $result;
    }

    static function delete($courseId) {
        if ($id = intval($courseId)) {
            CourseL10n::deleteAll($courseId);
            parent::delete($id);
        }
    }

    static function setState($courseId, $state) {
        self::updateValue($courseId, 'state', $state);
    }
}
