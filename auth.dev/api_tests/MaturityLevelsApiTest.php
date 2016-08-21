<?php

namespace PhpProjects\AuthDev;


use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class MaturityLevelsApiTest extends TestCase
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

        $this->apiClient = new \GuzzleHttp\Client([
            'base_uri' => 'http://auth.dev/',
            'allow_redirects' => false,
            'auth' => [ 'taken.user09', 'P@ssw0rd' ],
            'cookies' => $cookieJar,
            'http_errors' => false,
        ]);
    }

    /**
     * Level 0 in the richardson maturity model is that you are at least using http. Everything is however flowing through a single endpoint.
     */
    public function testLevel0()
    {
        $listUsersResponse = $this->apiClient->post('/api', [
            'body' => http_build_query([
                'method' => 'listUsers'
            ])
        ]);

        $this->assertEquals(200, $listUsersResponse->getStatusCode());
        $this->assertEquals([
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
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $eidtUserResponse = $this->apiClient->post('/api', [
            'body' => http_build_query([
                'method' => 'editUser',
                'username' => 'taken.user01',
                'newUsername' => 'taken.user01',
                'email' => 'newemail@digitalsandwich.com',
                'name' => 'Existing User 1',
                'password' => 'password hash',
            ])
        ]);

        $this->assertEquals(200, $eidtUserResponse->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $getGroupsResponse = $this->apiClient->post('/api', [
            'body' => http_build_query([
                'method' => 'listGroups'
            ])
        ]);

        $this->assertEquals(200, $getGroupsResponse->getStatusCode());
        $this->assertEquals([
            [ 'id' => 1, 'name' => 'Group 1', ],
            [ 'id' => 2, 'name' => 'Group 2', ],
            [ 'id' => 3, 'name' => 'Group 3', ],
            [ 'id' => 4, 'name' => 'Group 4', ],
            [ 'id' => 5, 'name' => 'Group 5', ],

        ], json_decode($getGroupsResponse->getBody()->getContents()));
    }

    /**
     * Level 1 builds on level 0 by adding endpoints for each resource
     */
    public function testLevel1()
    {
        $listUsersResponse = $this->apiClient->post('/api/users', [
            'body' => http_build_query([
                'method' => 'list',
            ])
        ]);

        $this->assertEquals(200, $listUsersResponse->getStatusCode());
        $this->assertEquals([
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
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $eidtUserResponse = $this->apiClient->post('/api/user/taken.user01', [
            'body' => http_build_query([
                'method' => 'edit',
                'username' => 'taken.user01',
                'email' => 'newemail@digitalsandwich.com',
                'name' => 'Existing User 1',
                'password' => 'password hash',
            ])
        ]);

        $this->assertEquals(200, $eidtUserResponse->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $getGroupsResponse = $this->apiClient->post('/api/groups', [
            'body' => http_build_query([
                'method' => 'list',
            ])
        ]);

        $this->assertEquals(200, $getGroupsResponse->getStatusCode());
        $this->assertEquals([
            [ 'id' => 1, 'name' => 'Group 1', ],
            [ 'id' => 2, 'name' => 'Group 2', ],
            [ 'id' => 3, 'name' => 'Group 3', ],
            [ 'id' => 4, 'name' => 'Group 4', ],
            [ 'id' => 5, 'name' => 'Group 5', ],

        ], json_decode($getGroupsResponse->getBody()->getContents()));
    }

    /**
     * Level 2 builds on level 1 by using http methods to closely represent the verbs being used with each entity.
     */
    public function testLevel2()
    {
        $listUsersResponse = $this->apiClient->get('/api/users');

        $this->assertEquals(200, $listUsersResponse->getStatusCode());
        $this->assertEquals([
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
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $eidtUserResponse = $this->apiClient->put('/api/user/taken.user01', [
            'body' => http_build_query([
                'username' => 'taken.user01',
                'email' => 'newemail@digitalsandwich.com',
                'name' => 'Existing User 1',
                'password' => 'password hash',
            ])
        ]);

        $this->assertEquals(200, $eidtUserResponse->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $getGroupsResponse = $this->apiClient->get('/api/groups');

        $this->assertEquals(200, $getGroupsResponse->getStatusCode());
        $this->assertEquals([
            [ 'id' => 1, 'name' => 'Group 1', ],
            [ 'id' => 2, 'name' => 'Group 2', ],
            [ 'id' => 3, 'name' => 'Group 3', ],
            [ 'id' => 4, 'name' => 'Group 4', ],
            [ 'id' => 5, 'name' => 'Group 5', ],

        ], json_decode($getGroupsResponse->getBody()->getContents()));
    }

    /**
     * Level 3 builds on level 2 by using linking to allow for api discovery and navigation
     */
    public function testLevel3()
    {
        $listUsersResponse = $this->apiClient->get('/api/users');

        $this->assertEquals(200, $listUsersResponse->getStatusCode());
        $this->assertEquals([
            'users' => [
                [
                    'user' => [ 'id' => 1, 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1', 'password' => $this->hash],
                    'links' => [
                        'self' => '/api/user/taken.user01',
                        'edit   ' => '/api/user/taken.user01',
                    ],
                ],
                [
                    'user' => ['id' => 2, 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2', 'password' => $this->hash],
                    'links' => [
                        'self' => '/api/user/taken.user02',
                        'edit   ' => '/api/user/taken.user02',
                    ],
                ],
            ],
            'links' => [
                'next' => '/api/users?page=2',
                'search' => '/api/users?q={searchTerm}',
            ],
            // ...
        ], json_decode($listUsersResponse->getBody()->getContents()));

        $eidtUserResponse = $this->apiClient->put('/api/user/taken.user01', [
            'body' => http_build_query([
                'username' => 'taken.user01',
                'email' => 'newemail@digitalsandwich.com',
                'name' => 'Existing User 1',
                'password' => 'password hash',
            ])
        ]);

        $this->assertEquals(200, $eidtUserResponse->getStatusCode());
        $this->assertEquals([
            'user' => [ 
                'id' => 1, 
                'username' => 'taken.user01', 
                'email' => 'newemail@digitalsandwich.com', 
                'name' => 'Existing User 1', 
                'password' => $this->hash
            ],
            'links' => [
                'self' => '/api/user/taken.user01',
                'edit   ' => '/api/user/taken.user01',
            ],
        ], json_decode($listUsersResponse->getBody()->getContents()));
    }
}