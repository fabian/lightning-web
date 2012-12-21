<?php

namespace Lightning\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\AccessToken;
use Lightning\ApiBundle\Entity\Item;
use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\Log;

abstract class AbstractTest extends WebTestCase
{
    const NOW = '2012-02-29T12:00:00+02:00';

    protected $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setUp()
    {
        static::$kernel = static::createKernel(array('debug' => false));
        static::$kernel->boot();

        $this->client = static::$kernel->getContainer()->get('test.client');
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tool = new SchemaTool($this->em);

        $classes = array(
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\Item'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\ItemList'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\Account'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\AccountList'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\AccessToken'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\Log'),
        );
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $calendar = $this->getMock('Lightning\ApiBundle\Service\Calendar');
        $calendar->expects($this->any())
            ->method('createDateTime')
            ->will($this->returnValue(new \DateTime(self::NOW)));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.calendar', $calendar);
    }

    protected function createAccount($expiry = null)
    {
        $account = new Account();
        $account->setCode('abc');
        $account->setSalt('123');
        $account->setSecret('6607dfa9e28a363016862c8cb03d797c953fa8c7'); // secret 123
        $account->setCreated(new \DateTime(self::NOW));
        $account->setModified(new \DateTime(self::NOW));
        $account->setExpiry($expiry ?: new \DateTime(self::NOW));

        $this->em->persist($account);
        $this->em->flush();

        return $account;
    }

    protected function createAccessToken($account, $approved = true)
    {
        $token = new AccessToken();
        $token->setAccount($account);
        $token->setApproved($approved);
        $token->setChallenge('6789');
        $token->setCreated(new \DateTime(self::NOW));

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param Account $account
     */
    protected function createList($account, $modified = null)
    {
        $list = new ItemList();
        $list->setTitle('Groceries');
        $list->setInvitation('Welcome123');
        $list->setCreated(new \DateTime(self::NOW));
        $list->setModified($modified ?: new \DateTime(self::NOW));

        $date = new \DateTime(self::NOW);
        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_OWNER);
        $accountList->setRead($date);
        $accountList->setPushed($date);
        $accountList->setCreated($date);
        $accountList->setModified($date);

        $this->em->persist($list);
        $this->em->flush();
        $this->em->persist($accountList);
        $this->em->flush();

        return $accountList;
    }

    /**
     * @param Account $account
     */
    protected function createAccountList($account, $list)
    {
        $date = new \DateTime(self::NOW);
        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_GUEST);
        $accountList->setRead($date);
        $accountList->setPushed($date);
        $accountList->setCreated($date);
        $accountList->setModified($date);

        $this->em->persist($accountList);
        $this->em->flush();

        return $accountList;
    }

    protected function createItem($list, $value = 'Milk', $deleted = false)
    {
        $date = new \DateTime(self::NOW);
        $item = new Item($list);
        $item->setValue($value);
        $item->setDeleted($deleted);
        $item->setCreated($date);
        $item->setModified($date);

        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    protected function createLog($account, $item, $action, $old = null, $happened = null)
    {
        $log = new Log($account, $item);
        $log->setAction($action);
        $log->setOld($old);
        $log->setHappened(new \DateTime($happened));

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }
}
