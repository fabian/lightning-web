<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Tests\AbstractTest;

class ItemControllerTest extends AbstractTest
{
    protected $list;

    public function setUp()
    {
        parent::setUp();

        $account = $this->createAccount();
        $this->list = $this->createList($account)->getList();
    }

    public function testIndex()
    {
        $this->createItem($this->list); // normal item
        $this->createItem($this->list, 'Bread', true); // deleted item

        $this->client->request(
            'GET',
            '/lists/1/items',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"items":[{"id":1,"value":"Milk","done":false,"deleted":false,"url":"http:\/\/localhost\/items\/1"}]}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
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

    public function testShowWrongAccount()
    {
        $this->createItem($this->list);
        $this->createAccount();

        $this->client->request(
            'GET',
            '/items/1',
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
        $this->createItem($this->list);

        $this->client->request(
            'PUT',
            '/items/1',
            array('value' => 'Coffee', 'done' => '1', 'modified' => '2012-02-29T13:00:00+02:00'),
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
        $this->assertEquals('2012-02-29 13:00:00', $item->getList()->getModified()->format('Y-m-d H:i:s'));

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

    public function testUpdateWrongId()
    {
        $this->createItem($this->list);

        $this->client->request(
            'PUT',
            '/items/999',
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

        $this->assertEquals('Milk', $item->getValue());
        $this->assertFalse($item->getDone());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No item found for id 999."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateWrongAccount()
    {
        $this->createItem($this->list);
        $this->createAccount();

        $this->client->request(
            'PUT',
            '/items/1',
            array('value' => 'Coffee', 'done' => '1'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
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
            '{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDelete()
    {
        $this->createItem($this->list);

        $this->client->request(
            'DELETE',
            '/items/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $item = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertTrue($item->getDeleted());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWrongId()
    {
        $this->createItem($this->list);

        $this->client->request(
            'DELETE',
            '/items/999',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $item = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertFalse($item->getDeleted());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No item found for id 999."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteWrongAccount()
    {
        $this->createItem($this->list);
        $this->createAccount();

        $this->client->request(
            'DELETE',
            '/items/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $item = $this->em
            ->getRepository('LightningApiBundle:Item')
            ->find(1);

        $this->assertFalse($item->getDeleted());

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Authenticated account 2 has no access to list."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }
}
