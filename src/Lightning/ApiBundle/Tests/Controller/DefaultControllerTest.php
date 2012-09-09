<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lists', array(), array(), array('HTTP_ACCEPT' => 'application/json'));

        $this->assertEquals('{"lists":[{"id":1,"title":"Example","created":"2012-09-09T17:06:11+0200","modified":"2012-09-09T17:06:11+0200"}]}', $client->getResponse()->getContent());
    }
}
