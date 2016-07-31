<?php

namespace PhpProjects\AuthDev\Controllers;

use Phake;
use PhpProjects\AuthDev\Views\ViewService;
use PHPUnit\Framework\TestCase;

class ErrorControllerTest extends TestCase
{
    /**
     * @var ErrorController
     */
    private $errorController;

    /**
     * @var ViewService
     */
    private $viewService;

    protected function setUp()
    {
        $this->viewService = \Phake::mock(ViewService::class);

        $this->errorController = new ErrorController($this->viewService, false);
    }

    public function testGetErrorPage()
    {
        $exception = new \Exception('This is a test exception');
        $this->errorController->getErrorPage($exception);

        Phake::verify($this->viewService)->renderHeader('HTTP/1.0 500 Internal Server Error');
        Phake::verify($this->viewService)->renderView('404', [
            'title' => 'Unexpected Error',
            'message' => 'There was an error processing your request',
            'recommendedUrl' => '/',
            'recommendedAction' => 'Go Home'
        ]);
    }

    public function testGetNotFoundPage()
    {
        $exception = (new ContentNotFoundException('Your content was not found'))
            ->setTitle('Not Found')
            ->setRecommendedAction('Do Something')
            ->setRecommendedUrl('/something');

        $this->errorController->getNotFoundPage($exception);

        Phake::verify($this->viewService)->renderHeader('HTTP/1.0 404 Not Found');
        Phake::verify($this->viewService)->renderView('404', [
            'title' => 'Not Found',
            'message' => 'Your content was not found',
            'recommendedAction' => 'Do Something',
            'recommendedUrl' => '/something',
        ]);
    }
}
