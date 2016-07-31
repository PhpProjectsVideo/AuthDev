<?php

namespace PhpProjects\AuthDev\Controllers;

/**
 * Thrown from controllers when content is requested that does not exist.
 */
class ContentNotFoundException extends \RuntimeException
{
    private $title = 'Our page has gone missing!';

    private $recommendedAction = 'Go Home';

    private $recommendedUrl = '/';

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecommendedAction() : string
    {
        return $this->recommendedAction;
    }

    /**
     * @param string $recommendedAction
     * @return $this
     */
    public function setRecommendedAction(string $recommendedAction)
    {
        $this->recommendedAction = $recommendedAction;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecommendedUrl() : string
    {
        return $this->recommendedUrl;
    }

    /**
     * @param string $recommendedUrl
     * @return $this
     */
    public function setRecommendedUrl(string $recommendedUrl)
    {
        $this->recommendedUrl = $recommendedUrl;
        return $this;
    }
}
