<?php

namespace PhpProjects\AuthDev\Authentication;

use Phake;
use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PhpProjects\AuthDev\Model\Permission\PermissionRepository;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase
{
    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @var array
     */
    private $session;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var array
     */
    private $permissionList;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    protected function setUp()
    {
        $this->session = [];
        
        $this->user = Phake::mock(UserEntity::class);
        Phake::when($this->user)->passwordMatches->thenReturn(true);
        Phake::when($this->user)->getGroupIds->thenReturn([1, 2, 3]);
        
        $this->userRepository = Phake::mock(UserRepository::class);
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($this->user);
        
        $this->permissionList = [
            PermissionEntity::createFromArray(['id' => 1, 'name' => 'Permission1']),
            PermissionEntity::createFromArray(['id' => 2, 'name' => 'Permission2']),
            PermissionEntity::createFromArray(['id' => 3, 'name' => 'Permission3']),
        ];
        
        $this->permissionRepository = Phake::mock(PermissionRepository::class);
        Phake::when($this->permissionRepository)->getByGroupIds->thenReturn(new \ArrayIterator($this->permissionList));
        
        $this->loginService = new LoginService($this->session, $this->userRepository, $this->permissionRepository);
    }

    public function testIsSessionAuthenticated()
    {
        $this->session['LoginService']['username'] = 'test';

        $this->assertTrue($this->loginService->isSessionAuthenticated());
    }

    public function testIsSessionAuthenticatedReturnsFalse()
    {
        $this->assertFalse($this->loginService->isSessionAuthenticated());
    }
    
    public function testAttemptAuthentication()
    {
        $vr = $this->loginService->attemptAuthentication('user', 'pass');

        Phake::verify($this->userRepository)->getByFriendlyName('user');
        Phake::verify($this->user)->passwordMatches('pass');
        
        $this->assertTrue($vr->isValid());
        $this->assertEquals('user', $this->session['LoginService']['username']);
    }
    
    public function testAttemptAuthenticationBadUser()
    {
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn(null);

        $vr = $this->loginService->attemptAuthentication('user', 'pass');
        
        $this->assertFalse($vr->isValid());
        $this->assertNotEmpty($vr->getValidationErrorsForField('login'));
        $this->assertTrue(empty($this->session['LoginService']));
    }
    
    public function testAttemptAuthenticationPasswordMismatch()
    {
        Phake::when($this->user)->passwordMatches->thenReturn(false);

        $vr = $this->loginService->attemptAuthentication('user', 'pass');

        $this->assertFalse($vr->isValid());
        $this->assertNotEmpty($vr->getValidationErrorsForField('login'));
        $this->assertTrue(empty($this->session['LoginService']));
    }
    
    public function testRemoveAuthentication()
    {
        $this->session['LoginService']['username'] = 'test';
        
        $this->loginService->removeAuthentication();

        $this->assertTrue(empty($this->session['LoginService']));
    }
    
    public function testSessionHasPermission()
    {
        $this->session['LoginService']['username'] = 'test';

        $hasPermission = $this->loginService->sessionHasPermission('Permission1');

        Phake::verify($this->userRepository)->getByFriendlyName('test');
        Phake::verify($this->permissionRepository)->getByGroupIds([1, 2, 3]);
        $this->assertTrue($hasPermission);
    }
    
    public function testSessionHasPermissionBadUser()
    {
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn(null);
        $this->session['LoginService']['username'] = 'test';

        $hasPermission = $this->loginService->sessionHasPermission('Permission1');
        $this->assertFalse($hasPermission);
    }
    
    public function testSessionHasPermissionBadPermission()
    {
        $this->session['LoginService']['username'] = 'test';

        $hasPermission = $this->loginService->sessionHasPermission('Permission4');

        $this->assertFalse($hasPermission);
    }
}