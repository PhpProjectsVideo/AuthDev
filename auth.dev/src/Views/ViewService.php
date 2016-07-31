<?php

namespace PhpProjects\AuthDev\Views;

/**
 * Service handling rendering of HTML and headers for views
 */
class ViewService
{
    /**
     * @var string
     */
    private $viewDirectory;

    /**
     * @var string
     */
    private $redirectMessage = '';

    /**
     * @var array
     */
    private $session;

    /**
     * ViewService constructor.
     * @param string $viewDirectory
     * @param array $session
     */
    public function __construct(string $viewDirectory, array &$session)
    {
        $this->viewDirectory = $viewDirectory;
        $this->session =& $session;
        
        if (isset($session['redirectMessage']))
        {
            $this->redirectMessage = (string)$session['redirectMessage'];
            unset($session['redirectMessage']);
        }
    }

    /**
     * Convenience constructor
     * 
     * @return ViewService
     */
    public static function create() : ViewService
    {
        return new self(CONFIG_VIEWS_DIR, $_SESSION);
    }

    /**
     * Displays the template using the context data given in the array
     * 
     * @param string $viewName
     * @param array $data
     */
    public function renderView(string $viewName, array $data = [])
    {
        extract($data);
        require $this->viewDirectory . '/' . $viewName . '.php';
    }

    /**
     * Redirects a user to the given location with the given response code.
     * 
     * Allows setting an optional message that can be retrieved after the redirect using getRedirectMessage(). This
     * message is only available on the next immediate request.
     * 
     * @param $location
     * @param int $httpResponseCode
     * @param string $message
     */
    public function redirect(string $location, int $httpResponseCode = 302, string $message = '')
    {
        $this->renderHeader("Location: {$location}", true, $httpResponseCode);
        $_SESSION['redirectMessage'] = $message;
    }

    /**
     * Returns the previous request's redirect message.
     * @return string
     */
    public function getRedirectMessage() : string
    {
        return $this->redirectMessage;
    }

    /**
     * Sends an http header to the browser
     * 
     * @param string $header
     */
    public function renderHeader(string $header, bool $replace = true, int $httpResponseCode = null)
    {
        header($header, $replace, $httpResponseCode);
    }
}
