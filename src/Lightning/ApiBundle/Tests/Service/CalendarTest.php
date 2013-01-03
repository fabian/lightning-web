<?php

namespace Lightning\ApiBundle\Tests\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use JMS\DiExtraBundle\Annotation\Service;
use Lightning\ApiBundle\Service\Calendar;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    private $calendar;

    protected function setUp()
    {
        $this->calendar = new Calendar();
    }

    public function testCreateDateTime()
    {
        $this->assertEquals('2012-02-29T12:00:00+00:00', $this->calendar->createDateTime('2012-02-29T12:00:00+0000')->format('c'));
    }
}
