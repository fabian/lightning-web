<?php

namespace Lightning\ApiBundle\Tests\Controller;

class AccountControllerTest extends ApiControllerTest
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/12/test');

        $this->assertTrue($crawler->filter('html:contains("test")')->count() > 0);
    }

    public function testCreate()
    {
        $client = static::createClient();

        $random = $this->getMock('Lightning\ApiBundle\Service\Random');
        $random->expects($this->any())
            ->method('code')
            ->will($this->returnValue('abc'));
        $random->expects($this->any())
            ->method('secret')
            ->will($this->returnValue('123'));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.random', $random);

        $crawler = $client->request('POST', '/accounts');

        $this->assertEquals('{"id":1,"url":"http:\/\/localhost\/accounts\/1","short":"http:\/\/localhost\/1\/abc","account":"http:\/\/localhost\/accounts\/1?secret=123"}', $client->getResponse()->getContent());
    }

    public function testShow()
    {
        $client = static::createClient();

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"id":1,"url":"http:\/\/localhost\/accounts\/1","short":"http:\/\/localhost\/1\/abc"}', $client->getResponse()->getContent());
    }

    public function testShowNoAccount()
    {
        $client = static::createClient();

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('403', $client->getResponse()->getStatusCode());
    }

    public function testShowWrongSecret()
    {
        $client = static::createClient();

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('403', $client->getResponse()->getStatusCode());
    }

    public function testShowWrongId()
    {
        $client = static::createClient();

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/999', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('404', $client->getResponse()->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $client = static::createClient();

        $this->createAccount();
        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/2', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('403', $client->getResponse()->getStatusCode());
    }
}
