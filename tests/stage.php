<?php
require_once('../inc/connect.php');

use CMS\Admin;
use WBT\Course;
use WBT\CourseL10n;
use common\Registry;
use WBT\Lesson;
use WBT\LessonL10n;
use WBT\Exerxise;
use WBT\Stage;
use WBT\StageL10n;
use WBT\Exercise;

out('WBT\Stage');

$owner = createAdmin();
$course = createCourse($owner->id);
$lesson = createLesson($course->id);
$exercise = createExercise();
$exercise1 = createExercise();

$stage = new Stage();
$stage->lessonId   = $lesson->id;
$stage->name       = $name = "unit test 1";
$stage->exerciseId = $exercise->id;
$stage->settings   = $settings = '{"settings":"unit test 1"}';

$locales = Registry::getInstance()->get('locales');

foreach (array_keys($locales) as $locale) {
	$l10n[$locale] = [
		'brief'       => 'brief 1 '.$locale,
		'description' => 'description 1'.$locale,
		'meta'        => 'meta 1'.$locale,
		'name'        => 'name 1'.$locale,
		'title'       => 'title 1'.$locale,
		'url'         => 'url 1'.rand().' '.$locale
	];
	$stage->l10n->loadDataFromArray($locale, $l10n[$locale]);
}

out('WBT\Stage->save() create');

$stage->save();

if ($id = $stage->id) {
	unset ($stage);

out("WBT\Stage->__construct($id)");

	$stage1 = new Stage($id);

	#print_r($stage1);
	compare($stage1->lessonId, $lesson->id, "Invalid lesson id ($id) after create.");
	compare($stage1->exerciseId, $exercise->id, "Invalid exercise id ($id) after create.");
	compare($stage1->name,     $name,       "Invalid name ($id) after create.");
	compare($stage1->settings, $settings,   "Invalid settings ($id) after create.");
	
	foreach ($l10n as $localeId=>$localeData) {
		foreach ($localeData as $field=>$value) {
			compare($stage1->l10n->get($field, $localeId), $value, "Invalid ($id)->l10n($locale, $field) after create.");
		}
	}

	$stage1->name      = $name = "name 2";
	$stage1->settings  = $settings = '{"settings":"unit test 2"}';
	$stage1->exerciseId = $exercise1->id;
	
	foreach (array_keys($locales) as $locale) {
		$l10n[$locale] = [
			'brief'       => 'brief 2 '.$locale,
			'description' => 'description 2'.$locale,
			'meta'        => 'meta 2'.$locale,
			'name'        => 'name 2'.$locale,
			'title'       => 'title 2'.$locale,
			'url'         => 'url 2 '.rand().' '.$locale
		];
	}

	foreach ($l10n as $localeId=>$localeData) {
		foreach ($localeData as $field=>$value) {
			$stage1->l10n->set($field, $value, $localeId);
		}
	}

out("WBT\Stage->save() update");

	$stage1->save();
	unset($stage1);

	$stage2 = new Stage($id);
	compare($stage2->lessonId,   $lesson->id,    "Invalid lesson id ($id) after update.");
	compare($stage2->exerciseId, $exercise1->id, "Invalid exercise id ($id) after update.");
	compare($stage2->name,       $name,          "Invalid name ($id) after update.");
	compare($stage2->settings,   $settings,      "Invalid settings ($id) after update.");
	
	foreach ($l10n as $localeId=>$localeData) {
		foreach ($localeData as $field=>$value) {
			compare($stage2->l10n->get($field, $localeId), $value, "Invalid ($id)->l10n($locale, $field) after update.");
		}
	}

	unset($lesson2);

out("WBT\Stage::getList");

	$list = Stage::getList($lesson->id);
	if (is_array($list)) {
		if (count($list)) {
			if (array_key_exists($id, $list)) {
				$stage3 = $list[$id];
				if ($stage3 instanceof Stage) {
					compare($stage3->lessonId,   $lesson->id,    "getList: Invalid lesson id ($id).");
					compare($stage3->exerciseId, $exercise1->id, "getList: Invalid exercise id ($id).");
					compare($stage3->name,       $name,          "getList: Invalid name ($id).");
					compare($stage3->settings,   $settings,      "getList: Invalid settings ($id).");
						
					foreach ($l10n as $localeId=>$localeData) {
						foreach ($localeData as $field=>$value) {
							compare($stage3->l10n->get($field, $localeId), $value, "getList: Invalid ($id)->l10n($locale, $field).");
						}
					}

				}
				else {
					out ("getList item is not an instance of Stage", true);
				}
			}
			else {
				out ("getList returns empty array from not empty database ($id)", true);
			}
		}
		else {
			out("getList return value is not an array", true);
		}
	}
	unset ($stage3);

out("WBT\Stage::delete($id)");

	Stage::delete($id);

	$stage5 = new Stage($id);

	if ($stage5->id == $id) out("Delete does not working ($id)", true);

	$db = $registry->get(Stage::DB);
	$rs = $db->query("SELECT COUNT(*) as `cnt` FROM `".StageL10n::TABLE."` WHERE `parent_id`=$id");
	if ($sa = $db->fetch($rs)) {
		if ($sa['cnt']) out ("Delete does not remove localization data ($id)", true);
	}
	
}
else {
	out('Empty id after create', true);
}

Exercise::delete($exercise1->id);
Exercise::delete($exercise->id);
Lesson::delete($lesson->id);
Course::delete($course->id);
Admin::delete($owner->id);

out(PHP_EOL.'... passed.'.PHP_EOL);

function createStage($lessonId, $exerciseId) {
	$stage = new Stage();
	
	$stage->lessonId   = $lessonId;
	$stage->name       = "name 1";
	$stage->exerciseId = $exerciseId; 
	$stage->settings   = '{"settings":"unit test"}';
	
	$locales = Registry::getInstance()->get('locales');
	
	foreach (array_keys($locales) as $locale) {
		$l10n[$locale] = [
			'brief'       => 'brief 1 '.$locale,
			'description' => 'description 1'.$locale,
			'meta'        => 'meta 1'.$locale,
			'name'        => 'name 1'.$locale,
			'title'       => 'title 1'.$locale,
			'url'         => 'url 1'.rand().' '.$locale
		];
		$stage->l10n->loadDataFromArray($locale, $l10n[$locale]);
	}
	
	$stage->save();
	return $stage;
}
