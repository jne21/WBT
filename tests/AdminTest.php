<?php
	use common\Admin;
	use common\Registry;

	class AdminTest extends PHPUnit_Framework_Testcase {

	public
		$data = [
			0 => [
				'admin_description'  => 'unitTest0',
				'admin_email'        => 'admin email0',
				'admin_login'        => 'admin_login0',
				'admin_name'         => 'admin_name0',
				'admin_password'     => 'admin_password0',
				'admin_state'        => Admin::BLOCKED,
				'admin_rights'       => Admin::RIGHTS_DEFAULT,
				'admin_locale'       => 'ru',
			],
			1 => [
				'admin_description'  => 'unitTest1',
				'admin_email'        => 'admin email 1',
				'admin_login'        => 'admin_login 1',
				'admin_name'         => 'admin_name 1',
				'admin_password'     => 'admin_password 1',
				'admin_state'        => Admin::ACTIVE,
				'admin_rights'       => Admin::RIGHTS_ALL,
				'admin_locale'       => 'uk',
			],
		],

		$adminId            = 0;

	function test_AdminCreate() {
		$registry = Registry::getInstance();


		$admin = new Admin;
		$admin->description = $this->data[0]['admin_description'];
		$admin->email       = $this->data[0]['admin_email'];
		$admin->login       = $this->data[0]['admin_login'];
		$admin->name        = $this->data[0]['admin_name'];
		$admin->setNewPassword($this->data[0]['admin_password']);
		$admin->state       = $this->data[0]['admin_state'];
		$admin->rights      = $this->data[0]['admin_rights'];
		$admin->locale      = $this->data[0]['admin_locale'];

//out( "CMS\Admin->save() - create mode" );
		$admin->save();

		$registry->set('adminId', $admin->id);
//echo "======= {$this->adminId} ========";

		unset($admin);

//out( "CMS\Admin->__construct()" );
		$admin = new Admin($registry->get('adminId'));
//echo "------- {$this->adminId} --------";

		$this->assertEquals($this->data[0]['admin_description'], $admin->description, "Create Admin: Error saving admin->description.");
		$this->assertEquals($this->data[0]['admin_email'],  $admin->email,    "Create Admin: Error saving admin->email");
		$this->assertEquals($this->data[0]['admin_login'],  $admin->login,    "Create Admin: Error saving admin->login");
		$this->assertEquals($this->data[0]['admin_name'],   $admin->name,     "Create Admin: Error saving admin->name");
		$this->assertEquals(Admin::passwordEncode($this->data[0]['admin_password']), $admin->password, "Create Admin: Error saving admin->password");
		$this->assertEquals($this->data[0]['admin_state'],  $admin->state,    "Create Admin: Error saving admin->state");
		$this->assertEquals($this->data[0]['admin_rights'], $admin->rights,   "Create Admin: Error saving admin->rights");
		$this->assertEquals($this->data[0]['admin_locale'], $admin->locale,   "Create Admin: Error saving admin->locale");

		unset($admin);
	}

	function test_AdminUpdate () {

		$registry = Registry::getInstance();
		$this->adminId = $registry->get('adminId');

		$admin = new Admin($this->adminId); //die(print_r($admin, 1));

		$admin->description = $this->data[1]['admin_description'];
		$admin->email       = $this->data[1]['admin_email'];
		$admin->login       = $this->data[1]['admin_login'];
		$admin->name        = $this->data[1]['admin_name'];
		$admin->setNewPassword($this->data[1]['admin_password']);
		$admin->state       = $this->data[1]['admin_state'];
		$admin->rights      = $this->data[1]['admin_rights'];
		$admin->locale      = $this->data[1]['admin_locale'];
	
//out( "CMS\Admin->save() - update mode");
		$admin->save();
		unset($admin);

		$admin = new Admin($this->adminId);

		$this->assertEquals($this->adminId,                      $admin->id,          "Update Admin: Error saving admin->id.");
		$this->assertEquals($this->data[1]['admin_description'], $admin->description, "Update Admin: Error saving admin->description.");
		$this->assertEquals($this->data[1]['admin_email'],       $admin->email,       "Update Admin: Error saving admin->email.");
		$this->assertEquals($this->data[1]['admin_login'],       $admin->login,       "Update Admin: Error saving admin->login.");
		$this->assertEquals($this->data[1]['admin_name'],        $admin->name,        "Update Admin: Error saving admin->name.");
		$this->assertEquals(Admin::passwordEncode($this->data[1]['admin_password']), $admin->password, "Update Admin: Error saving admin->password.");
		$this->assertEquals($this->data[1]['admin_state'],       $admin->state,       "Update Admin: Error saving admin->state.");
		$this->assertEquals($this->data[1]['admin_rights'],      $admin->rights,      "Update Admin: Error saving admin->rights.");
		$this->assertEquals($this->data[1]['admin_locale'],      $admin->locale,      "Update Admin: Error saving admin->locale.");

		unset($admin);
	}

	function test_AdminGetInstance() {
		$registry = Registry::getInstance();
		$this->adminId = $registry->get('adminId');

		$admin = Admin::getInstance($this->data[1]['admin_login'], $this->data[1]['admin_password']);
		$this->assertNotFalse($admin, "GetInstance Admin: Error getInstance({$this->adminId}) Not Found." , true);
		$this->assertInstanceOf('common\Admin', $admin, "Invalid instance in GetInstance Admin: Error getInstance({$this->adminId}).");
		$this->assertEquals($this->adminId, $admin->id, "GetInstance Admin: Error getInstance({$adminId}).");
		unset($admin);
	}

	function test_AdminGetList() {
		$registry = Registry::getInstance();
		$this->adminId = $registry->get('adminId');

//out( "CMS\Admin->getList()");
		$list = Admin::getList();
		$this->assertNotCount(0, $list, "List Admin: getList() returned empty array on not empty DB. Existing Admin({$this->adminId}) not found.");
		$this->assertArrayHasKey($this->adminId, $list, "List Admin: Existing Admin($adminId) not found by getList()");

		$admin = $list[$this->adminId];
		$this->assertInstanceOf('common\Admin', $admin, "List Admin: getList() item is not an instance of Admin.");
		$this->assertEquals($this->adminId,                      $admin->id,          "Admin getList: Error getting admin->id");
		$this->assertEquals($this->data[1]['admin_description'], $admin->description, "Admin getList: Error getting admin->description");
		$this->assertEquals($this->data[1]['admin_email'],       $admin->email,       "Admin getList: Error getting admin->email");
		$this->assertEquals($this->data[1]['admin_login'],       $admin->login,       "Admin getList: Error getting admin->login");
		$this->assertEquals($this->data[1]['admin_name'],        $admin->name,        "Admin getList: Error getting admin->name");
		$this->assertEquals(Admin::passwordEncode($this->data[1]['admin_password']), $admin->password, "Admin getList: Error getting admin->password");
		$this->assertEquals($this->data[1]['admin_state'],       $admin->state,       "Admin getList: Error getting admin->state");
		$this->assertEquals($this->data[1]['admin_rights'],      $admin->rights,      "Admin getList: Error getting admin->rights");
		$this->assertEquals($this->data[1]['admin_locale'],      $admin->locale,      "Admin getList: Error getting admin->locale");
		unset($admin);
	}

	function test_AdminDelete() {
		$registry = Registry::getInstance();
		$this->adminId = $registry->get('adminId');
//out( "CMS\Admin->delete()");
		Admin::delete($this->adminId);
		$admin = new Admin($this->adminId);
		$this->assertNull($admin->id, "Delete Admin: Error delete({$this->adminId})" );
	}

	function test_cleanUp ()
	{
		$registry = Registry::getInstance();
		$db = $registry->get('db');
		$this->adminId = $registry->get('adminId');
		$db->query("DELETE FROM `".Admin::TABLE."` WHERE `id`={$this->adminId} LIMIT 1");
		$db->query("DELETE FROM `".Admin::TABLE."` WHERE `description` LIKE 'unitTest%'");
	}


	static function createAdmin() {
		$admin = new Admin;
		$admin->description = "unittest-".date('YmdHis');
		$admin->login = "unittest-".date('YmdHis');
		$admin->password = 'qwerty';
		$admin->state = 0;
		$admin->rights = 0;
		$admin->locale = 'uk';
		$admin->save();
		return $admin;
	}
}

