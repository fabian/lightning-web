<?php

namespace Lightning\WebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountsControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/a/test');

        $this->assertTrue($crawler->filter('html:contains("test")')->count() > 0);
    }
}
