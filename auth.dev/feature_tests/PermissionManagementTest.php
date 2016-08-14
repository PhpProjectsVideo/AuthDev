<?php

namespace PhpProjects\AuthDev;

use PHPUnit_Extensions_Database_DataSet_IDataSet;

class PermissionManagementTest extends DatabaseSeleniumTestCase
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

            ],
            'groups' => [
                [ 'id' => 1, 'name' => 'Group 1', ],

            ],
            'permissions' => [
                [ 'id' => 1, 'name' => 'Permission 1', ],
                [ 'id' => 2, 'name' => 'Permission 2', ],
                [ 'id' => 3, 'name' => 'Permission 3', ],
                [ 'id' => 4, 'name' => 'Permission 4', ],
                [ 'id' => 5, 'name' => 'Permission 5', ],
                [ 'id' => 6, 'name' => 'Permission 6', ],
                [ 'id' => 7, 'name' => 'Permission 7', ],
                [ 'id' => 8, 'name' => 'Permission 8', ],
                [ 'id' => 9, 'name' => 'Permission 9', ],
                [ 'id' => 10, 'name' => 'Permission 10', ],
                [ 'id' => 11, 'name' => 'Permission 11', ],
                [ 'id' => 12, 'name' => 'Administrator', ],
            ],
            'users_groups' => [
                [ 'users_id' => 1, 'groups_id' => 1 ],
            ],
            'groups_permissions' => [
                [ 'groups_id' => 1, 'permissions_id' => 12 ],
            ],
        ]);
    }

    public function setUpPage()
    {
        parent::setUpPage();
        $this->url('http://auth.dev/auth/login');
        $this->byName('username')->value('taken.user01');
        $this->byName('password')->value('P@ssw0rd');
        $this->byName('login')->click();
    }

    public function testListingPermissions()
    {
        $this->url('http://auth.dev/permissions/');

        //Test that we can see permissions
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 1']");
        $this->assertEquals('Permission 1', $tableRow->byXPath('td[2]')->text());
    }

    public function testPagination()
    {
        $this->url('http://auth.dev/permissions/');

        //Paginate
        $this->byId('pagination-next')->click();

        //Check Url
        $this->assertEquals('http://auth.dev/permissions/?page=2', $this->url());

        //Test that we can see permissions
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 9']");
        $this->assertEquals('Permission 9', $tableRow->byXPath('td[2]')->text());

        //Paginate Back
        $this->byId('pagination-previous')->click();

        //Check Url
        $this->assertEquals('http://auth.dev/permissions/', $this->url());

        //Test that we can see permissions
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 1']");
        $this->assertEquals('Permission 1', $tableRow->byXPath('td[2]')->text());
    }

    public function testPermissionSearch()
    {
        $this->url('http://auth.dev/permissions/');

        // Search for permissions
        $this->byId('permission-list-search-term')->value('Permission 1');
        $this->byId('permission-list-search')->click();

        $this->assertEquals('http://auth.dev/permissions/?q=Permission+1', $this->url());

        // Test that we aren't showing non-matching permissions.
        $this->assertEmpty($this->byId('permission-list')->elements($this->using('xpath')->value(".//tr[normalize-space(td//text())='Permission 2']")));

        // Test that we show all matching permissions
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 1']");
        $this->assertEquals('Permission 1', $tableRow->byXPath('td[2]')->text());

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 10']");
        $this->assertEquals('Permission 10', $tableRow->byXPath('td[2]')->text());

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 11']");
        $this->assertEquals('Permission 11', $tableRow->byXPath('td[2]')->text());
    }


    public function testAddingPermission()
    {
        //Navigate to the add permission page
        $this->url('http://auth.dev/permissions/');
        $this->byLinkText('Add Permission')->click();

        //Fill out the form
        $this->byName('name')->value('A Test Permission');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/permissions/', $this->url());

        //Check for page content
        $this->assertEquals('Permission A Test Permission successfully edited!', $this->byId('notification')->text());

        // Search for permissions
        $this->byId('permission-list-search-term')->value('A Test Permission');
        $this->byId('permission-list-search')->click();

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[td//text()[contains(.,'A Test Permission')]]");
        $this->assertEquals('A Test Permission', $tableRow->byXPath('td[2]')->text());
    }
    public function testAddingEmptyName()
    {
        //Navigate to the add permission page
        $this->url('http://auth.dev/permissions/');
        $this->byLinkText('Add Permission')->click();

        //Fill out the form
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/permissions/new', $this->url());

        //Check display of gruup name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-permission')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Name is required', $container->byClassName('help-block')->text());
    }

    public function testAddingDuplicatePermission()
    {
        //Navigate to the add permission page
        $this->url('http://auth.dev/permissions/');
        $this->byLinkText('Add Permission')->click();

        //Fill out the form
        $this->byName('name')->value('Permission 1');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/permissions/new', $this->url());

        //Check for page content
        $this->assertEquals('Permission 1', $this->byName('name')->value());

        //Check display of permission name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-permission')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('This name is already registered. Please try another.', $container->byClassName('help-block')->text());
    }

    public function testAddingInvalidName()
    {
        $this->url('http://auth.dev/permissions/');
        $this->byLinkText('Add Permission')->click();

        //Fill out the form
        $this->byName('name')->value(str_repeat('abc', 100));
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/permissions/new', $this->url());

        //Check for page content
        $this->assertEquals(str_repeat('abc', 100), $this->byName('name')->value());

        //Check display of name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-permission')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Names can only be up to 100 characters long.', $container->byClassName('help-block')->text());
    }

    public function testEditingPermission()
    {
        $this->url('http://auth.dev/permissions/');

        //Find the right permission
        $this->byId('permission-list-search-term')->value('Permission 1');
        $this->byId('permission-list-search')->click();

        //Click edit for that permission
        $this->byLinkText('Permission 1')->click();

        // Validate existing data
        $this->assertEquals('Permission 1', $this->byName('name')->value());

        //Modify the permission
        $this->byName('name')->clear();
        $this->byName('name')->value('A Test Permission');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/permissions/', $this->url());

        //Check for page content
        $this->assertEquals('Permission A Test Permission successfully edited!', $this->byId('notification')->text());

        //Search for permissions
        $this->byId('permission-list-search-term')->value('A Test Permission');
        $this->byId('permission-list-search')->click();

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[td//text()[contains(.,'A Test Permission')]]");
        $this->assertEquals('A Test Permission', $tableRow->byXPath('td[2]')->text());

        //Search for old permission
        $this->byId('permission-list-search-term')->value('Permission 1');
        $this->byId('permission-list-search')->click();

        //Old permission should be gone
        $this->assertEmpty($this->elements($this->using('link text')->value('Permission 1')));
    }

    public function testRemovingPermissions()
    {
        $this->url('http://auth.dev/permissions/');

        //Check the delete box for the proper permissions
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 1']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 3']");
        $tableRow->byXPath('td[1]/input')->click();

        //Click the delete button
        $this->byId('permission-list-delete')->click();

        $this->assertEquals('http://auth.dev/permissions/remove?entities%5B%5D=Permission+1&entities%5B%5D=Permission+3', $this->url());

        //Cancel
        $this->byId('cancel')->click();

        $this->assertEquals('http://auth.dev/permissions/', $this->url());

        //Make sure the permission is still there
        $this->assertNotNull($this->byLinkText('Permission 1'));

        //Check the delete box for the proper permissions again
        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 1']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('permission-list')->byXPath(".//tr[normalize-space(td//text())='Permission 3']");
        $tableRow->byXPath('td[1]/input')->click();

        //click the delete button again
        $this->byId('permission-list-delete')->click();

        $this->byId('confirm')->click();

        $this->assertEquals('http://auth.dev/permissions/', $this->url());
        $this->assertEquals('Permissions successfully removed: Permission 1, Permission 3', $this->byId('notification')->text());

        //Make sure permissions are gone
        $this->assertEmpty($this->elements($this->using('link text')->value('Permission 1')));
        $this->assertEmpty($this->elements($this->using('link text')->value('Permission 3')));
    }
}
