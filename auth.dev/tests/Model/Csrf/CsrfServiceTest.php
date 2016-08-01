<?php

namespace PhpProjects\AuthDev\Model\Csrf;

use PHPUnit\Framework\TestCase;

class CsrfServiceTest extends TestCase
{
    /**
     * @var CsrfService
     */
    private $csrfService;

    public function setUp()
    {
        $sessionId = 1234567;
        $session = [];
        $tokenTTL = 1800;
        $key = 'mykey';
        $this->csrfService = new CsrfService($key, $sessionId, $session, $tokenTTL);
    }

    public function testGenerationOfToken()
    {
        $token1 = $this->csrfService->getNewToken();
        $token2 = $this->csrfService->getNewToken();

        $this->assertNotEmpty($token1, "getNewToken should generate a token");
        $this->assertNotEmpty($token2, "getNewToken should generate a token");

        $this->assertGreaterThanOrEqual(32, strlen($token1), "tokens should be at least 32 characters long");
        $this->assertGreaterThanOrEqual(32, strlen($token2), "tokens should be at least 32 characters long");
        $this->assertNotEquals($token1, $token2, "The same two tokens should never be generated");

        $this->assertTrue($this->csrfService->validateToken($token1), "Newly generated tokens should be valid");
        $this->assertTrue($this->csrfService->validateToken($token2), "Newly generated tokens should be valid");
    }
    
    public function testUnknownTokenIsInvalid()
    {
        $this->assertFalse($this->csrfService->validateToken('12345678901234567890123456789012'), "You shouldn't be able to easily guess a token");
    }
    
    public function testTokenCantBeReused()
    {
        $token = $this->csrfService->getNewToken();
        $this->csrfService->validateToken($token);
        
        $this->assertFalse($this->csrfService->validateToken($token), "Tokens should only be valid the first time");
    }
    
    public function testTokenCantBeShared()
    {
        $token = $this->csrfService->getNewToken();
        
        $otherSession = [];
        $otherService = new CsrfService('mykey', 1234567, $otherSession, 1800);

        $this->assertFalse($otherService->validateToken($token), "Tokens should only be usable in their own data store");
    }
    
    public function testTokenExpires()
    {
        $otherSession = [];
        $otherService = new CsrfService('mykey', 1234567, $otherSession, 0);
        
        $token = $otherService->getNewToken();
        $this->assertFalse($otherService->validateToken($token), "Tokens should expire after the ttl");
    }
}