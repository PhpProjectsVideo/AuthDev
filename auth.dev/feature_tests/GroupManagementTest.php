<?php

namespace PhpProjects\AuthDev;

use PHPUnit_Extensions_Database_DataSet_IDataSet;

class GroupManagementTest extends DatabaseSeleniumTestCase
{
    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'groups' => [
                [ 'name' => 'Group 1', ],
                [ 'name' => 'Group 2', ],
                [ 'name' => 'Group 3', ],
                [ 'name' => 'Group 4', ],
                [ 'name' => 'Group 5', ],
                [ 'name' => 'Group 6', ],
                [ 'name' => 'Group 7', ],
                [ 'name' => 'Group 8', ],
                [ 'name' => 'Group 9', ],
                [ 'name' => 'Group 10', ],
                [ 'name' => 'Group 11', ],
            ],
        ]);
    }

    public function testListingGroups()
    {
        $this->url('http://auth.dev/groups/');

        //Test that we can see groups
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 1']");
        $this->assertEquals('Group 1', $tableRow->byXPath('td[2]')->text());
    }

    public function testPagination()
    {
        $this->url('http://auth.dev/groups/');

        //Paginate
        $this->byId('pagination-next')->click();

        //Check Url
        $this->assertEquals('http://auth.dev/groups/?page=2', $this->url());

        //Test that we can see groups
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 9']");
        $this->assertEquals('Group 9', $tableRow->byXPath('td[2]')->text());

        //Paginate Back
        $this->byId('pagination-previous')->click();

        //Check Url
        $this->assertEquals('http://auth.dev/groups/', $this->url());

        //Test that we can see groups
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 1']");
        $this->assertEquals('Group 1', $tableRow->byXPath('td[2]')->text());
    }

    public function testGroupSearch()
    {
        $this->url('http://auth.dev/groups/');

        // Search for groups
        $this->byId('group-list-search-term')->value('Group 1');
        $this->byId('group-list-search')->click();

        $this->assertEquals('http://auth.dev/groups/?q=Group+1', $this->url());

        // Test that we aren't showing non-matching groups.
        $this->assertEmpty($this->byId('group-list')->elements($this->using('xpath')->value(".//tr[normalize-space(td//text())='Group 2']")));

        // Test that we show all matching groups
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 1']");
        $this->assertEquals('Group 1', $tableRow->byXPath('td[2]')->text());

        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 10']");
        $this->assertEquals('Group 10', $tableRow->byXPath('td[2]')->text());

        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 11']");
        $this->assertEquals('Group 11', $tableRow->byXPath('td[2]')->text());
    }


    public function testAddingGroup()
    {
        //Navigate to the add group page
        $this->url('http://auth.dev/groups/');
        $this->byLinkText('Add Group')->click();

        //Fill out the form
        $this->byName('name')->value('A Test Group');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/groups/', $this->url());

        //Check for page content
        $this->assertEquals('Group A Test Group successfully edited!', $this->byId('notification')->text());

        // Search for groups
        $this->byId('group-list-search-term')->value('A Test Group');
        $this->byId('group-list-search')->click();

        $tableRow = $this->byId('group-list')->byXPath(".//tr[td//text()[contains(.,'A Test Group')]]");
        $this->assertEquals('A Test Group', $tableRow->byXPath('td[2]')->text());
    }
    public function testAddingEmptyName()
    {
        //Navigate to the add group page
        $this->url('http://auth.dev/groups/');
        $this->byLinkText('Add Group')->click();

        //Fill out the form
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/groups/new', $this->url());

        //Check display of gruup name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertEquals('', $element->value());
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Name is required', $container->byClassName('help-block')->text());
    }

    public function testAddingDuplicateGroup()
    {
        //Navigate to the add group page
        $this->url('http://auth.dev/groups/');
        $this->byLinkText('Add Group')->click();

        //Fill out the form
        $this->byName('name')->value('Group 1');
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/groups/new', $this->url());

        //Check for page content
        $this->assertEquals('Group 1', $this->byName('name')->value());

        //Check display of group name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('This name is already registered. Please try another.', $container->byClassName('help-block')->text());
    }

    public function testAddingInvalidName()
    {
        $this->url('http://auth.dev/groups/');
        $this->byLinkText('Add Group')->click();

        //Fill out the form
        $this->byName('name')->value(str_repeat('abc', 100));
        $this->byName('save')->click();

        //Check to make sure redirect did not occur
        $this->assertEquals('http://auth.dev/groups/new', $this->url());

        //Check for page content
        $this->assertEquals(str_repeat('abc', 100), $this->byName('name')->value());

        //Check display of name field
        $element = $this->byName('name');
        $container = $element->byXPath("(ancestor::div[contains(@class,'form-group')])[1]");
        $this->assertContains('has-error', $container->attribute('class'));
        $this->assertEquals('Names can only be up to 100 characters long.', $container->byClassName('help-block')->text());
    }

    public function testEditingGroup()
    {
        $this->url('http://auth.dev/groups/');

        //Find the right group
        $this->byId('group-list-search-term')->value('Group 1');
        $this->byId('group-list-search')->click();

        //Click edit for that group
        $this->byLinkText('Group 1')->click();

        // Validate existing data
        $this->assertEquals('Group 1', $this->byName('name')->value());

        //Modify the group
        $this->byName('name')->clear();
        $this->byName('name')->value('A Test Group');
        $this->byName('save')->click();

        //Check for redirect
        $this->assertEquals('http://auth.dev/groups/', $this->url());

        //Check for page content
        $this->assertEquals('Group A Test Group successfully edited!', $this->byId('notification')->text());

        //Search for groups
        $this->byId('group-list-search-term')->value('A Test Group');
        $this->byId('group-list-search')->click();

        $tableRow = $this->byId('group-list')->byXPath(".//tr[td//text()[contains(.,'A Test Group')]]");
        $this->assertEquals('A Test Group', $tableRow->byXPath('td[2]')->text());

        //Search for old group
        $this->byId('group-list-search-term')->value('Group 1');
        $this->byId('group-list-search')->click();

        //Old group should be gone
        $this->assertEmpty($this->elements($this->using('link text')->value('Group 1')));
    }

    public function testRemovingGroups()
    {
        $this->url('http://auth.dev/groups/');

        //Check the delete box for the proper groups
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 1']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 3']");
        $tableRow->byXPath('td[1]/input')->click();

        //Click the delete button
        $this->byId('group-list-delete')->click();

        $this->assertEquals('http://auth.dev/groups/remove?groups%5B%5D=Group+1&groups%5B%5D=Group+3', $this->url());

        //Cancel
        $this->byId('cancel')->click();

        $this->assertEquals('http://auth.dev/groups/', $this->url());

        //Make sure the group is still there
        $this->assertNotNull($this->byLinkText('Group 1'));

        //Check the delete box for the proper groups again
        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 1']");
        $tableRow->byXPath('td[1]/input')->click();

        $tableRow = $this->byId('group-list')->byXPath(".//tr[normalize-space(td//text())='Group 3']");
        $tableRow->byXPath('td[1]/input')->click();

        //click the delete button again
        $this->byId('group-list-delete')->click();

        $this->byId('confirm')->click();

        $this->assertEquals('http://auth.dev/groups/', $this->url());
        $this->assertEquals('Groups successfully removed: Group 1, Group 3', $this->byId('notification')->text());

        //Make sure groups are gone
        $this->assertEmpty($this->elements($this->using('link text')->value('Group 1')));
        $this->assertEmpty($this->elements($this->using('link text')->value('Group 3')));
    }
}
