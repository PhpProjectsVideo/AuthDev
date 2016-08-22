<?php

use PhpProjects\AuthDev\Database\DatabaseService;
use PhpProjects\AuthDev\Database\DatabaseSessionHandler;

require __DIR__ . "/../vendor/autoload.php";

const CONFIG_CSRF_KEY = 'ihger85hegrj08q9hue';
const CONFIG_CSRF_TTL = 1800;

const CONFIG_VIEWS_DIR = __DIR__ . '/../views';

// The code below is used to make sure when we are running selenium tests, we use our test database.
// You can see the other piece of this in the DatabaseSeleniumTestCase::setUpPage method.
if (array_key_exists('iamwebdriver', $_GET) && $_SERVER['HTTP_HOST'] == 'auth.dev')
{
    setcookie('iamwebdriver', 1);
    header('Location: /');
    exit;
}
$dbName = isset($_COOKIE['iamwebdriver']) ? 'auth_test' : 'auth';

DatabaseService::setDefaultPdoParameters([
    "mysql:host=localhost;dbname={$dbName}",
    "auth",
    "auth123",
]);

//Setup our session handler
$sessionHandler = new DatabaseSessionHandler(DatabaseService::getInstance()->getPdo());
session_set_save_handler($sessionHandler);