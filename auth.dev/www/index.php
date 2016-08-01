<?php

use PhpProjects\AuthDev\Controllers\ContentNotFoundException;
use PhpProjects\AuthDev\Controllers\ErrorController;
use PhpProjects\AuthDev\Controllers\UserController;

require __DIR__ . '/../src/bootstrap.php';

session_start();

list($path, ) = explode('?', $_SERVER['REQUEST_URI'], 2);
$pathParts = explode('/', ltrim($path, '/'));

try
{
    switch ($pathParts[0])
    {
        case 'users':
            $controller = UserController::create();

            switch ($pathParts[1])
            {
                case '':
                    $controller->getList($_GET['page'] ?? 1, $_GET['q'] ?? '');
                    break;
                case 'new':
                    if ($_SERVER['REQUEST_METHOD'] == 'GET')
                    {
                        $controller->getNew();
                    }
                    else
                    {
                        $controller->postNew($_POST);
                    }
                    break;
                case 'detail':
                    if ($_SERVER['REQUEST_METHOD'] == 'GET')
                    {
                        $controller->getDetail($pathParts[2] ?? '');
                    }
                    else
                    {
                        $controller->postDetail($pathParts[2] ?? '', $_POST);
                    }
                    break;
                default:
                    throw (new ContentNotFoundException("I could not find the page you were looking for. You may need to start over!"))
                        ->setTitle('Page not found!');
            }
            break;

        case '':
            header('Location: /users/');
            break;
        
        default:
            throw (new ContentNotFoundException("I could not find the page you were looking for. You may need to start over!"))
                ->setTitle('Page not found!');
    }
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
