<?php

use PhpProjects\AuthDev\Database\DatabaseService;
use PhpProjects\AuthDev\Memcache\MemcacheService;

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

MemcacheService::setServers([
    ['host' => 'localhost', 'port' => '11211']
]);
MemcacheService::setNsPrefix($dbName);

//Setup our session handler
ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', 'tcp://localhost:11211');