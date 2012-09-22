<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ListControllerTest extends ApiControllerTest
{
    protected $accountList;

    public function setUp()
    {
        parent::setUp();

        $account = $this->createAccount();
        $this->accountList = $this->createList($account);
    }

    public function testShow()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"id":1,"title":"Groceries","url":"http:\/\/localhost\/lists\/1"}', $client->getResponse()->getContent());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $crawler = $client->request('GET', '/lists/999', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No list found for id 999."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        $client = static::createClient();

        $crawler = $client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"id":1,"title":"Todos","url":"http:\/\/localhost\/lists\/1"}', $client->getResponse()->getContent());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdateWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $crawler = $client->request('PUT', '/lists/999', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No list found for id 999."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUpdateWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testUpdateNotOwnerAccount()
    {
        $client = static::createClient(array('debug' => false));

        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $crawler = $client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 is not owner of list."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
