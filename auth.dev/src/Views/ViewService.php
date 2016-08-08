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
     * @var string
     */
    private $redirectStatus = '';

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
            list($this->redirectMessage, $this->redirectStatus) = $session['redirectMessage'];
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
    public function redirect(string $location, int $httpResponseCode = 302, string $message = '', string $status = '')
    {
        $this->renderHeader("Location: {$location}", true, $httpResponseCode);
        $_SESSION['redirectMessage'] = array($message, $status);
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
     * Returns the previous request's redirect message.
     * @return string
     */
    public function getRedirectStatus() : string
    {
        return $this->redirectStatus;
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

    /**
     * Helper function to use in templates to start a block of content that you'll be passing to another template.
     */
    private function startBlock()
    {
        ob_start();
    }

    /**
     * Helper function that stops a block of content and returns that content for use in another template.
     * @return string
     */
    private function endBlock() : string 
    {
        return ob_get_clean() ?: '';
    }
}
