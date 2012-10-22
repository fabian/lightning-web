<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Lightning\ApiBundle\Entity\Log;
use Lightning\ApiBundle\Entity\AccountList;

/**
 * @Service
 */
class History
{
    protected $doctrine;

    protected $security;

    protected $account;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($doctrine, $security)
    {
        $this->doctrine = $doctrine;
        $this->security = $security;

        $id = $this->security->getToken()->getUser()->getUsername();
        $this->account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($id);
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
        $log = new Log($this->account, $item);
        $log->setAction($action);
        $log->setOld($old);
        $log->setHappened(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($log);
    }
}
