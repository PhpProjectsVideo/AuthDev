<?php

namespace PhpProjects\AuthDev;


use GuzzleHttp\Psr7\Response;
use PhpProjects\AuthDev\Memcache\MemcacheService;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class UserApiTest extends TestCase
{
    use DatabaseTestCaseTrait {
        DatabaseTestCaseTrait::setUp as dbSetup;
    }

    /**
     * @var \GuzzleHttp\Client;
     */
    private $apiClient;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var Response
     */
    private $result;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'users' => [
                [ 'id' => 1, 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1', 'password' => $this->hash],
                [ 'id' => 2, 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2', 'password' => $this->hash],
                [ 'id' => 3, 'username' => 'taken.user03', 'email' => 'taken3@digitalsandwich.com', 'name' => 'Existing User 3', 'password' => $this->hash],
                [ 'id' => 4, 'username' => 'taken.user04', 'email' => 'taken4@digitalsandwich.com', 'name' => 'Existing User 4', 'password' => $this->hash],
                [ 'id' => 5, 'username' => 'taken.user05', 'email' => 'taken5@digitalsandwich.com', 'name' => 'Existing User 5', 'password' => $this->hash],
                [ 'id' => 6, 'username' => 'taken.user06', 'email' => 'taken6@digitalsandwich.com', 'name' => 'Existing User 6', 'password' => $this->hash],
                [ 'id' => 7, 'username' => 'taken.user07', 'email' => 'taken7@digitalsandwich.com', 'name' => 'Existing User 7', 'password' => $this->hash],
                [ 'id' => 8, 'username' => 'taken.user08', 'email' => 'taken8@digitalsandwich.com', 'name' => 'Existing User 8', 'password' => $this->hash],
                [ 'id' => 9, 'username' => 'taken.user09', 'email' => 'taken9@digitalsandwich.com', 'name' => 'Existing User 9', 'password' => $this->hash],
                [ 'id' => 10, 'username' => 'taken.user10', 'email' => 'taken10@digitalsandwich.com', 'name' => 'Existing User 10', 'password' => $this->hash],
                [ 'id' => 11, 'username' => 'taken.user11', 'email' => 'taken11@digitalsandwich.com', 'name' => 'Existing User 11', 'password' => $this->hash],
            ],
            'groups' => [
                [ 'id' => 1, 'name' => 'Group 1', ],
                [ 'id' => 2, 'name' => 'Group 2', ],
                [ 'id' => 3, 'name' => 'Group 3', ],
                [ 'id' => 4, 'name' => 'Group 4', ],
                [ 'id' => 5, 'name' => 'Group 5', ],
            ],
            'users_groups' => [
                [ 'users_id' => 9, 'groups_id' => 1 ],
            ],
            'permissions' => [
                [ 'id' => 1, 'name' => 'Administrator' ],
            ],
            'groups_permissions' => [
                [ 'groups_id' => 1, 'permissions_id' => 1 ],
            ],
        ]);
    }

    public function setUp()
    {
        $this->hash =  password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => 10 ]);
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar(false, [['Domain' => 'auth.dev', 'Name' => 'iamwebdriver', 'Value' => 1]]);
        MemcacheService::getInstance()->fullFlush();

        $this->apiClient = new \GuzzleHttp\Client([
            'base_uri' => 'http://auth.dev/',
            'allow_redirects' => false,
            'auth' => [ 'taken.user09', 'P@ssw0rd' ],
            'cookies' => $cookieJar,
            'http_errors' => false,
        ]);
        self::dbSetup();
    }

    public function testListingUsers()
    {
        $this->result = $this->apiClient->get('/api/users');

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);


        $this->assertArrayHasKey('users', $response);
        $this->assertCount(10, $response['users']);

        $this->assertEquals('taken.user01', $response['users'][0]['user']['username']);
        $this->assertEquals('Existing User 1', $response['users'][0]['user']['name']);
        $this->assertEquals('taken1@digitalsandwich.com', $response['users'][0]['user']['email']);
        $this->assertEquals('/api/users/user/1', $response['users'][0]['links']['self']);
        $this->assertEquals('/api/users/user/1', $response['users'][0]['links']['edit']);

        $this->assertEquals('/api/users?page=2', $response['links']['next']);
        $this->assertEquals('/api/users?q={searchTerm}', $response['links']['search']);
        $this->assertEquals('/api/users/user', $response['links']['create']);

    }

    public function testPagination()
    {
        $this->result = $this->apiClient->get('/api/users?page=2');

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);


        $this->assertCount(1, $response['users']);

        $this->assertEquals('taken.user11', $response['users'][0]['user']['username']);
        $this->assertEquals('Existing User 11', $response['users'][0]['user']['name']);
        $this->assertEquals('taken11@digitalsandwich.com', $response['users'][0]['user']['email']);
        $this->assertEquals('/api/users/user/11', $response['users'][0]['links']['self']);
        $this->assertEquals('/api/users/user/11', $response['users'][0]['links']['edit']);

        $this->assertEquals('/api/users?page=1', $response['links']['prev']);
        $this->assertEquals('/api/users?q={searchTerm}', $response['links']['search']);
        $this->assertEquals('/api/users/user', $response['links']['create']);
    }

    public function testUserSearch()
    {
        $this->result = $this->apiClient->get('/api/users?q=taken.user1');

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);


        $this->assertCount(2, $response['users']);

        $this->assertEquals('taken.user10', $response['users'][0]['user']['username']);
        $this->assertEquals('Existing User 10', $response['users'][0]['user']['name']);
        $this->assertEquals('taken10@digitalsandwich.com', $response['users'][0]['user']['email']);
        $this->assertEquals('/api/users/user/10', $response['users'][0]['links']['self']);
        $this->assertEquals('/api/users/user/10', $response['users'][0]['links']['edit']);

        $this->assertEquals('/api/users', $response['links']['list']);
        $this->assertEquals('/api/users/user', $response['links']['create']);
    }


    public function testAddingUser()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(201, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('mike.lively', $response['user']['username']);
        $this->assertEquals('Mike Lively', $response['user']['name']);
        $this->assertEquals('m@digitalsandwich.com', $response['user']['email']);

        $userId = $this->getPdo()->query("SELECT id FROM users WHERE username = 'mike.lively'")->fetchColumn(0);

        $this->assertEquals('/api/users/user/' . $userId, $this->result->getHeader('Location')[0]);

        $this->assertEquals('/api/users/user/' . $userId, $response['links']['self']);
        $this->assertEquals('/api/users/user/' . $userId, $response['links']['edit']);
        $this->assertEquals('/api/users', $response['links']['list']);
        $this->assertEquals('/api/users/user', $response['links']['create']);
    }

    public function testAddingEmptyUsername()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Username is required', $response['errors'][0]);
    }

    public function testAddingEmptyName()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Name is required', $response['errors'][0]);
    }

    public function testAddingEmptyEmail()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Email is required', $response['errors'][0]);
    }

    public function testAddingEmptyPasswords()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Password is required', $response['errors'][0]);
    }

    public function testAddingDuplicateUserByUsername()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'taken.user01',
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('This username is already registered. Please try another.', $response['errors'][0]);
    }

    public function testAddingDuplicateUserByEmail()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'email' => 'taken1@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('This email is already registered. Please try another.', $response['errors'][0]);
    }


    public function testAddingInvalidUsername()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively~~~~',
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Usernames must be less than 50 characters and can only contain a-z, A-Z, 0-9 or the characters . _ and -.', $response['errors'][0]);
    }

    public function testAddingInvalidEmail()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'email' => 'noemail',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('You must enter a valid email. Please try another.', $response['errors'][0]);
    }

    public function testAddingInvalidName()
    {
        $this->result = $this->apiClient->post('/api/users/user', [
            'form_params' => [
                'username' => 'mike.lively',
                'name' => str_repeat('abc', 100),
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ],
        ]);

        $this->assertEquals(400, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('Names can only be up to 100 characters long.', $response['errors'][0]);
    }

    public function testEditingUser()
    {
        $this->result = $this->apiClient->put('/api/users/user/1', [
            'form_params' => ([
                'username' => 'mike.lively',
                'name' => 'Mike Lively',
                'email' => 'm@digitalsandwich.com',
                'clear-password' => 'P@ssword',
            ]),
        ]);

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);


        $this->assertEquals('mike.lively', $response['user']['username']);
        $this->assertEquals('Mike Lively', $response['user']['name']);
        $this->assertEquals('m@digitalsandwich.com', $response['user']['email']);

        $this->assertEquals('/api/users/user/1', $response['links']['self']);
        $this->assertEquals('/api/users/user/1', $response['links']['edit']);
        $this->assertEquals('/api/users', $response['links']['list']);
        $this->assertEquals('/api/users/user', $response['links']['create']);
    }

    public function testRemovingUsers()
    {
        $this->result = $this->apiClient->delete('/api/users/user/1');

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);

        $this->assertEquals('/api/users', $response['links']['list']);
        $this->assertEquals('/api/users/user', $response['links']['create']);

        $this->result = $this->apiClient->get('/api/users/user/1');
        $this->assertEquals(404, $this->result->getStatusCode());
    }

    public function testAdjustingGroups()
    {
        $this->result = $this->apiClient->put('/api/users/user/1/groups', [
            'form_params' => ['groupIds' => [1, 2, 3]],
        ]);

        $this->assertEquals(201, $this->result->getStatusCode());

        $this->assertEquals('/api/users/user/1', $this->result->getHeader('Location')[0]);
    }
    
    public function testViewingUser()
    {

        $this->result = $this->apiClient->get('/api/users/user/9');

        $this->assertEquals(200, $this->result->getStatusCode());

        $response = json_decode($this->result->getBody()->getContents(), true);


        $this->assertEquals('taken.user09', $response['user']['username']);
        $this->assertEquals('Existing User 9', $response['user']['name']);
        $this->assertEquals('taken9@digitalsandwich.com', $response['user']['email']);
        $this->assertCount(1, $response['user']['user_groups']['groups']);
        $this->assertEquals(1, $response['user']['user_groups']['groups'][0]['group']['id']);
        $this->assertEquals('/api/group/1', $response['user']['user_groups']['groups'][0]['links']['self']);
        $this->assertEquals('/api/group/1', $response['user']['user_groups']['groups'][0]['links']['edit']);
        $this->assertEquals('/api/groups', $response['user']['user_groups']['links']['list']);

        $this->assertEquals('/api/users/user/9', $response['links']['self']);
        $this->assertEquals('/api/users/user/9', $response['links']['edit']);
        $this->assertEquals('/api/users', $response['links']['list']);
        $this->assertEquals('/api/users/user', $response['links']['create']);
    }


    /**
     * Saves the contents of the last response
     */
    public function onNotSuccessfulTest($e)
    {
        if (!empty($this->result))
        {
            $artifactDir = __DIR__ . '/../tests/artifacts/api/';
            if (!file_exists($artifactDir))
            {
                mkdir($artifactDir, 0777, true);
            }

            $contents = 'HTTP/' . $this->result->getProtocolVersion() . ' ' . $this->result->getStatusCode() . "\n";

            foreach ($this->result->getHeaders() as $name => $values)
            {
                foreach ($values as $value)
                {
                    $contents .= "{$name}: {$value}\n";
                }
            }
            $this->result->getBody()->rewind();
            $contents .= "\n" . $this->result->getBody()->getContents();

            file_put_contents($artifactDir . str_replace('\\', '_', get_class($this) . '::' . $this->getName()) . '.txt', $contents);
        }

        parent::onNotSuccessfulTest($e);
    }
}