<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service
 */
class Calendar
{
    /**
     * @return DateTime
     */
    public function createDateTime($time = 'now')
    {
        return new \DateTime($time);
    }
}
