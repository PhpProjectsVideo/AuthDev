<?php

namespace PhpProjects\AuthDev;

class ServerErrorTest extends DatabaseSeleniumTestCase
{
    public function test404Page()
    {
        $this->url('http://auth.dev/nopagehere');
        
        $this->assertEquals('Page not found!', $this->byId('title')->text());
        $this->assertContains('I could not find the page you were looking for.', $this->byId('message')->text());
        $this->assertContains('/', $this->byLinkText('Go Home')->attribute('href'));
    }
}
