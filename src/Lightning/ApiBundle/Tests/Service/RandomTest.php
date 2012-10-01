<?php

namespace Lightning\ApiBundle\Tests\Service;

use JMS\DiExtraBundle\Annotation\Service;
use Lightning\ApiBundle\Service\Random;

class RandomTest extends \PHPUnit_Framework_TestCase
{
    private $random;

    protected function setUp()
    {
        $this->random = new Random(1);
    }

    public function testCode()
    {
        $code = $this->random->code(8);

        $this->assertEquals('b2jwzsiz', $code);
    }

    public function testChallenge()
    {
        $challenge = $this->random->challenge();

        $this->assertEquals(6261, $challenge);
    }

    public function testSecret()
    {
        $secret = $this->random->secret();

        $this->assertEquals('9e96f28fe1e7a613ddc978d1695cedae', $secret);
    }
}
