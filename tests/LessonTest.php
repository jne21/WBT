<?php
use common\Admin;
use common\Registry;
use WBT\Course;
use WBT\CourseL10n;
use WBT\Lesson;
use WBT\LessonL10n;
use WBT\Stage;

class LessonTest extends PHPUnit_Framework_Testcase
{

    const REG_KEY = 'unitTest_LessonTest';

    public $l10nFields = array(
        'brief',
        'description',
        'meta',
        'name',
        'title',
        'url'
    );

    function test_CourseCreate()
    {
        $registry = Registry::getInstance();

        $owner = AdminTest::createAdmin();
        $course = CourseTest::createCourse($owner->id);
        $lesson = new Lesson($course->id);

        $lesson->courseId = $course->id;
        $lesson->name = $name = "unitTest";

        $locales = Registry::getInstance()->get('locales');
        $lesson->l10n = $l10n = self::createLocale();
        $lesson->save();
        $this->assertNotEmpty($lesson->id, 'Empty id after create');
        $id = $lesson->id;
        unset($lesson);

        $lesson1 = new Lesson($id);
        $this->assertEquals($course->id, $lesson1->courseId, "Invalid course id ({$id}) after create.");
        $this->assertEquals($name, $lesson1->name, "Invalid name ({$id}) after create.");

        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals(
                    $l10n->get($field, $localeId),
                    $lesson1->l10n->get($field, $localeId),
                    "Invalid ($id)->l10n($localeId, $field) after create."
                );
            }
        }

        $setup = [
            'ownerId' => $owner->id,
            'courseId' => $course->id,
            'id' => $id,
            'name' => $name,
            'l10n' => $l10n
        ];

        $registry->set(self::REG_KEY, $setup);
        unset($lesson1);
    }

    function test_LessonUpdate()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);

        $lesson = new Lesson($setup['id']);
        $lesson->name = $name = "unitTest 1";
        $l10n = self::createLocale();

        $locales = $registry->get('locales');
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $lesson->l10n->set($field, $l10n->get($field, $localeId), $localeId);
            }
        }

        $lesson->save();

        $setup['name'] = $name;
        $setup['l10n'] = $l10n;
        $registry->set(self::REG_KEY, $setup);

        unset($lesson);

        $lesson1 = new Lesson($setup['id']);
        $this->assertEquals($setup['courseId'], $lesson1->courseId, "Invalid course id ({$setup['id']}) after update.");
        $this->assertEquals($setup['name'], $lesson1->name, "Invalid name ({$setup['id']}) after update.");

        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals($setup['l10n']->get($field, $localeId), $lesson1->l10n->get($field, $localeId), "Invalid ({$setup['id']})->l10n($localeId, $field) after update.");
            }
        }
        unset($lesson1);
    }

    function test_LessonGetList()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $locales = $registry->get('locales');

        $list = Lesson::getList($setup['courseId']);
        $this->assertNotCount(0, $list, "getList returns empty array from not empty database ({$setup['id']})");
        $this->assertArrayHasKey($setup['id'], $list, "getList: Existing Lesson not found ({$setup['id']})");
        $lesson = $list[$setup['id']];
        $this->assertInstanceOf('WBT\Lesson', $lesson, "getList item is not an instance of Lesson");
        $this->assertEquals($setup['courseId'], $lesson->courseId, "getList: Invalid course id ({$setup['id']}).");
        $this->assertEquals($setup['name'], $lesson->name, "getList: Invalid name ({$setup['id']}).");
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals($setup['l10n']->get($field, $localeId), $lesson->l10n->get($field, $localeId), "getList: Invalid ({$setup['id']})->l10n($localeId, $field).");
            }
        }
        unset($lesson);
    }

    function test_LessonDelete()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $db = $registry->get(Lesson::DB);
        $id = $setup['id'];

        Lesson::delete($id);

        $lesson = new Lesson($id);
        $this->assertNotEquals($id, $lesson->id, "Delete does not working ($id)");

        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . LessonL10n::TABLE . "` WHERE `parent_id`=$id");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove localization data ($id)");
        }
        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . Stage::TABLE . "` WHERE `lesson_id`=$id");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove stages ($id)");
        }
    }

    function test_cleanUp()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        Course::delete($setup['courseId']);
        Admin::delete($setup['ownerId']);

        $db = $registry->get(Lesson::DB);
        $db->query("DELETE FROM `" . Admin::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . Course::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . CourseL10n::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . Lesson::TABLE . "` WHERE `name` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . LessonL10n::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $registry->set(self::REG_KEY, null);
    }

    static function createLocale()
    {
        $locales = Registry::getInstance()->get('locales');
        $locale = new LessonL10n();
        foreach (array_keys($locales) as $localeId) {
            $l10n[$localeId] = [
                'brief' => 'brief_' . rand() . '_' . $localeId,
                'description' => 'unitTest_description_' . rand() . '_' . $localeId,
                'meta' => 'meta_' . rand() . '_' . $localeId,
                'name' => 'name_' . rand() . '_' . $localeId,
                'title' => 'title_' . rand() . '_' . $localeId,
                'url' => 'url_' . rand() . ' ' . $localeId
            ];
            $locale->loadDataFromArray($localeId, $l10n[$localeId]);
        }
        return $locale;
    }

    function createLesson($courseId)
    {
        $lesson = new Lesson();

        $lesson->courseId = $courseId;
        $lesson->name = $name = "name 1";
        $lesson->l10n = self::createLocale();
        $lesson->save();
        return $lesson;
    }
}
