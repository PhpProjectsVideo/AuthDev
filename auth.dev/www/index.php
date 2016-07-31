<?php

use PhpProjects\AuthDev\Controllers\ContentNotFoundException;
use PhpProjects\AuthDev\Controllers\ErrorController;

require __DIR__ . '/../src/bootstrap.php';

session_start();

list($path, ) = explode('?', $_SERVER['REQUEST_URI'], 2);
$pathParts = explode('/', ltrim($path, '/'));

try
{
    throw (new ContentNotFoundException("I could not find the page you were looking for. You may need to start over!"))
        ->setTitle('Page not found!');
}
catch (ContentNotFoundException $e)
{
    $controller = ErrorController::create();
    $controller->getNotFoundPage($e);
}
catch (Throwable $e)
{
    $controller = ErrorController::create();
    $controller->getErrorPage($e);
}
