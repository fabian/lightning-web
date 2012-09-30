<?php

namespace Lightning\ApiBundle\Tests\Service;

use JMS\DiExtraBundle\Annotation\Service;
use Lightning\ApiBundle\Service\Random;

class RandomTest extends \PHPUnit_Framework_TestCase
{
    private $random;

    protected function setUp()
    {
        $this->random = new Random();
    }

    public function testCode()
    {
        $code = $this->random->code(8, 1);

        $this->assertEquals('9v7n3Uab', $code);
    }

    public function testChallenge()
    {
        $challenge = $this->random->challenge(1);

        $this->assertEquals(6261, $challenge);
    }
}
