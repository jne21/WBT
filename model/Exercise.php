<?php
namespace WBT;

use \common\Registry;

final class Exercise extends \common\SimpleObject {

	const
		TABLE = 'exercise',
		DB    = 'db'
	;

	public
		$id, $name, $description, $script;

	function loadDataFromArray($data) {
		$this->id          = $data['id'];
		$this->name        = $data['name'];
		$this->description = $data['description'];
		$this->script      = $data['script'];
	}
	
	function save() {
		$db = Registry::getInstance()->get(self::DB);
		$record = [
				'name'        => $this->name,
				'description' => $this->description,
				'script'      => $this->script
		];
		if ($this->id) {
			$db->update (self::TABLE, $record, "`id`=".intval($this->id));
		}
		else {
			$db->insert(self::TABLE, $record);
			$this->id = $db->insertId();
		}
	}

}