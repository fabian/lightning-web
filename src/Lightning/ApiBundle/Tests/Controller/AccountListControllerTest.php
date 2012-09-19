<?php

namespace Lightning\ApiBundle\Tests\Controller;

class AccountListControllerTest extends ApiControllerTest
{
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = $this->createAccount();
    }

    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/accounts/1/lists', array('title' => 'Example'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"permission":"owner","deleted":false,"title":"Example","url":"http:\/\/localhost\/lists\/1"}', $client->getResponse()->getContent());
    }

    public function testCreateWrongOwnerId()
    {
        $client = static::createClient(array('debug' => false));

        $client->request('POST', '/accounts/99/lists', array('title' => 'Example'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No account found for id 99."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCreateWrongOwnerAccount()
    {
        $this->createAccount();

        $client = static::createClient(array('debug' => false));

        $client->request('POST', '/accounts/2/lists', array('title' => 'Example'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testShow()
    {
        $client = static::createClient();

        $this->createList($this->account);

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"lists":[{"permission":"owner","deleted":false,"title":"Groceries","url":"http:\/\/localhost\/lists\/1"}]}', $client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShowNoAccount()
    {
        $client = static::createClient(array('debug' => false));

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":401,"message":"Account header not found."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongSecret()
    {
        $client = static::createClient(array('debug' => false));

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account header authentication failed."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $crawler = $client->request('GET', '/accounts/999/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No account found for id 999."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/2/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
