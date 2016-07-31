<?php

use PhpProjects\AuthDev\Database\DatabaseService;

require __DIR__ . "/../vendor/autoload.php";

const CONFIG_VIEWS_DIR = __DIR__ . '/../views';

// The code below is used to make sure when we are running selenium tests, we use our test database.
// You can see the other piece of this in the DatabaseSeleniumTestCase::setUpPage method.
if (array_key_exists('iamwebdriver', $_GET) && $_SERVER['HTTP_HOST'] == 'auth.dev')
{
    setcookie('iamwebdriver', 1);
    header('Location: /');
    exit;
}
$dbName = isset($_COOKIE['iamwebdriver']) ? 'test-auth.sqlite' : 'auth.sqlite';
define('CONFIG_DB_PATH', __DIR__ . "/../data/{$dbName}");

DatabaseService::setDefaultPdoParameters([
    'sqlite:' . CONFIG_DB_PATH
]);