<?php

use common\Registry;
use WBT\Exercise;

class ExerciseTest extends PHPUnit_Framework_Testcase {

	public
		$data = [
			0 => [
				'name'        => "unit test 0",
				'description' => "unitTest description 0",
				'script'      => "unit_test_script_0.php"
			],
			1 => [
				'name'        => "unit test 1",
				'description' => "unitTest description 1",
				'script'      => "unit_test_script_1.php"
			]
		];


	function test_createExercise ()
	{
		$registry = Registry::getInstance();
		$exercise = new Exercise();
		$exercise->name        = $this->data[0]['name'];
		$exercise->description = $this->data[0]['description'];
		$exercise->script      = $this->data[0]['script'];
		$exercise->save();

		$this->assertNotEmpty($exercise->id, 'Empty id after create');
		$id = $exercise->id;
		$registry->set('exerciseId', $id);
		unset ($exercise);

		$exercise1 = new Exercise($id);

		$this->assertEquals($this->data[0]['name'], $exercise1->name, "Invalid name ($id) after create.");
		$this->assertEquals($this->data[0]['description'], $exercise1->description, "Invalid description ($id) after create.");
		$this->assertEquals($this->data[0]['script'], $exercise1->script, "Invalid script ($id) after create.");
	}

	function test_updateExercise ()
	{
		$registry = Registry::getInstance();
		$id = $registry->get('exerciseId');

		$exercise = new Exercise($id);
		$exercise->name        = $this->data[1]['name'];
		$exercise->description = $this->data[1]['description'];
		$exercise->script      = $this->data[1]['script'];
		$exercise->save();
		unset($exercise);

		$exercise1 = new Exercise($id);
		$this->assertEquals($this->data[1]['name'], $exercise1->name, "Invalid name ($id) after update.");
		$this->assertEquals($this->data[1]['description'], $exercise1->description, "Invalid description ($id) after update.");
		$this->assertEquals($this->data[1]['script'], $exercise1->script, "Invalid script ($id) after update.");
		unset($exercise1);
	}

	function testExerciseGetList()
	{
		$registry = Registry::getInstance();
		$id = $registry->get('exerciseId');

		$list = Exercise::getList();
		$this->assertNotCount(0, $list, "getList returns empty array from not empty database ($id)");
		$this->assertArrayHasKey($id, $list, "getList did not found existing Exercise ($id)");
		$exercise = $list[$id];
		$this->assertInstanceOf('WBT\Exercise', $exercise, "getList item is not an instance of Exercise");
		$this->assertEquals($this->data[1]['name'], $exercise->name, "getList: Invalid name ($id) after update.");
		$this->assertEquals($this->data[1]['description'], $exercise->description, "getList: Invalid description ($id) after update.");
		$this->assertEquals($this->data[1]['script'], $exercise->script, "getList: Invalid script ($id) after update.");
		unset ($exercise);
	}

	function test_exerciseDelete ()
	{
		$registry = Registry::getInstance();
		$id = $registry->get('exerciseId');

		Exercise::delete($id);

		$exercise = new Exercise($id);
		$this->assertNotEquals($id, $exercise->id, "Delete does not working ($id)");
	}

	function test_cleanUp ()
	{
		Registry::getInstance()->get('db')->query("DELETE FROM `".Exercise::TABLE."` WHERE `description` LIKE 'unitTest%'");
	}


	function createExercise() {
		$exercise = new Exercise();

		$exercise->name        = $name        = "unit test ".date('Y-m-d H:i:s');
		$exercise->description = $description = "unitTest description";
		$exercise->script      = $script      = "unit_test_script.php";
		$exercise->save();
		return $exercise;
	}
}
