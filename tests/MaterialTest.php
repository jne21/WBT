<?php

use common\Admin;
use WBT\Course;
use common\Registry;
use WBT\Lesson;
use WBT\LessonL10n;
use WBT\Stage;
use WBT\Exercise;
use WBT\LocaleManager;
use WBT\Material;
use WBT\MaterialL10n;

class MaterialTest extends PHPUnit_Framework_Testcase
{
    const REG_KEY = 'unitTest_MaterialTest';

    public $l10nFields = array(
        'fileName',
        'originalFileName',
        'mimeType'
    );

    function test_MaterialCreate()
    {
        $registry = Registry::getInstance();
        $owner = AdminTest::createAdmin();
        $course = CourseTest::createCourse($owner->id);
        $lesson = LessonTest::createLesson($course->id);
        $exercise = ExerciseTest::createExercise();
        $stage = StageTest::createStage($lesson->id, $exercise->id);

        $material = new Material();
        $material->stageId   = $stage->id;
        $material->name = $name = 'unitTest-Material-name-'.date('YmdHis');
        $material->description = $description = 'unitTest-Material-description-'.date('YmdHis');

        $fileSpec = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . '0.gif';
        $locales = LocaleManager::getLocales();
        $material->l10n = $l10n = self::createLocale($fileSpec);

        $material->save();
        $this->assertNotEmpty($material->id, 'Empty id after create');
        $id = $material->id;
        unset($material);

        $material1 = new Material($id);
        $this->assertEquals($stage->id, $material1->stageId, "Invalid stage id ({$id}) after create.");
        $this->assertEquals($name, $material1->name, "Invalid name ({$id}) after create.");
        $this->assertEquals($description, $material1->description, "Invalid description ({$id}) after create.");

        foreach (array_keys($locales) as $localeId) {
            $savedName = $material1->l10n->get('original_file_name', $localeId);
            $this->assertFileEquals(
                $fileSpec,
                $registry->get('material_path') . $material1->l10n->get('fileName', $localeId),
                "Invalid $savedName file contensts ($id)->l10n($localeId) after create."
            );

            $this->assertEquals(
                basename($l10n->get('uploaded_file_name', $localeId)),
                $material1->l10n->get('original_file_name', $localeId),
                "Invalid original file name ($id)->l10n($localeId) after create."
            );
        }

        $setup = [
            'ownerId' => $owner->id,
            'courseId' => $course->id,
            'lessonId' => $lesson->id,
            'exerciseId' => $exercise->id,
            'stageId' => $stage->id,
            'id' => $id,
            'l10n' => $l10n
        ];

        $registry->set(self::REG_KEY, $setup);
        unset($material1);
    }

    function test_MaterialUpdate()
    {
        $registry = Registry::getInstance();
        $locales = LocaleManager::getLocales();
        $setup = $registry->get(self::REG_KEY);

        $material = new Material($setup['id']);
        $l10n = $material->l10n;

        $fileSpec = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'gcode.gif';
        $l10n = $this->updateLocale($fileSpec, $l10n);
        $material->name = $name = 'unitTest-Material-name-1-'.date('YmdHis');
        $material->description = $description = 'unitTest-Material-description-1-'.date('YmdHis');
        $material->save();

        $setup['l10n'] = $l10n;
        $registry->set(self::REG_KEY, $setup);

        unset($material);

        $id = $setup['id'];
        $material1 = new Material($setup['id']);
 
        $this->assertEquals($setup['stageId'], $material1->stageId, "Invalid stage id ({$id}) after update.");
        $this->assertEquals($name, $material1->name, "Invalid name ({$id}) after update.");
        $this->assertEquals($description, $material1->description, "Invalid description ({$id}) after update.");

        foreach (array_keys($locales) as $localeId) {
            $savedName = $material1->l10n->get('original_file_name', $localeId);
            $this->assertFileEquals(
                $fileSpec,
                $registry->get('material_path') . $material1->l10n->get('fileName', $localeId),
                "Invalid $savedName file contensts ($id)->l10n($localeId) after update."
            );
            $this->assertEquals(
                basename($l10n->get('uploaded_file_name', $localeId)),
                $material1->l10n->get('original_file_name', $localeId),
                "Invalid original file name ($id)->l10n($localeId) after update."
            );
        }

        $registry->set(self::REG_KEY, $setup);
        unset($material1);
    }

    function test_MaterialGetList()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $locales = LocaleManager::getLocales();

        $list = Material::getList($setup['stageId']);
        $this->assertNotCount(0, $list, "getList returns empty array from not empty database ({$setup['id']})");
        $this->assertArrayHasKey($setup['id'], $list, "getList: Existing Material not found ({$setup['id']})");
        $material = $list[$setup['id']];
        $this->assertInstanceOf('WBT\Material', $material, "getList item is not an instance of Stage");
        $this->assertEquals($setup['stageId'], $material->stageId, "getList: Invalid stage id ({$setup['id']}).");

        foreach (array_keys($locales) as $localeId) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals($setup['l10n']->get($field, $localeId), $material->l10n->get($field, $localeId), "getList: Invalid ({$setup['id']})->l10n($localeId, $field).");
            }
        }

         unset($material);
    }

    function test_MaterialDelete()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $db = $registry->get(Material::DB);
        $id = $setup['id'];

        Material::delete($id);

        $material = new Material($id);
        $this->assertNotEquals($id, $material->id, "Delete does not working ($id)");

        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . MaterialL10n::TABLE . "` WHERE `parent_id`=$id");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove localization data ($id)");
        }
        $rs1 = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . Material::TABLE . "` WHERE `id`=$id");
        if ($sa = $db->fetch($rs1)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove materials ($id)");
        }
    }

    function test_cleanUp()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        Material::delete($setup['id']);
        Stage::delete($setup['stageId']);
        Lesson::delete($setup['lessonId']);
        Course::delete($setup['courseId']);
        Admin::delete($setup['ownerId']);

        $db = $registry->get(Material::DB);
        $db->query("DELETE FROM `".MaterialL10n::TABLE."` WHERE `parent_id`={$setup['id']}") or die($db->lastError);
#        $db->query("DELETE FROM `".MaterialL10n::TABLE."` WHERE `original_file_name`='gcode.gif'") or die($db->lastError);
        $db->query("DELETE FROM `".Material::TABLE."` WHERE `name` LIKE 'unitTest%'") or die($db->lastError);
        $db->query("DELETE FROM `".Stage::TABLE."` WHERE `name` LIKE 'unitTest%'") or die($db->lastError);
        $db->query("DELETE FROM `".Exercise::TABLE."` WHERE `name` LIKE 'unitTest%'") or die($db->lastError);
        $db->query("DELETE FROM `".Lesson::TABLE."` WHERE EXISTS(SELECT * FROM `".LessonL10n::TABLE."` `s` WHERE `parent_id`=`".Lesson::TABLE."`.`id` AND `s`.`name` LIKE 'unitTest%')") or die($db->lastError);

        $registry->set(self::REG_KEY, null);
    }

    static function createLocale($sourceFile)
    {
        $locales = LocaleManager::getLocales();
        $locale = new MaterialL10n();
        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $path = pathinfo($sourceFile, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        foreach (array_keys($locales) as $localeId) {
            $localeFileName = $path . "unitTest_".$localeId.'.'.$extension;
            copy($sourceFile, $localeFileName);
            $locale->prepareToReceiveFile($localeFileName, basename($sourceFile), $localeId);
        }
#print_r($locale);
#echo PHP_EOL . self::getTestFileSpec() . PHP_EOL;
        return $locale;
    }

    
    static function updateLocale($sourceFile, $locale)
    {
        $locales = LocaleManager::getLocales();
        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $path = pathinfo($sourceFile, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        foreach (array_keys($locales) as $localeId) {
            $localeFileName = $path . "unitTest_".$localeId.'.'.$extension;
            copy($sourceFile, $localeFileName);
            $locale->prepareToReceiveFile($localeFileName, basename($sourceFile), $localeId);
        }
#print_r($locale);
#echo PHP_EOL . self::getTestFileSpec() . PHP_EOL;
        return $locale;
    }

    static function createMaterial($stageId)
    {
        $material = new Material();

        $material->stageId = $stageId;
        $material->l10n = self::createLocale();
        $material->save();
        return $material;
    }

    
}
