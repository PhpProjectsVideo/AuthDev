<?php

namespace PhpProjects\AuthDev;

/**
 * Custom test case that combines database tests with selenium tests.
 */
abstract class DatabaseSeleniumTestCase extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Adds database support so we can ensure we reset the test database on each run.
     * 
     * Need to rename dbSetup so we can call it from this class.
     */
    use DatabaseTestCaseTrait {
        DatabaseTestCaseTrait::setUp as dbSetUp;
    }
    
    public static $browsers = [
        [
            'browserName' => 'chrome',
            'host'    => '192.168.56.1',
            'port'    => 4444,
        ],
    ];

    /**
     * Set the base url and database stuff.
     */
    protected function setUp()
    {
        $this->setBrowserUrl('http://auth.dev/');
        $this->dbSetUp();
    }

    /**
     * Session specific setup. This along with code in src/bootstrap.php helps ensure we run functional tests against 
     * the database.
     */
    public function setUpPage()
    {
        $this->url('http://auth.dev/?iamwebdriver');
    }

    /**
     * Our default data set is empty. It can be overridden on a case by case basis in our tests.
     * 
     * @return \PHPUnit_Extensions_Database_DataSet_ArrayDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }

    /**
     * Takes a screenshot whenever a selenium test fails. This allows you to view more details about the failure. 
     * 
     * The screenshot will be in the tests/artifacts directory.
     */
    public function onNotSuccessfulTest($e)
    {
        $screenshotDir = __DIR__ . '/artifacts/';
        if (!file_exists($screenshotDir))
        {
            mkdir($screenshotDir, 0777, true);
        }

        $contents = $this->currentScreenshot();
        file_put_contents($screenshotDir . str_replace('\\', '_', $this->getTestId()) . '.png', $contents);
        
        parent::onNotSuccessfulTest($e);
    }
}