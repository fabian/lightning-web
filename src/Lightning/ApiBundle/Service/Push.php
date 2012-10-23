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
class Push
{
    protected $accountListManager;

    protected $doctrine;

    protected $router;

    protected $airship;

    /**
     * @InjectParams({
     *     "accountListManager" = @Inject("lightning.api_bundle.service.account_list_manager"),
     *     "doctrine" = @Inject("doctrine"),
     *     "airship" = @Inject("lightning.api_bundle.service.urban_airship"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($accountListManager, $doctrine, $router, $airship)
    {
        $this->accountListManager = $accountListManager;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->airship = $airship;
    }

    public function send($accountId, $listId)
    {
        $accountList = $this->accountListManager->checkAccountList($accountId, $listId);

        $list = $accountList->getList();

        foreach ($list->getAccounts() as $accountList) {

            $id = $accountList->getAccount()->getId();

            if ($id != $accountId) {

                $notification = $this->getNotification($accountList);

                // unread count
                $count = 0;
                foreach ($accountList->getAccount()->getLists() as $list) {
                    if ($list->getModified() > $accountList->getRead()) {
                        $count++;
                    }
                }

                $url = $this->router->generate(
                    'lightning_api_account_show',
                    array(
                        'id' => $id,
                    ),
                    true
                );

                $this->airship->push(array($url), $count, $notification, $accountList->getList()->getId());

                $accountList->setPushed(new \DateTime());
            }
        }

        $em = $this->doctrine->getManager();
        $em->flush();
    }

    public function getNotification($accountList)
    {
        $query = $this->doctrine
            ->getRepository('LightningApiBundle:Log')
            ->createQueryBuilder('l')
            ->innerJoin('l.item', 'i')
            ->andWhere('l.account != :account')->setParameter('account', $accountList->getAccount())
            ->andWhere('i.list = :list')->setParameter('list', $accountList->getList())
            ->orderBy('l.happened', 'ASC');

        $since = $accountList->getPushed();
        if ($since) {
            $query->andWhere('l.happened > :since')->setParameter('since', $since);
        }

        $logs = $query->getQuery()->getResult();

        $items = array();
        foreach ($logs as $log) {

            $id = $log->getItem()->getId();

            // skip modified if recently added
            if (isset($items[$id])) {

                $added = $items[$id]->getAction() == Log::ACTION_ADDED;
                $modified = $log->getAction() == Log::ACTION_MODIFIED;

                if ($added && $modified) {
                    continue;
                }
            }

            $items[$id] = $log;
        }

        $added = array();
        $modified = array();
        $completed = array();
        foreach ($items as $log) {

            switch ($log->getAction()) {
                case Log::ACTION_ADDED:
                    $added[] = $log->getItem()->getValue();
                    break;
                case Log::ACTION_MODIFIED:
                    $modified[] = $log->getOld() . ' to ' . $log->getItem()->getValue();
                    break;
                case Log::ACTION_COMPLETED:
                    $completed[] = $log->getItem()->getValue();
                    break;
                case Log::ACTION_DELETED:
                    // ignore deleted
                    break;
            }
        }

        $notifications = array();
        if (count($added) > 0) {
            $notifications[] = 'Added ' . $this->arrayToText($added);
        }
        if (count($modified) > 0) {
            $notifications[] = 'Changed ' . $this->arrayToText($modified);
        }
        if (count($completed) > 0) {
            $notifications[] = 'Completed ' . $this->arrayToText($completed);
        }

        $notification = implode('. ', $notifications);
        $notification .= '.';

        return $notification;
    }

    public function arrayToText($array)
    {
        if (count($array) == 0) {
            return '';
        }

        if (count($array) == 1) {
            return reset($array);
        }

        $text = '';
        $text .= implode(', ', array_slice($array, 0, count($array) - 1));
        $text .= ' and ';
        $text .= $array[count($array) - 1];

        return $text;
    }
}
