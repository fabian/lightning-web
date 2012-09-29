<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ItemControllerTest extends ApiControllerTest
{
    protected $accountList;

    public function setUp()
    {
        parent::setUp();

        $account = $this->createAccount();
        $this->createList($account);
    }

    public function testCreate()
    {
        $crawler = $this->client->request(
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
            '{"id":1,"value":"Milk","done":false,"deleted":false,"url":"http:\/\/localhost\/items\/1?list=1"}',
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
        $crawler = $this->client->request(
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

        $crawler = $this->client->request(
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
}
