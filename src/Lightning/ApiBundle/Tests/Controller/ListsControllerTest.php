<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ListsControllerTest extends ApiControllerTest
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lists', array(), array(), array('HTTP_ACCEPT' => 'application/json'));

        $this->assertEquals('{"lists":[]}', $client->getResponse()->getContent());
    }

    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/lists', array('title' => 'Example'), array(), array('HTTP_ACCEPT' => 'application/json'));
        $this->assertEquals('{"id":1,"title":"Example"}', $client->getResponse()->getContent());

        $crawler = $client->request('GET', '/lists', array(), array(), array('HTTP_ACCEPT' => 'application/json'));
        $this->assertEquals('{"lists":[{"id":1,"title":"Example"}]}', $client->getResponse()->getContent());
    }
}
