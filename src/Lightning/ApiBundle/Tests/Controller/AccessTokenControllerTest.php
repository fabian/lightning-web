<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Entity\AccessToken;

class AccessTokenControllerTest extends ApiControllerTest
{
    public function testApprove()
    {
        $account = $this->createAccount();

        $accountList = new AccessToken($account);
        $accountList->setChallenge('1234');
        $accountList->setCreated(new \DateTime('now'));

        $this->em->persist($accountList);
        $this->em->flush();

        $crawler = $this->client->request('PUT', '/accounts/1/access_tokens/1', array('challenge' => '1234'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $token = $this->em
            ->getRepository('LightningApiBundle:AccessToken')
            ->find(1)
        ;

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

        $crawler = $this->client->request('PUT', '/accounts/1/access_tokens/1', array('challenge' => '9999'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $token = $this->em
            ->getRepository('LightningApiBundle:AccessToken')
            ->find(1)
        ;

        $this->assertFalse($token->getApproved());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }
}
