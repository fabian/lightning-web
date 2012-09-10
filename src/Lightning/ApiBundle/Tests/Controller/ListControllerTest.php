<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Entity\Account;

class ListControllerTest extends ApiControllerTest
{
    public function setUp()
    {
        parent::setUp();

        $account = new Account();
        $account->setCode('abc');
        $account->setCreated(new \DateTime('now'));
        $account->setModified(new \DateTime('now'));

        $factory = static::$kernel->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder($account);
        $password = $encoder->encodePassword('123', $account->getSalt());
        $account->setSecret($password);

        $this->em->persist($account);
        $this->em->flush();
    }

    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals('{"lists":[]}', $client->getResponse()->getContent());
    }

    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/lists', array('title' => 'Example'), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"id":1,"title":"Example"}', $client->getResponse()->getContent());

        $crawler = $client->request('GET', '/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"lists":[{"id":1,"title":"Example"}]}', $client->getResponse()->getContent());
    }
}
