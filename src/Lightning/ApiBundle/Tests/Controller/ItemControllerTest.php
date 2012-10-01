<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ItemControllerTest extends ApiControllerTest
{
    protected $list;

    public function setUp()
    {
        parent::setUp();

        $account = $this->createAccount();
        $this->list = $this->createList($account)->getList();
    }

    public function testCreate()
    {
        $this->client->request(
            'POST',
            '/lists/1/items',
            array('value' => 'Milk'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"value":"Milk","done":false,"deleted":false,"url":"http:\/\/localhost\/items\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(201, $response->getStatusCode());

        $list = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertEquals('Milk', $list->getValue());
    }

    public function testCreateWrongList()
    {
        $this->client->request(
            'POST',
            '/lists/999/items',
            array('value' => 'Milk'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No list found for id 999."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateWrongAccount()
    {
        $this->createAccount();

        $this->client->request(
            'POST',
            '/lists/1/items',
            array('value' => 'Milk'),
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

    public function testShow()
    {
        $this->createItem($this->list);

        $this->client->request(
            'GET',
            '/items/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"value":"Milk","done":false,"deleted":false,"url":"http:\/\/localhost\/items\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowWrongId()
    {
        $this->client->request(
            'GET',
            '/items/999',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No item found for id 999."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $this->createItem($this->list);

        $this->client->request(
            'PUT',
            '/items/1',
            array('value' => 'Coffee', 'done' => '1'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $item = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertEquals('Coffee', $item->getValue());
        $this->assertTrue($item->getDone());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"value":"Coffee","done":true,"deleted":false,"url":"http:\/\/localhost\/items\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateConflict()
    {
        $this->createItem($this->list);

        $this->client->request(
            'PUT',
            '/items/1',
            array('value' => 'Coffee', 'done' => '1', 'modified' => '2012-02-01T12:00:00+02:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $item = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertEquals('Milk', $item->getValue());
        $this->assertFalse($item->getDone());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":409,"message":"Conflict, list has later modification."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(409, $response->getStatusCode());
    }
}
