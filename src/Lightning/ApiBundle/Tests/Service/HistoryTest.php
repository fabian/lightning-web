<?php

namespace Lightning\ApiBundle\Tests\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use JMS\DiExtraBundle\Annotation\Service;
use Lightning\ApiBundle\Service\History;
use Lightning\ApiBundle\Entity\Log;
use Lightning\ApiBundle\Tests\AbstractTest;

class HistoryTest extends AbstractTest
{
    protected $item;

    public function setUp()
    {
        parent::setUp();

        $this->history = new History($this->em);

        $account = $this->createAccount();
        $list = $this->createList($account)->getList();
        $this->item = $this->createItem($list);
    }

    public function testAdded()
    {
        $this->history->added($this->item);
        $this->em->flush();

        $log = $this->em
            ->getRepository('LightningApiBundle:Log')
            ->find(1);

        $this->assertEquals(Log::ACTION_ADDED, $log->getAction());
        $this->assertEquals($this->item, $log->getItem());
        $this->assertNull($log->getOld());
        $this->assertInstanceOf('\DateTime', $log->getHappened());
    }

    public function testModified()
    {
        $this->history->modified($this->item, 'Bread');
        $this->em->flush();

        $log = $this->em
            ->getRepository('LightningApiBundle:Log')
            ->find(1);

        $this->assertEquals(Log::ACTION_MODIFIED, $log->getAction());
        $this->assertEquals($this->item, $log->getItem());
        $this->assertEquals('Bread', $log->getOld());
        $this->assertInstanceOf('\DateTime', $log->getHappened());
    }

    public function testCompleted()
    {
        $this->history->completed($this->item);
        $this->em->flush();

        $log = $this->em
            ->getRepository('LightningApiBundle:Log')
            ->find(1);

        $this->assertEquals(Log::ACTION_COMPLETED, $log->getAction());
        $this->assertEquals($this->item, $log->getItem());
        $this->assertNull($log->getOld());
        $this->assertInstanceOf('\DateTime', $log->getHappened());
    }

    public function testDelete()
    {
        $this->history->deleted($this->item);
        $this->em->flush();

        $log = $this->em
            ->getRepository('LightningApiBundle:Log')
            ->find(1);

        $this->assertEquals(Log::ACTION_DELETED, $log->getAction());
        $this->assertEquals($this->item, $log->getItem());
        $this->assertNull($log->getOld());
        $this->assertInstanceOf('\DateTime', $log->getHappened());
    }
}
