<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Views\ViewService;
use Exception;

/**
 * Controller for displaying error views.
 */
class ErrorController
{
    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var bool
     */
    private $debugMode;

    /**
     * @param ViewService $viewService
     * @param bool $debugMode
     */
    public function __construct(ViewService $viewService, bool $debugMode)
    {
        $this->viewService = $viewService;
        $this->debugMode = $debugMode;
    }

    /**
     * Convenience Constructor
     *
     * @return ErrorController
     */
    public static function create() : ErrorController
    {
        return new self(ViewService::create(), $_SERVER['HTTP_HOST'] == 'auth.dev');
    }

    /**
     * Handles 404 conditions from the application
     *
     * @param ContentNotFoundException $exception
     */
    public function getNotFoundPage(ContentNotFoundException $exception)
    {
        $this->viewService->renderHeader('HTTP/1.0 404 Not Found');
        $this->viewService->renderView('404', [
            'title' => $exception->getTitle(),
            'message' => $exception->getMessage(),
            'recommendedUrl' => $exception->getRecommendedUrl(),
            'recommendedAction' => $exception->getRecommendedAction(),
        ]);
    }

    /**
     * Handles error conditions from the application
     *
     * @param Exception $exception
     */
    public function getErrorPage($exception)
    {
        if ($this->debugMode)
        {
            $templateData = [
                'title' => 'Exception: ' . get_class($exception),
                'message' => (string)$exception,
                'recommendedUrl' => '/',
                'recommendedAction' => 'Go Home'
            ];
        }
        else
        {
            $templateData = [
                'title' => 'Unexpected Error',
                'message' => 'There was an error processing your request',
                'recommendedUrl' => '/',
                'recommendedAction' => 'Go Home'
            ];
        }

        $this->viewService->renderHeader('HTTP/1.0 500 Internal Server Error');
        $this->viewService->renderView('404', $templateData);
    }
}
