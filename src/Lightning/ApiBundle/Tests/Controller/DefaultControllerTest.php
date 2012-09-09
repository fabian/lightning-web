<?php

namespace Lightning\ApiBundle\Tests\Controller;

class DefaultControllerTest extends ApiControllerTest
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lists', array(), array(), array('HTTP_ACCEPT' => 'application/json'));

        $this->assertEquals('{"lists":[{"id":1,"title":"Example"}]}', $client->getResponse()->getContent());
    }
}
