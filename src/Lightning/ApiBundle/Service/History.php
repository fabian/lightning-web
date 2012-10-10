<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Lightning\ApiBundle\Entity\Log;

/**
 * @Service
 */
class History
{
    protected $em;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function added($item)
    {
        $this->store($item, Log::ACTION_ADDED);
    }

    public function modified($item, $old)
    {
        $this->store($item, Log::ACTION_MODIFIED, $old);
    }

    public function completed($item)
    {
        $this->store($item, Log::ACTION_COMPLETED);
    }

    public function deleted($item)
    {
        $this->store($item, Log::ACTION_DELETED);
    }

    protected function store($item, $action, $old = null)
    {
        $log = new Log($item);
        $log->setAction($action);
        $log->setOld($old);
        $log->setHappened(new \DateTime());

        $this->em->persist($log);
    }
}
