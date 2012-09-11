<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ListControllerTest extends ApiControllerTest
{
    public function setUp()
    {
        parent::setUp();

        $this->createAccount();
    }

    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"lists":[]}', $client->getResponse()->getContent());
    }

    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/lists', array('title' => 'Example'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"id":1,"title":"Example"}', $client->getResponse()->getContent());

        $client->request('GET', '/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"lists":[{"id":1,"title":"Example","url":"http:\/\/localhost\/lists\/1"}]}', $client->getResponse()->getContent());
    }
}
