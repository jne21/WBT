<?php
namespace WBT;

use \common\Registry;
use WBT\LessonL10n;

final class Lesson extends \common\SimpleObject {

	const
		TABLE = 'lesson',
		DB    = 'db',
		
		HIDDEN = 0,
		VISIBLE = 1,

		ORDER_FIELD_NAME = 'order'
	;

	public
		$id, $courseId, $name, $order, $l10n;

	use \common\entity;

	function __construct($courseId = NULL) {
	    if ($id = intval($courseId)) {
            parent::__construct($id);
		}
		$this->l10n  = new LessonL10n($this->id);
	}
	
	function loadDataFromArray($data) {
		$this->id         = intval($data['id']);
		$this->courseId   = intval($data['course_id']);
		$this->name       = $data['name'];
		$this->order      = intval($data[self::ORDER_FIELD_NAME]);
	}
	
	function save() {
		$db = Registry::getInstance()->get(self::DB);
		if ($this->id) {
			$this->dateUpdate = time();
			$db->update (
					self::TABLE,
					[
							'name'        => $this->name
					],
					"`id`=".intval($this->id)
			);
		}
		else {
			$this->order = self::getNextOrderIndex();
			$db->insert(
					self::TABLE,
					[
							'course_id'             => $this->courseId,
							'name'                  => $this->name,
							self::ORDER_FIELD_NAME  => $this->order
					]
			) or die($db->lastError);
			$this->id = $db->insertId();
			$this->l10n->parentId = $this->id;
		}
		$this->l10n->save();
	}
	
	static function getList($courseId) {

	    $result = parent::getList("SELECT * FROM `".self::TABLE."` WHERE `course_id`=".intval($courseId)." ORDER BY `".self::ORDER_FIELD_NAME."`");
		$l10nList = LessonL10n::getListByIds(array_keys($result));
		foreach ($result as $lessonId=>$lesson) {
			$result[$lessonId]->l10n = $l10nList[$lessonId];
		}
		return $result;
	}
	
	static function delete($lessonId) {
		LessonL10n::deleteAll(LessonL10n::TABLE, $lesson->id);
	    parent::delete($lessonId);
	}
	
	static function setState($lessonId, $state) {
		self::updateValue($lessonId, 'state', $state);
	}

}