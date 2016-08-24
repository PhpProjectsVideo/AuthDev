<?php

namespace PhpProjects\AuthDev;

use PhpProjects\AuthDev\Memcache\MemcacheService;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class LoginTest extends DatabaseSeleniumTestCase
{
    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        $hash =  password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => 10 ]);
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'users' => [
                [ 'id' => 1, 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1', 'password' => $hash ],
                [ 'id' => 2, 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2', 'password' => $hash ],
            ],
            'groups' => [
                [ 'id' => 1, 'name' => 'Group 1', ],
            ],
            'users_groups' => [
                [ 'users_id' => 1, 'groups_id' => 1 ],
            ],
            'permissions' => [
                [ 'id' => 1, 'name' => 'Administrator' ],
            ],
            'groups_permissions' => [
                [ 'groups_id' => 1, 'permissions_id' => 1 ],
            ],
        ]);
    }

    public function setUp()
    {
        parent::setUp();
        MemcacheService::getInstance()->fullFlush();
    }

    public function testLoginFromDirect()
    {
        $this->url('http://auth.dev/auth/login');
        $this->byName('username')->value('taken.user01');
        $this->byName('password')->value('P@ssw0rd');
        $this->byName('login')->click();

        $this->assertEquals('http://auth.dev/users/', $this->url());
    }

    public function testLoginFromUrl()
    {
        $this->url('http://auth.dev/users/new');
        
        $this->assertStringStartsWith('http://auth.dev/auth/login', $this->url());
        
        $this->byName('username')->value('taken.user01');
        $this->byName('password')->value('P@ssw0rd');
        
        $this->byName('login')->click();

        $this->assertEquals('http://auth.dev/users/new', $this->url());
    }


    public function testLogout()
    {
        $this->url('http://auth.dev/auth/login');
        $this->byName('username')->value('taken.user01');
        $this->byName('password')->value('P@ssw0rd');
        $this->byName('login')->click();

        $this->assertEquals('http://auth.dev/users/', $this->url());
        
        $this->byId('logout')->click();

        $this->assertStringStartsWith('http://auth.dev/auth/login', $this->url());
    }

    public function testFailedLogin()
    {
        $this->url('http://auth.dev/auth/login');
        $this->byName('username')->value('taken.user03');
        $this->byName('password')->value('P@ssw0rd');
        $this->byName('login')->click();

        $this->assertStringStartsWith('http://auth.dev/auth/login', $this->url());
        $this->assertEquals('Authentication Error', $this->byId('title')->text());
        $this->assertEquals('Incorrect username or password, please try again', $this->byId('message')->text());
    }

    public function testLackOfPermissions()
    {
        $this->url('http://auth.dev/users/new');

        $this->assertStringStartsWith('http://auth.dev/auth/login', $this->url());

        $this->byName('username')->value('taken.user02');
        $this->byName('password')->value('P@ssw0rd');

        $this->byName('login')->click();

        $this->assertEquals('http://auth.dev/users/new', $this->url());
        $this->assertEquals('Permissions Error', $this->byId('title')->text());
        $this->assertEquals('You do not have permission to access this content.', $this->byId('message')->text());
    }
}
