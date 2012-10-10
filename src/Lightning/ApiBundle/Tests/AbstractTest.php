<?php

namespace Lightning\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\Item;
use Lightning\ApiBundle\Entity\ItemList;

abstract class AbstractTest extends WebTestCase
{
    protected $client;

    protected $doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setUp()
    {
        static::$kernel = static::createKernel(array('debug' => false));
        static::$kernel->boot();

        $this->client = static::$kernel->getContainer()->get('test.client');
        $this->doctrine = static::$kernel->getContainer()->get('doctrine');
        $this->em = $this->doctrine->getEntityManager();

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
    }

    protected function createAccount()
    {
        $account = new Account();
        $account->setCode('abc');
        $account->setSalt('123');
        $account->setSecret('6607dfa9e28a363016862c8cb03d797c953fa8c7'); // secret 123
        $account->setCreated(new \DateTime('now'));
        $account->setModified(new \DateTime('now'));

        $this->em->persist($account);
        $this->em->flush();

        return $account;
    }

    /**
     * @param Account $account
     */
    protected function createList($account)
    {
        $list = new ItemList();
        $list->setTitle('Groceries');
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));

        $date = new \DateTime('2012-02-29T12:00:00+02:00');
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
        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_GUEST);
        $accountList->setRead(new \DateTime('now'));
        $accountList->setPushed(new \DateTime('now'));
        $accountList->setCreated(new \DateTime('now'));
        $accountList->setModified(new \DateTime('now'));

        $this->em->persist($accountList);
        $this->em->flush();

        return $accountList;
    }

    protected function createItem($list, $deleted = false)
    {
        $item = new Item($list);
        $item->setValue('Milk');
        $item->setDeleted($deleted);
        $item->setCreated(new \DateTime('now'));
        $item->setModified(new \DateTime('now'));

        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }
}
