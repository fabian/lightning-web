<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

use Lightning\ApiBundle\Entity\Account;

abstract class ApiControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tool = new SchemaTool($this->em);

        $classes = array(
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\ItemList'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\Account'),
            $this->em->getClassMetadata('Lightning\ApiBundle\Entity\AccountList'),
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
}
