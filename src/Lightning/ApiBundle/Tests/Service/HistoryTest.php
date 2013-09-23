<?php

namespace Lightning\ApiBundle\Tests\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Lightning\ApiBundle\Service\History;
use Lightning\ApiBundle\Entity\Log;
use Lightning\ApiBundle\Tests\AbstractTest;

class HistoryTest extends AbstractTest
{
    protected $history;

    protected $item;

    public function setUp()
    {
        parent::setUp();

        $this->createAccount();

        $doctrine = static::$kernel->getContainer()->get('doctrine');

        $user = $this->getMock('stdClass', array('getUsername'));
        $user->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue(1));

        $token = $this->getMock('stdClass', array('getUser'));
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $security = $this->getMock('stdClass', array('getToken'));
        $security->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $calendar = $this->getMock('Lightning\ApiBundle\Service\Calendar');
        $calendar->expects($this->any())
            ->method('createDateTime')
            ->will($this->returnValue(new \DateTime('2012-02-29T12:00:00+0000')));

        $this->history = new History($doctrine, $security, $calendar);

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
