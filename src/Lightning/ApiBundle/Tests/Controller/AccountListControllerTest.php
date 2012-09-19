<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\ItemList;

class AccountListControllerTest extends ApiControllerTest
{
    public function testShow()
    {
        $client = static::createClient();

        $account = $this->createAccount();

        $list = new ItemList();
        $list->setTitle('Groceries');
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));

        $accountList = new AccountList();
        $accountList->setPermission('owner');
        $accountList->setRead(new \DateTime('now'));
        $accountList->setPushed(new \DateTime('now'));
        $accountList->setCreated(new \DateTime('now'));
        $accountList->setModified(new \DateTime('now'));
        $accountList->setList($list);
        $accountList->setAccount($account);

        $this->em->persist($accountList);
        $this->em->persist($list);
        $this->em->flush();

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('[{"id":1,"permission":"owner","deleted":false,"account":{"id":1},"list":{"id":1,"title":"Groceries"},"url":"http:\/\/localhost\/lists\/1"}]', $client->getResponse()->getContent());
    }

    public function testShowNoAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"error":{"code":401,"message":"Account header not found."}}', trim($client->getResponse()->getContent()));
    }

    public function testShowWrongSecret()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/1/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"error":{"code":403,"message":"Account header authentication failed."}}', trim($client->getResponse()->getContent()));
    }

    public function testShowWrongId()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/999/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"error":{"code":404,"message":"No account found for id 999."}}', trim($client->getResponse()->getContent()));
    }

    public function testShowWrongAccount()
    {
        $client = static::createClient(array('debug' => false));

        $this->createAccount();
        $this->createAccount();

        $crawler = $client->request('GET', '/accounts/2/lists', array(), array(), array(
            'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
            'HTTP_ACCEPT' => 'application/json',
        ));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"error":{"code":403,"message":"Account 2 doesn\'t match authenticated account."}}', trim($client->getResponse()->getContent()));
    }
}
