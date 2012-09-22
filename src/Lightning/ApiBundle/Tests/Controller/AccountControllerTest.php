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

    public function testAccess()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/12/test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
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

        $this->assertEquals('{"id":1,"url":"http:\/\/localhost\/accounts\/1","short_url":"http:\/\/localhost\/1\/abc","account":"http:\/\/localhost\/accounts\/1?secret=123","lists_url":"http:\/\/localhost\/accounts\/1\/lists"}', $client->getResponse()->getContent());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testShow()
    {
        $client = static::createClient();

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"id":1,"url":"http:\/\/localhost\/accounts\/1","short_url":"http:\/\/localhost\/1\/abc","lists_url":"http:\/\/localhost\/accounts\/1\/lists"}', $client->getResponse()->getContent());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShowNoAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":401,"message":"Account header not found."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongSecret()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account header authentication failed."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/999', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No account found for id 999."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();
        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/2', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testToken()
    {
        $client = static::createClient();

        $this->createAccount();

        $airship = $this->getMockBuilder('Lightning\ApiBundle\Service\UrbanAirship')
            ->disableOriginalConstructor()
            ->getMock();
        $airship->expects($this->once())
            ->method('register')
            ->with('ABC123', 'http://localhost/accounts/1');
        static::$kernel->getContainer()->set('lightning.api_bundle.service.urban_airship', $airship);

        $crawler = $client->request('PUT', '/accounts/1/tokens/ABC123', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('', $client->getResponse()->getContent());
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testTokenWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('PUT', '/accounts/999/tokens/ABC123', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":404,"message":"No account found for id 999."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTokenWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();
        $this->createAccount();

        $crawler = $client->request('PUT', '/accounts/2/tokens/ABC123', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}', trim($client->getResponse()->getContent()));
        $this->assertEquals('application/json', $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

}
