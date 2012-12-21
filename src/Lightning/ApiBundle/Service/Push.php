<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Lightning\ApiBundle\Entity\Log;

/**
 * @Service
 * @Tag("monolog.logger", attributes = {"channel" = "push"})
 */
class Push
{
    protected $accountListManager;

    protected $doctrine;

    protected $airship;

    protected $router;

    protected $logger;

    protected $calendar;

    /**
     * @InjectParams({
     *     "accountListManager" = @Inject("lightning.api_bundle.service.account_list_manager"),
     *     "doctrine" = @Inject("doctrine"),
     *     "airship" = @Inject("lightning.api_bundle.service.urban_airship"),
     *     "router" = @Inject("router"),
     *     "logger" = @Inject("logger"),
     *     "calendar" = @Inject("lightning.api_bundle.service.calendar")
     * })
     */
    public function __construct($accountListManager, $doctrine, $airship, $router, $logger, $calendar)
    {
        $this->accountListManager = $accountListManager;
        $this->doctrine = $doctrine;
        $this->airship = $airship;
        $this->router = $router;
        $this->logger = $logger;
        $this->calendar = $calendar;
    }

    public function send($accountId, $listId)
    {
        $accountList = $this->accountListManager->checkAccountList($accountId, $listId);

        $list = $accountList->getList();

        foreach ($list->getAccounts() as $accountList) {

            $id = $accountList->getAccount()->getId();

            if ($id != $accountId) {

                $notification = $this->getNotification($accountList);

                if ($notification) {

                    // unread count
                    $count = 0;
                    foreach ($accountList->getAccount()->getLists() as $list) {
                        if ($list->getList()->getModified() > $list->getRead()) {
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

                    $this->logger->debug('Pushing notification.', array('account' => $url));

                    $response = $this->airship->push(array($url), $count, $notification, $accountList->getList()->getId());

                    $this->logger->debug('Pushed notification.', array('status' => $response->getStatusCode(), 'reason' => $response->getReasonPhrase()));
                }

                $accountList->setPushed($this->calendar->createDateTime('now'));
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

        $notification = '';
        if (count($notifications)) {
            $notification = implode('. ', $notifications);
            $notification .= '.';
        }

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
