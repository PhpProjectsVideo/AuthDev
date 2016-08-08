<?php

namespace PhpProjects\AuthDev;

use PHPUnit_Extensions_Database_DataSet_IDataSet;

class UserManagementTest extends DatabaseSeleniumTestCase
{
    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'users' => [
                [ 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1', 'password' => 'badhash' ],
                [ 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2', 'password' => 'badhash' ],
                [ 'username' => 'taken.user03', 'email' => 'taken3@digitalsandwich.com', 'name' => 'Existing User 3', 'password' => 'badhash' ],
                [ 'username' => 'taken.user04', 'email' => 'taken4@digitalsandwich.com', 'name' => 'Existing User 4', 'password' => 'badhash' ],
                [ 'username' => 'taken.user05', 'email' => 'taken5@digitalsandwich.com', 'name' => 'Existing User 5', 'password' => 'badhash' ],
                [ 'username' => 'taken.user06', 'email' => 'taken6@digitalsandwich.com', 'name' => 'Existing User 6', 'password' => 'badhash' ],
                [ 'username' => 'taken.user07', 'email' => 'taken7@digitalsandwich.com', 'name' => 'Existing User 7', 'password' => 'badhash' ],
                [ 'username' => 'taken.user08', 'email' => 'taken8@digitalsandwich.com', 'name' => 'Existing User 8', 'password' => 'badhash' ],
                [ 'username' => 'taken.user09', 'email' => 'taken9@digitalsandwich.com', 'name' => 'Existing User 9', 'password' => 'badhash' ],
                [ 'username' => 'taken.user10', 'email' => 'taken10@digitalsandwich.com', 'name' => 'Existing User 10', 'password' => 'badhash' ],
                [ 'username' => 'taken.user11', 'email' => 'taken11@digitalsandwich.com', 'name' => 'Existing User 11', 'password' => 'badhash' ],
            ],
            'groups' => [
                [ 'name' => 'Group 1', ],
                [ 'name' => 'Group 2', ],
                [ 'name' => 'Group 3', ],
                [ 'name' => 'Group 4', ],
                [ 'name' => 'Group 5', ],
            ],
            'users_groups' => [ ],
        ]);
    }

    public function testListingUsers()
    {
        $this->url('http://auth.dev/users/');

        //Test that we can see users
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user01']");
        $this->assertEquals('taken.user01', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Existing User 1', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('taken1@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());
    }

    public function testPagination()
    {
        $this->url('http://auth.dev/users/');
        
        //Paginate
        $this->byId('pagination-next')->click();
        
        //Check Url
        $this->assertEquals('http://auth.dev/users/?page=2', $this->url());

        //Test that we can see users
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user11']");
        $this->assertEquals('taken.user11', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Existing User 11', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('taken11@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());

        //Paginate Back
        $this->byId('pagination-previous')->click();

        //Check Url
        $this->assertEquals('http://auth.dev/users/', $this->url());

        //Test that we can see users
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user01']");
        $this->assertEquals('taken.user01', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Existing User 1', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('taken1@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());
    }

    public function testUserSearch()
    {
        $this->url('http://auth.dev/users/');

        // Search for users
        $this->byId('user-list-search-term')->value('taken.user1');
        $this->byId('user-list-search')->click();

        $this->assertEquals('http://auth.dev/users/?q=taken.user1', $this->url());

        // Test that we aren't showing non-matching users.
        $this->assertEmpty($this->byId('user-list')->elements($this->using('xpath')->value(".//tr[normalize-space(td//text())='taken.user01']")));

        // Test that we show all matching users
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user10']");
        $this->assertEquals('taken.user10', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Existing User 10', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('taken10@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());

        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user11']");
        $this->assertEquals('taken.user11', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Existing User 11', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('taken11@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());
    }


    public function testAddingUser()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('username')->value('mike.lively');
        $this->byName('name')->value('Mike Lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/users/', $this->url());

        //Check for page content
        $this->assertEquals('User mike.lively successfully edited!', $this->byId('notification')->text());

        // Search for users
        $this->byId('user-list-search-term')->value('mike.lively');
        $this->byId('user-list-search')->click();

        $tableRow = $this->byId('user-list')->byXPath(".//tr[td//text()[contains(.,'Mike Lively')]]");
        $this->assertEquals('mike.lively', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Mike Lively', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('m@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());
    }
    public function testAddingEmptyUsername()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('name')->value('Mike Lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of username field
        $element = $this->byName('username');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Username is required', $container->byClassName('help-block')->text());
    }

    public function testAddingEmptyName()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Name is required', $container->byClassName('help-block')->text());
    }

    public function testAddingEmptyEmail()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('username')->value('mike.lively');
        $this->byName('name')->value('Mike Lively');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of email field
        $element = $this->byName('email');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Email is required', $container->byClassName('help-block')->text());
    }

    public function testAddingEmptyPasswords()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('name')->value('Mike Lively');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());

        //Check display of email field
        $element = $this->byName('clear-password');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Password is required', $container->byClassName('help-block')->text());
    }

    public function testAddingNonMatchingPasswords()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('name')->value('Mike Lively');
        $this->byName('clear-password')->value('P@ssw0rd1');
        $this->byName('clear-password-confirm')->value('P@ssw0rd2');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('', $this->byName('clear-password')->value());
        $this->assertEquals('', $this->byName('clear-password-confirm')->value());

        //Check display of email field
        $element = $this->byName('clear-password');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Passwords must match', $container->byClassName('help-block')->text());
    }

    public function testAddingDuplicateUserByUsername()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('name')->value('Mike Lively');
        $this->byName('username')->value('taken.user01');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('taken.user01', $this->byName('username')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of username field
        $element = $this->byName('username');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('This username is already registered. Please try another.', $container->byClassName('help-block')->text());
    }

    public function testAddingDuplicateUserByEmail()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('name')->value('Mike Lively');
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('taken1@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('taken1@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of email field
        $element = $this->byName('email');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('This email is already registered. Please try another.', $container->byClassName('help-block')->text());
    }


    public function testAddingInvalidUsername()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('name')->value('Mike Lively');
        $this->byName('username')->value('mike.lively~~~~');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively~~~~', $this->byName('username')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of username field
        $element = $this->byName('username');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Usernames must be less than 50 characters and can only contain a-z, A-Z, 0-9 or the characters . _ and -.', $container->byClassName('help-block')->text());
    }

    public function testAddingInvalidEmail()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //disable html 5 validation
        $this->execute([ 'script' => 'document.getElementById("email").setAttribute("type", "text");', 'args' => [] ]);

        //Fill out the form
        $this->byName('name')->value('Mike Lively');
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('noemail');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals('Mike Lively', $this->byName('name')->value());
        $this->assertEquals('noemail', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of email field
        $element = $this->byName('email');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('You must enter a valid email. Please try another.', $container->byClassName('help-block')->text());
    }

    public function testAddingInvalidName()
    {
        //Navigate to the add user page
        $this->url('http://auth.dev/users/');
        $this->byLinkText('Add User')->click();

        //Fill out the form
        $this->byName('name')->value(str_repeat('abc', 100));
        $this->byName('username')->value('mike.lively');
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('clear-password')->value('P@ssw0rd');
        $this->byName('clear-password-confirm')->value('P@ssw0rd');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/users/new', $this->url());

        //Check for page content
        $this->assertEquals('mike.lively', $this->byName('username')->value());
        $this->assertEquals(str_repeat('abc', 100), $this->byName('name')->value());
        $this->assertEquals('m@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password')->value());
        $this->assertEquals('P@ssw0rd', $this->byName('clear-password-confirm')->value());

        //Check display of name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Names can only be up to 100 characters long.', $container->byClassName('help-block')->text());
    }

    public function testEditingUser()
    {
        $this->url('http://auth.dev/users/');

        //Find the right user
        $this->byId('user-list-search-term')->value('taken.user01');
        $this->byId('user-list-search')->click();

        //Click edit for that user
        $this->byLinkText('taken.user01')->click();

        // Validate existing data
        $this->assertEquals('taken.user01', $this->byName('username')->value());
        $this->assertEquals('Existing User 1', $this->byName('name')->value());
        $this->assertEquals('taken1@digitalsandwich.com', $this->byName('email')->value());
        $this->assertEquals('', $this->byName('clear-password')->value());
        $this->assertEquals('', $this->byName('clear-password-confirm')->value());

        //Modify the user
        $this->byName('username')->clear();
        $this->byName('username')->value('mike.lively');
        $this->byName('name')->clear();
        $this->byName('name')->value('Mike Lively');
        $this->byName('email')->clear();
        $this->byName('email')->value('m@digitalsandwich.com');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/users/', $this->url());

        //Check for page content
        $this->assertEquals('User mike.lively successfully edited!', $this->byId('notification')->text());

        //Search for users
        $this->byId('user-list-search-term')->value('mike.lively');
        $this->byId('user-list-search')->click();

        $tableRow = $this->byId('user-list')->byXPath(".//tr[td//text()[contains(.,'Mike Lively')]]");
        $this->assertEquals('mike.lively', $tableRow->byXPath('td[2]')->text());
        $this->assertEquals('Mike Lively', $tableRow->byXPath('td[3]')->text());
        $this->assertEquals('m@digitalsandwich.com', $tableRow->byXPath('td[4]')->text());

        //Search for old user
        $this->byId('user-list-search-term')->value('taken.user01');
        $this->byId('user-list-search')->click();

        //Old user should be gone
        $this->assertEmpty($this->elements($this->using('link text')->value('taken.user01')));
    }

    public function testRemovingUsers()
    {
        $this->url('http://auth.dev/users/?q=taken.user1');

        //Check the delete box for the proper users
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user10']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user11']");
        $tableRow->byXPath('td[1]/input')->click();

        //Click the delete button
        $this->byId('user-list-delete')->click();

        $this->assertEquals('http://auth.dev/users/remove?users%5B%5D=taken.user10&users%5B%5D=taken.user11', $this->url());

        //Cancel
        $this->byId('cancel')->click();

        $this->assertEquals('http://auth.dev/users/?q=taken.user1', $this->url());

        //Make sure the user is still there
        $this->assertNotNull($this->byLinkText('taken.user10'));

        //Check the delete box for the proper users again
        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user10']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('user-list')->byXPath(".//tr[normalize-space(td//text())='taken.user11']");
        $tableRow->byXPath('td[1]/input')->click();

        //click the delete button again
        $this->byId('user-list-delete')->click();

        $this->byId('confirm')->click();

        $this->assertEquals('http://auth.dev/users/?q=taken.user1', $this->url());
        $this->assertEquals('Users successfully removed: taken.user10, taken.user11', $this->byId('notification')->text());

        //Make sure users are gone
        $this->assertEmpty($this->elements($this->using('link text')->value('taken.user10')));
        $this->assertEmpty($this->elements($this->using('link text')->value('taken.user11')));
    }

    public function testAdjustingGroups()
    {
        $this->url('http://auth.dev/users/detail/taken.user01');

        $otherGroups = $this->byId('other-groups');
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 1']")->click();
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 2']");
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 3']")->click();
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 4']");
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 5']");
        $otherGroups->byXPath(".//button[normalize-space(text())='Add to Groups']")->click();

        $this->assertEquals('http://auth.dev/users/detail/taken.user01', $this->url());
        $memberGroups = $this->byId('member-groups');
        $memberGroups->byXPath(".//label[normalize-space(text())='Group 1']")->click();
        $memberGroups->byXPath(".//button[normalize-space(text())='Remove from Groups']")->click();

        $this->assertEquals('http://auth.dev/users/detail/taken.user01', $this->url());
        
        $otherGroups = $this->byId('other-groups');
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 1']");
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 2']");
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 4']");
        $otherGroups->byXPath(".//label[normalize-space(text())='Group 5']");

        $memberGroups = $this->byId('member-groups');
        $memberGroups->byXPath(".//label[normalize-space(text())='Group 3']");
    }
}
