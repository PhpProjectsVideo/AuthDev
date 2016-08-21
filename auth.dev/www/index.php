<?php

use PhpProjects\AuthDev\Controllers\Api\UserApiController;
use PhpProjects\AuthDev\Controllers\ContentNotFoundException;
use PhpProjects\AuthDev\Controllers\ErrorController;
use PhpProjects\AuthDev\Controllers\GroupController;
use PhpProjects\AuthDev\Controllers\LoginController;
use PhpProjects\AuthDev\Controllers\PermissionController;
use PhpProjects\AuthDev\Controllers\UserController;

require __DIR__ . '/../src/bootstrap.php';

session_start();

list($path, ) = explode('?', $_SERVER['REQUEST_URI'], 2);

$routes = [
    '^/$' => function () { header('Location: /users/'); },
    '^/auth(/.*)$' => function ($matches) {
        $controller = LoginController::create();

        $subRoutes = [
            '^/login$' => [
                'get' => function () use ($controller) { $controller->getLogin($_GET['originalUrl'] ?? ''); },
                'post' => function () use ($controller) { $controller->postLogin($_POST); },
            ],
            '^/logout$' => [
                'post' => function () use ($controller) { $controller->postLogout($_POST); },
            ],
        ];
        
        executeRoutes($matches[1], $subRoutes);
    },
    '^/users(/.*)$' => function ($matches) {
        $controller = UserController::create();

        $subRoutes = \PhpProjects\AuthDev\Controllers\SimpleCrudController::generateRoutes($controller);
        $subRoutes['^/update-groups/([^/]+)$'] = [
            'post' => function ($matches) use ($controller) { $controller->postUpdateGroups(urldecode($matches[1] ?? ''), $_POST); },
        ];
        
        executeRoutes($matches[1], $subRoutes);
    },
    '^/groups(/.*)$' => function ($matches) {
        $controller = GroupController::create();

        $subRoutes = \PhpProjects\AuthDev\Controllers\SimpleCrudController::generateRoutes($controller);
        $subRoutes['^/update-permissions/([^/]+)$'] = [
            'post' => function ($matches) use ($controller) { $controller->postUpdatePermissions(urldecode($matches[1] ?? ''), $_POST); },
        ];

        executeRoutes($matches[1], $subRoutes);
    },
    '^/permissions(/.*)$' => function ($matches) {
        $controller = PermissionController::create();

        $subRoutes = \PhpProjects\AuthDev\Controllers\SimpleCrudController::generateRoutes($controller);

        executeRoutes($matches[1], $subRoutes);
    },
    '^/api(/.*)$' => function ($matches) {
        $controller = UserApiController::create();

        $subRoutes = [
            '^/users$' => [
                'get' => function () use ($controller) { $controller->getList($_GET['page'] ?? 1, $_GET['q'] ?? ''); },
            ],
            '^/users/user$' => [
                'post' => function () use ($controller) { $controller->createUser($_POST); },
            ],
            '^/users/user/([^/]+)$' => [
                'get' => function ($matches) use ($controller) { $controller->getUser(urldecode($matches[1])); },
                'delete' => function ($matches) use ($controller) { $controller->deleteUser(urldecode($matches[1])); },
                'put' => function ($matches) use ($controller) {
                    parse_str(file_get_contents("php://input"),$postVars);
                    $controller->editUser(urldecode($matches[1]), $postVars);
                },
            ],
            '^/users/user/([^/]+)/groups$' => [
                'put' => function ($matches) use ($controller) {
                    parse_str(file_get_contents("php://input"),$postVars);
                    $controller->editUserGroups(urldecode($matches[1]), $postVars);
                },
            ],
        ];

        executeRoutes($matches[1], $subRoutes);
    }
];

try
{
    executeRoutes($path, $routes);
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

function executeRoutes(string $path, array $routes)
{
    foreach ($routes as $route => $routeOperations)
    {
        if (preg_match('#' . str_replace('#', '\#', $route) . '#', $path, $matches))
        {
            if (is_array($routeOperations))
            {
                if (isset($routeOperations[strtolower($_SERVER['REQUEST_METHOD'])]))
                {
                    $routeOperations[strtolower($_SERVER['REQUEST_METHOD'])]($matches);
                    return;
                }
            }
            else
            {
                $routeOperations($matches);
                return;
            }
        }
    }

    throw (new ContentNotFoundException("I could not find the page you were looking for. You may need to start over!"))
        ->setTitle('Page not found!');
}