<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\AccountList;

/**
 * @Service
 */
class AccountListManager
{
    protected $accountManager;

    protected $random;

    protected $doctrine;

    protected $security;

    protected $calendar;

    /**
     * @InjectParams({
     *     "accountManager" = @Inject("lightning.api_bundle.service.account_manager"),
     *     "random" = @Inject("lightning.api_bundle.service.random"),
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context"),
     *     "calendar" = @Inject("lightning.api_bundle.service.calendar")
     * })
     */
    public function __construct($accountManager, $random, $doctrine, $security, $calendar)
    {
        $this->accountManager = $accountManager;
        $this->random = $random;
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->calendar = $calendar;
    }

    /**
     * Creates a list and the corresponding account list and returns
     * the account list.
     *
     * @param int $accountId
     * @param string $title
     *
     * @return \Lightning\ApiBundle\Entity\AccountList
     */
    public function createList($accountId, $title)
    {
        $account = $this->accountManager->checkAccount($accountId);

        $list = new ItemList();

        // generate invitation code
        $list->setInvitation($this->random->code());

        $list->setTitle($title);
        $list->setCreated($this->calendar->createDateTime('now'));
        $list->setModified($this->calendar->createDateTime('now'));

        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_OWNER);
        $accountList->setRead($this->calendar->createDateTime('now'));
        $accountList->setPushed($this->calendar->createDateTime('now'));
        $accountList->setCreated($this->calendar->createDateTime('now'));

        $em = $this->doctrine->getManager();
        $em->persist($list);
        $em->flush(); // make sure list has an ID
        $em->persist($accountList);
        $em->flush();

        return $accountList;
    }

    public function getLists($accountId)
    {
        $account = $this->accountManager->checkAccount($accountId);

        $lists = $account->getLists();

        return $lists;
    }

    public function joinList($accountId, $listId, $invitation)
    {
        $account = $this->accountManager->checkAccount($accountId);

        $accountList = $this->doctrine
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('account' => $accountId, 'list' => $listId, 'deleted' => false));

        if ($accountList) {
            // account already has access to list
            return;
        }

        $list = $this->doctrine
            ->getRepository('LightningApiBundle:ItemList')
            ->find($listId);

        if (!$list) {
            throw new NotFoundHttpException('List ' . $listId . ' not found.');
        }

        if ($list->getInvitation() !== $invitation) {
            throw new AccessDeniedHttpException('Invitation ' . $invitation . ' doesn\'t match invitation for list.');
        }

        // invitation matched, add guest permission
        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_GUEST);
        $accountList->setRead($this->calendar->createDateTime('now'));
        $accountList->setPushed($this->calendar->createDateTime('now'));
        $accountList->setCreated($this->calendar->createDateTime('now'));

        $em = $this->doctrine->getManager();
        $em->persist($accountList);
        $em->flush();
    }

    public function readList($accountId, $listId, $read)
    {
        $accountList = $this->checkAccountList($accountId, $listId);

        $read = new \DateTime($read);
        if ($read > $accountList->getRead()) {
            $accountList->setRead($read);
        }

        $em = $this->doctrine->getManager();
        $em->flush();
    }

    /**
     * @param string|integer $accountId
     * @param string|integer $listId
     *
     * @return AccountList
     */
    public function checkAccountList($accountId, $listId)
    {
        if ($this->security->getToken()->getUser()->getUsername() !== (int) $accountId) {
            throw new AccessDeniedHttpException('Account ' . $accountId . ' doesn\'t match authenticated account.');
        }

        $accountList = $this->doctrine
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('account' => $accountId, 'list' => $listId, 'deleted' => false));

        if (!$accountList) {
            throw new AccessDeniedHttpException('Authenticated account ' . $accountId . ' has no access to list.');
        }

        return $accountList;
    }
}
