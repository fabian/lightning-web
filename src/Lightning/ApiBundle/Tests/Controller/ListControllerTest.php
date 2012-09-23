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
        $crawler = $this->client->request('GET', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"id":1,"title":"Groceries","url":"http:\/\/localhost\/lists\/1"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowWrongId()
    {
        $crawler = $this->client->request('GET', '/lists/999', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":404,"message":"No list found for id 999."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $this->createAccount();

        $crawler = $this->client->request('GET', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $crawler = $this->client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $list = $this->em
            ->getRepository('LightningApiBundle:ItemList')
            ->find(1)
        ;

        $this->assertEquals('Todos', $list->getTitle());

        $response = $this->client->getResponse();
        $this->assertEquals('{"id":1,"title":"Todos","url":"http:\/\/localhost\/lists\/1"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateWrongId()
    {
        $crawler = $this->client->request('PUT', '/lists/999', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":404,"message":"No list found for id 999."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateWrongAccount()
    {
        $this->createAccount();

        $crawler = $this->client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdateNotOwnerAccount()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $crawler = $this->client->request('PUT', '/lists/1', array('title' => 'Todos'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 is not owner of list."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdateConflict()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $crawler = $this->client->request('PUT', '/lists/1', array('title' => 'Todos', 'modified' => '2012-02-01T12:00:00+02:00'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":409,"message":"Conflict, list has later modification."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testDelete()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());
        $this->em->clear();

        $crawler = $this->client->request('DELETE', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWrongId()
    {
        $crawler = $this->client->request('DELETE', '/lists/999', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":404,"message":"No list found for id 999."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteWrongAccount()
    {
        $this->createAccount();

        $crawler = $this->client->request('DELETE', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDeleteNotOwnerAccount()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $crawler = $this->client->request('DELETE', '/lists/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":{"code":403,"message":"Authenticated account 2 is not owner of list."}}', trim($response->getContent()));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }
}
