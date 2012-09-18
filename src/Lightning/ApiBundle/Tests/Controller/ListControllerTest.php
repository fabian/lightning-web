<?php

namespace Lightning\ApiBundle\Tests\Controller;

class ListControllerTest extends ApiControllerTest
{
    public function setUp()
    {
        parent::setUp();

        $this->createAccount();
    }

    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/lists', array('title' => 'Example', 'owner' => 1), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));
        $this->assertEquals('{"id":1,"title":"Example"}', $client->getResponse()->getContent());

        $accountList = $this->em
            ->getRepository('LightningApiBundle:AccountList')
            ->find(1);

        $this->assertEquals('Example', $accountList->getList()->getTitle());
    }
}
