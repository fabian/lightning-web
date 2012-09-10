<?php

namespace Lightning\WebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test');

        $this->assertTrue($crawler->filter('html:contains("test")')->count() > 0);
    }
}
