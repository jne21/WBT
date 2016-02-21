<?php

use common\Admin;
use WBT\Course;
use common\Registry;
use WBT\Lesson;
use WBT\Stage;
use WBT\StageL10n;
use WBT\Exercise;
use WBT\LocaleManager;
use WBT\Material;
use WBT\MaterialL10n;

class StageTest extends PHPUnit_Framework_Testcase
{
    const REG_KEY = 'unitTest_StageTest';

    public $l10nFields = array(
        'name',
        'brief',
        'description',
        'meta',
        'title',
        'url'
    );

    function test_StageCreate()
    {
        $registry = Registry::getInstance();
        $owner = AdminTest::createAdmin();
        $course = CourseTest::createCourse($owner->id);
        $lesson = LessonTest::createLesson($course->id);
        $exercise = ExerciseTest::createExercise();
        $exercise1 = ExerciseTest::createExercise();

        $stage = new Stage();
        $stage->lessonId   = $lesson->id;
        $stage->name       = $name = "unit test 1";
        $stage->exerciseId = $exercise->id;
        $stage->settings   = $settings = '{"settings":"unit test 1"}';

        $locales = LocaleManager::getLocales();
        $stage->l10n = $l10n = self::createLocale();
        $stage->save();

        $this->assertNotEmpty($stage->id, 'Empty id after create');
        $id = $stage->id;
        unset($stage);

        $stage1 = new Stage($id);
        $this->assertEquals($lesson->id, $stage1->lessonId, "Invalid lesson id ({$id}) after create.");

        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals(
                    $l10n->get($field, $localeId),
                    $stage1->l10n->get($field, $localeId),
                    "Invalid ($id)->l10n($localeId, $field) after create."
                );
            }
        }

        $setup = [
            'ownerId' => $owner->id,
            'courseId' => $course->id,
            'lessonId' => $lesson->id,
            'exerciseId' => $exercise->id,
            'id' => $id,
            'l10n' => $l10n
        ];

        $registry->set(self::REG_KEY, $setup);
        unset($stage1);
    }

    function test_StageUpdate()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $stage = new Stage($setup['id']);
        $l10n = self::createLocale();

        $locales = LocaleManager::getLocales();
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $stage->l10n->set($field, $l10n->get($field, $localeId), $localeId);
            }
        }

        $stage->save();

        $setup['l10n'] = $l10n;
        $registry->set(self::REG_KEY, $setup);

        unset($stage);

        $stage1 = new Stage($setup['id']);
        $this->assertEquals($setup['lessonId'], $stage1->lessonId, "Invalid lesson id ({$setup['id']}) after update.");

        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals($setup['l10n']->get($field, $localeId), $stage1->l10n->get($field, $localeId), "Invalid ({$setup['id']})->l10n($localeId, $field) after update.");
            }
        }
        unset($stage1);
    }

    function test_StageGetList()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $locales = LocaleManager::getLocales();

        $list = Stage::getList($setup['lessonId']);
        $this->assertNotCount(0, $list, "getList returns empty array from not empty database ({$setup['id']})");
        $this->assertArrayHasKey($setup['id'], $list, "getList: Existing Stage not found ({$setup['id']})");
        $stage = $list[$setup['id']];
        $this->assertInstanceOf('WBT\Stage', $stage, "getList item is not an instance of Stage");
        $this->assertEquals($setup['lessonId'], $stage->lessonId, "getList: Invalid lesson id ({$setup['id']}).");
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals($setup['l10n']->get($field, $localeId), $stage->l10n->get($field, $localeId), "getList: Invalid ({$setup['id']})->l10n($localeId, $field).");
            }
        }
        unset($stage);
    }

    function test_StageDelete()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $db = $registry->get(Lesson::DB);
        $id = $setup['id'];

        Stage::delete($id);

        $stage = new Stage($id);
        $this->assertNotEquals($id, $stage->id, "Delete does not working ($id)");

        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . StageL10n::TABLE . "` WHERE `parent_id`=$id");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove localization data ($id)");
        }
        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . Material::TABLE . "` WHERE `stage_id`=$id");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove materials ($id)");
        }
    }

    function test_cleanUp()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        Stage::delete($setup['stageId']);
        Lesson::delete($setup['lessonId']);
        Course::delete($setup['courseId']);
        Admin::delete($setup['ownerId']);

        $db = $registry->get(Stage::DB);
        $db->query("DELETE FROM `" . StageL10n::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . Stage::TABLE . "` WHERE `name` LIKE 'unitTest%'");
        $registry->set(self::REG_KEY, null);
    }

    static function createLocale()
    {
        $locales = LocaleManager::getLocales();
        $locale = new StageL10n();
        foreach (array_keys($locales) as $localeId) {
            $l10n[$localeId] = [
                'name' => 'name_' . rand() . '_' . $localeId,
                'brief' => 'brief_' . rand() . '_' . $localeId,
                'description' => 'unitTest_description_' . rand() . '_' . $localeId,
                'meta' => 'meta_' . rand() . '_' . $localeId,
                'title' => 'title_' . rand() . '_' . $localeId,
                'url' => 'url_' . rand() . ' ' . $localeId
            ];
            $locale->loadDataFromArray($localeId, $l10n[$localeId]);
        }
        return $locale;
    }

    static function createStage($lessonId, $execiseId)
    {
        $stage = new Stage();

        $stage->lessonId = $lessonId;
        $stage->exerciseId = $execiseId;
        $stage->name = 'unitTest-'. microtime();
        $stage->l10n = self::createLocale();
        $stage->save();
        return $stage;
    }
    
    
}
