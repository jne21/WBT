<?php

use common\Registry;
use WBT\Exercise;

class ExerciseTest extends PHPUnit_Framework_Testcase
{

    public
        $data = [
            0 => [
                'name'            => "unit test 0",
                'description'     => "unitTest description 0",
                'controller'      => "unit_test_controller_0.php",
                'config_template' => "unit_test_config_template"
            ],
            1 => [
                'name'            => "unit test 1",
                'description'     => "unitTest description 1",
                'controller'      => "unit_test_controller_1.php",
                'config_template' => "unit_test_config_template 1"
            ]
        ];

    function test_createExercise ()
    {
        $registry = Registry::getInstance();
        $exercise = new Exercise();
        $exercise->name           = $this->data[0]['name'];
        $exercise->description    = $this->data[0]['description'];
        $exercise->controller     = $this->data[0]['controller'];
        $exercise->configTemplate = $this->data[0]['config_template'];
        $exercise->save();

        $this->assertNotEmpty($exercise->id, 'Empty id after create');
        $id = $exercise->id;
        $registry->set('exerciseId', $id);
        unset ($exercise);

        $exercise1 = new Exercise($id);

        $this->assertEquals($this->data[0]['name'], $exercise1->name, "Invalid name ($id) after create.");
        $this->assertEquals($this->data[0]['description'], $exercise1->description, "Invalid description ($id) after create.");
        $this->assertEquals($this->data[0]['controller'], $exercise1->controller, "Invalid controller ($id) after create.");
        $this->assertEquals($this->data[0]['config_template'], $exercise1->configTemplate, "Invalid config_template ($id) after create.");
    }

    function test_updateExercise ()
    {
        $registry = Registry::getInstance();
        $id = $registry->get('exerciseId');

        $exercise = new Exercise($id);
        $exercise->name           = $this->data[1]['name'];
        $exercise->description    = $this->data[1]['description'];
        $exercise->controller     = $this->data[1]['controller'];
        $exercise->configTemplate = $this->data[1]['config_template'];
        $exercise->save();
        unset($exercise);

        $exercise1 = new Exercise($id);
        $this->assertEquals($this->data[1]['name'], $exercise1->name, "Invalid name ($id) after update.");
        $this->assertEquals($this->data[1]['description'], $exercise1->description, "Invalid description ($id) after update.");
        $this->assertEquals($this->data[1]['controller'], $exercise1->controller, "Invalid controller ($id) after update.");
        $this->assertEquals($this->data[1]['config_template'], $exercise1->configTemplate, "Invalid config_template ($id) after update.");
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
        $this->assertEquals($this->data[1]['controller'], $exercise->controller, "getList: Invalid controller ($id) after update.");
        $this->assertEquals($this->data[1]['config_template'], $exercise->configTemplate, "getList: Invalid config_template ($id) after update.");
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

    static function createExercise()
    {
        $exercise = new Exercise();

        $exercise->name           = "unitTest ".microtime();
        $exercise->description    = "unitTest description";
        $exercise->controller     = "unit_test_controller.php";
        $exercise->configTemplate = "unit_test_config_template";
        $exercise->save();
        return $exercise;
    }
}
