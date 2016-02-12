<?php
use common\Admin;
use WBT\Course;
use WBT\CourseL10n;
use WBT\LocaleManager;
use common\Registry;

class CourseTest extends PHPUnit_Framework_Testcase
{
    const REG_KEY = 'unitTest_CourseTest';

    public $l10nFields = array ('brief', 'description', 'meta', 'name', 'state', 'title', 'url');

    function test_CourseCreate()
    {
        $registry = Registry::getInstance();
        $locales = LocaleManager::getLocales();

        $owner = AdminTest::createAdmin();
        $course = new Course();
        $course->ownerId = $owner->id;
        $course->state = $state = 8;
        $course->l10n = $l10n = self::createLocale();

        $course->save();
        $id = $course->id;
        $this->assertNotEmpty($course->id, 'Empty id after create');
        unset($course);

        $course1 = new Course($id);

        $this->assertEquals($owner->id, $course1->ownerId, "Invalid owner id ($id) after create.");
        $this->assertEquals($state, $course1->state, "Invalid state ($id) after create.");

        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals(
                    $l10n->get($field, $localeId),
                    $course1->l10n->get($field, $localeId),
                    "Invalid ($id)->l10n($localeId, $field) after create.");
            }
        }

        $setup = [
            'ownerId' => $owner->id,
            'id' => $id,
            'state' => $state,
            'l10n' => $l10n
        ];
        $registry->set(self::REG_KEY, $setup);
        unset($course1);
    }

    function test_CourseUpdate()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        
        $course = new Course($setup['id']);
        $course->state = $state = 1;
        $l10n = self::createLocale();
        
        $locales = LocaleManager::getLocales();
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $course->l10n->set(
                    $field,
                    $l10n->get($field, $localeId),
                    $localeId
                );
            }
        }

        $course->save();
        
        $setup['state'] = $state;
        $setup['l10n'] = $l10n;
        $registry->set(self::REG_KEY, $setup);
        
        unset($course);
        
        $course1 = new Course($setup['id']);
        $this->assertEquals($setup['ownerId'], $course1->ownerId, "Invalid owner id ({$setup['id']}) after update.");
        $this->assertEquals($setup['state'], $course1->state, "Invalid state ({$setup['id']}) after update.");
        
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals(
                    $setup['l10n']->get($field, $localeId),
                    $course1->l10n->get($field, $localeId),
                    "Invalid ({$setup['id']})->l10n($localeId, $field) after update."
                );
            }
        }
        unset($course1);
    }

    function test_CourseGetList()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        $locales = LocaleManager::getLocales();

        $list = Course::getList();
        $this->assertNotCount(0, $list, "getList returns empty array from not empty database ({$setup['id']})");
        $this->assertArrayHasKey($setup['id'], $list, "getList: Existing Course not found ({$setup['id']})");
        $course = $list[$setup['id']];
        $this->assertInstanceOf('WBT\Course', $course, "getList item is not an instance of Course");
        $this->assertEquals($setup['ownerId'], $course->ownerId, "getList: Invalid owner id ({$setup['id']}).");
        $this->assertEquals($setup['state'], $course->state, "getList: Invalid state ({$setup['id']}).");
        foreach ($locales as $localeId => $localeData) {
            foreach ($this->l10nFields as $field) {
                $this->assertEquals(
                    $setup['l10n']->get($field, $localeId),
                    $course->l10n->get($field, $localeId),
                    "getList: Invalid ({$setup['id']})->l10n($localeId, $field).");
            }
        }
        unset($course);
    }

    function test_CourseSetState()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        
        Course::setState($setup['id'], 0);
        $course = new Course($setup['id']);
        $this->assertEquals(0, $course->state, "Invalid value after setState ($id).");
        unset($course);
    }

    function test_CourseDelete()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);

        Course::delete($setup['id']);
        $course = new Course($setup['id']);
        $this->assertNotEquals($setup['id'], $course->id, "Delete does not working ({$setup['id']})");
        
        $db = $registry->get(Course::DB);
        $rs = $db->query("SELECT IFNULL(COUNT(*), 0) as `cnt` FROM `" . CourseL10n::TABLE . "` WHERE `parent_id`={$setup['id']}");
        if ($sa = $db->fetch($rs)) {
            $this->assertEquals(0, $sa['cnt'], "Delete does not remove localization data ({$setup['id']})");
        }
    }

    function test_cleanUp()
    {
        $registry = Registry::getInstance();
        $setup = $registry->get(self::REG_KEY);
        Admin::delete($setup['ownerId']);
        
        $db = $registry->get(Course::DB);
        $db->query("DELETE FROM `" . Admin::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . Course::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $db->query("DELETE FROM `" . CourseL10n::TABLE . "` WHERE `description` LIKE 'unitTest%'");
        $registry->set(self::REG_KEY, null);
    }

    static function createCourse($ownerId)
    {
        $course = new Course();
        
        $course->ownerId = $ownerId;
        $course->state = 8;
        
        $course->save();
        return $course;
    }

    static function createLocale()
    {
        $locales = LocaleManager::getLocales();
        $locale = new CourseL10n();
        foreach (array_keys($locales) as $localeId) {
            $l10n[$localeId] = [
                'brief' => 'brief_' . rand() . '_' . $localeId,
                'description' => 'unitTest_description_' . rand() . '_' . $localeId,
                'meta' => 'meta_' . rand() . '_' . $localeId,
                'name' => 'name_' . rand() . '_' . $localeId,
                'state' => rand(0, 255),
                'title' => 'title_' . rand() . '_' . $localeId,
                'url' => 'url_' . rand() . ' ' . $localeId
            ];
            $locale->loadDataFromArray($localeId, $l10n[$localeId]);
        }
        return $locale;
    }
}