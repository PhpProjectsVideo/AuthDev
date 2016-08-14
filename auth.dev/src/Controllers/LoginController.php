<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Authentication\LoginService;
use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Controller for displaying authentication.
 */
class LoginController
{
    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @var CsrfService
     */
    private $csrfService;

    /**
     * @param ViewService $viewService
     * @param LoginService $loginService
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, LoginService $loginService, CsrfService $csrfService)
    {
        $this->viewService = $viewService;
        $this->loginService = $loginService;
        $this->csrfService = $csrfService;
    }

    /**
     * @return LoginController
     */
    public static function create() : LoginController
    {
        return new self(ViewService::create(), LoginService::create(), CsrfService::create());
    }

    /**
     * Dipsplays the login form
     * 
     * Will set up to form to point the redirect back to $originalUrl on successful login.
     * 
     * @param string $originalUrl
     */
    public function getLogin(string $originalUrl)
    {
        if ($this->loginService->isSessionAuthenticated())
        {
            $this->viewService->redirect($originalUrl ?: '/');
        }
        else
        {
            $this->viewService->renderView('auth/login', [
                'originalUrl' => $originalUrl ?? '/',
                'validationResults' => new ValidationResults([]),
                'token' => $this->csrfService->getNewToken(),
            ]);
        }
    }

    /**
     * Processes the login form.
     * 
     * On successful login, will redirect the user back to the url in $postData['originalUrl'].
     * 
     * On unsuccessful login will redisplay the form with an invalid login error.
     * 
     * @param array $postData
     */
    public function postLogin(array $postData)
    {
        $validationResult = $this->loginService->attemptAuthentication($postData['username'], $postData['password']);

        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {
            $validationResult->addErrorForField('login', 'Your session has expired, please try again');
        }
        if ($validationResult->isValid())
        {
            $this->viewService->redirect($postData['originalUrl'] ?: '/');
        }
        else
        {
            $this->viewService->renderView('auth/login', [
                'originalUrl' => $postData['originalUrl'] ?? '/',
                'validationResults' => $validationResult,
                'token' => $this->csrfService->getNewToken(),
            ]);
        }
    }

    /**
     * Processes a logout request.
     * 
     * Upon completion, redirects the user back to the url in $postData['originalUrl']
     * 
     * @param array $postData
     */
    public function postLogout(array $postData)
    {
        $this->loginService->removeAuthentication();
        $this->viewService->redirect($postData['originalUrl'] ?? '/');
    }
}
