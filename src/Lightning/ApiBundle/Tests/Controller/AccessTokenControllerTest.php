<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Entity\AccessToken;

class AccessTokenControllerTest extends ApiControllerTest
{
    public function testAccess()
    {
        $account = $this->createAccount();

        $crawler = $this->client->request(
            'GET',
            '/1/abc'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAccessToken()
    {
        $account = $this->createAccount();

        $random = $this->getMock('Lightning\ApiBundle\Service\Random');
        $random->expects($this->any())
            ->method('challenge')
            ->will($this->returnValue('9876'));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.random', $random);

        $crawler = $this->client->request(
            'POST',
            '/1/abc'
        );

        $token = $this->em
            ->getRepository('LightningApiBundle:AccessToken')
            ->find(1);

        $this->assertEquals(9876, $token->getChallenge());

        $response = $this->client->getResponse();
        $this->assertEquals('{"challenge":"9876"}', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApprove()
    {
        $account = $this->createAccount();

        $accountList = new AccessToken($account);
        $accountList->setChallenge('1234');
        $accountList->setCreated(new \DateTime('now'));

        $this->em->persist($accountList);
        $this->em->flush();

        $crawler = $this->client->request(
            'PUT',
            '/accounts/1/access_tokens/1',
            array('challenge' => '1234'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $token = $this->em
            ->getRepository('LightningApiBundle:AccessToken')
            ->find(1);

        $this->assertTrue($token->getApproved());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testApproveWrongChallenge()
    {
        $account = $this->createAccount();

        $accountList = new AccessToken($account);
        $accountList->setChallenge('1234');
        $accountList->setCreated(new \DateTime('now'));

        $this->em->persist($accountList);
        $this->em->flush();

        $crawler = $this->client->request(
            'PUT',
            '/accounts/1/access_tokens/1',
            array('challenge' => '9999'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $token = $this->em
            ->getRepository('LightningApiBundle:AccessToken')
            ->find(1);

        $this->assertFalse($token->getApproved());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testApproveWrongId()
    {
        $account = $this->createAccount();

        $crawler = $this->client->request(
            'PUT',
            '/accounts/1/access_tokens/2',
            array('challenge' => '9999'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"error":{"code":404,"message":"No token found for id 2."}}',
            trim($response->getContent())
        );
        $this->assertEquals(404, $response->getStatusCode());
    }
}
