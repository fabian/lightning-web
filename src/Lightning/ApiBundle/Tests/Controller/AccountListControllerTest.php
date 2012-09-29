<?php

namespace Lightning\ApiBundle\Tests\Controller;

class AccountListControllerTest extends ApiControllerTest
{
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = $this->createAccount();
    }

    public function testCreate()
    {
        $this->client->request(
            'POST',
            '/accounts/1/lists',
            array('title' => 'Example'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"permission":"owner","deleted":false,"title":"Example","url":"http:\/\/localhost\/lists\/1"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateWrongOwnerId()
    {
        $this->client->request(
            'POST',
            '/accounts/99/lists',
            array('title' => 'Example'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No account found for id 99."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateWrongOwnerAccount()
    {
        $this->createAccount();

        $this->client->request(
            'POST',
            '/accounts/2/lists',
            array('title' => 'Example'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testIndex()
    {
        $this->createList($this->account);
        $this->em->clear();

        $crawler = $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"lists":[{"permission":"owner","deleted":false,"title":"Groceries","url":"http:\/\/localhost\/lists\/1"}]}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexNoAccount()
    {
        $crawler = $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":401,"message":"Account header not found."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIndexWrongSecret()
    {
        $crawler = $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Account header authentication failed."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testIndexWrongId()
    {
        $crawler = $this->client->request(
            'GET',
            '/accounts/999/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No account found for id 999."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testIndexWrongAccount()
    {
        $this->createAccount();

        $crawler = $this->client->request(
            'GET',
            '/accounts/2/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }
}
