<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Tests\AbstractTest;

class AccountControllerTest extends AbstractTest
{
    public function testCreate()
    {
        $random = $this->getMock('Lightning\ApiBundle\Service\Random');
        $random->expects($this->any())
            ->method('code')
            ->will($this->returnValue('abc'));
        $random->expects($this->any())
            ->method('secret')
            ->will($this->returnValue('123'));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.random', $random);

        $this->client->request('POST', '/accounts');

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"expiry":"2012-02-29T12:00:00+0000","url":"http:\/\/localhost\/accounts\/1","short_url":"http:\/\/localhost\/1\/abc","account":"http:\/\/localhost\/accounts\/1?secret=123","lists_url":"http:\/\/localhost\/accounts\/1\/lists"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testShow()
    {
        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"id":1,"expiry":"2012-10-01T12:00:00+0000","url":"http:\/\/localhost\/accounts\/1","short_url":"http:\/\/localhost\/1\/abc","lists_url":"http:\/\/localhost\/accounts\/1\/lists"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowNoAccount()
    {
        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":401,"status_text":"Unauthorized","current_content":"","message":"Account header not found."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShowWrongSecret()
    {
        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account header authentication failed."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testShowWrongAccount()
    {
        $this->createAccount();
        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/2',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAccessToken()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account);

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/1?challenge=6789',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAccessTokenNotApproved()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account, false);

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/1?challenge=6789',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAccessTokenWrongChallenge()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account);

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/1?challenge=1111',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAccessTokenWrongId()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account);

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/2?challenge=6789',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAccessTokenWrongAccount()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account);

        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/2',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/1?challenge=6789',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}', trim($response->getContent()));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAccessTokenExpired()
    {
        $account = $this->createAccount();
        $this->createAccessToken($account, true, new \DateTime('2012-01-01T12:00:00+0000'));

        $this->client->request(
            'GET',
            '/accounts/1',
            array(),
            array(),
            array(
                'HTTP_ACCESSTOKEN' => 'http://localhost/accounts/1/access_tokens/1?challenge=6789',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('{"status":"error","status_code":401,"status_text":"Unauthorized","current_content":"","message":"Account header not found."}', trim($response->getContent()));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testDeviceToken()
    {
        $this->createAccount();

        $airship = $this->getMockBuilder('Lightning\ApiBundle\Service\UrbanAirship')
            ->disableOriginalConstructor()
            ->getMock();
        $airship->expects($this->once())
            ->method('register')
            ->with('ABC123', 'http://localhost/accounts/1');
        static::$kernel->getContainer()->set('lightning.api_bundle.service.urban_airship', $airship);

        $this->client->request(
            'PUT',
            '/accounts/1/device_tokens/ABC123',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeviceTokenException()
    {
        $this->createAccount();

        $airship = $this->getMockBuilder('Lightning\ApiBundle\Service\UrbanAirship')
            ->disableOriginalConstructor()
            ->getMock();
        $airship->expects($this->once())
            ->method('register')
            ->with('ABC123', 'http://localhost/accounts/1')
            ->will($this->throwException(new \RuntimeException('Internal error')));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.urban_airship', $airship);

        $this->client->request(
            'PUT',
            '/accounts/1/device_tokens/ABC123',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":500,"status_text":"Internal Server Error","current_content":"","message":""}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testDeviceTokenWrongAccount()
    {
        $this->createAccount();
        $this->createAccount();

        $this->client->request(
            'PUT',
            '/accounts/2/device_tokens/ABC123',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testVerify()
    {
        $this->createAccount();

        $purchased = new \DateTime('2012-12-31T12:00:00+0000');

        $appStore = $this->getMockBuilder('Lightning\ApiBundle\Service\AppStore')
            ->disableOriginalConstructor()
            ->getMock();
        $appStore->expects($this->once())
            ->method('verify')
            ->with('ABC123')
            ->will($this->returnValue(array('ch.lightningapp.oneyear', $purchased)));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.app_store', $appStore);

        $this->client->request(
            'PUT',
            '/accounts/1/receipt',
            array(
                'data' => 'ABC123',
            ),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $account = $this->em
            ->getRepository('LightningApiBundle:Account')
            ->find(1);

        $this->assertEquals($purchased, $account->getExpiry());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }
}
