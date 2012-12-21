<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Tests\AbstractTest;

class ListControllerTest extends AbstractTest
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
        $this->client->request(
            'GET',
            '/lists/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"title":"Groceries","modified":"2012-02-29T12:00:00+0200","url":"http:\/\/localhost\/lists\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $this->createAccount();

        $this->client->request(
            'GET',
            '/lists/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $this->client->request(
            'PUT',
            '/lists/1',
            array('title' => 'Todos', 'modified' => '2012-05-25T13:00:00+02:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $list = $this->em
            ->getRepository('LightningApiBundle:ItemList')
            ->find(1);

        $this->assertEquals('Todos', $list->getTitle());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"title":"Todos","modified":"2012-02-29T12:00:00+0200","url":"http:\/\/localhost\/lists\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateWrongAccount()
    {
        $this->createAccount();

        $this->client->request(
            'PUT',
            '/lists/1',
            array('title' => 'Todos'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdateNotOwnerAccount()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $this->client->request(
            'PUT',
            '/lists/1',
            array('title' => 'Todos'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 is not owner of list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUpdateConflict()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $this->client->request(
            'PUT',
            '/lists/1',
            array('title' => 'Todos', 'modified' => '2012-02-01T12:00:00+02:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":409,"message":"Conflict, list has later modification."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testDelete()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());
        $this->em->clear();

        $this->client->request(
            'DELETE',
            '/lists/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $lists = $this->em
            ->getRepository('LightningApiBundle:ItemList')
            ->findAll();

        $this->assertCount(0, $lists);

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWrongAccount()
    {
        $this->createAccount();

        $this->client->request(
            'DELETE',
            '/lists/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDeleteNotOwnerAccount()
    {
        $account = $this->createAccount();
        $this->createAccountList($account, $this->accountList->getList());

        $this->client->request(
            'DELETE',
            '/lists/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 is not owner of list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }
}
