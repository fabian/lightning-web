<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;
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

    protected $doctrine;

    protected $security;

    /**
     * @InjectParams({
     *     "accountManager" = @Inject("lightning.api_bundle.service.account_manager"),
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($accountManager, $doctrine, $security)
    {
        $this->accountManager = $accountManager;
        $this->doctrine = $doctrine;
        $this->security = $security;
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
        $list->setTitle($title);
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));

        $accountList = new AccountList($account, $list);
        $accountList->setPermission(AccountList::PERMISSION_OWNER);
        $accountList->setRead(new \DateTime('now'));
        $accountList->setPushed(new \DateTime('now'));
        $accountList->setCreated(new \DateTime('now'));
        $accountList->setModified(new \DateTime('now'));

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

    public function readList($accountId, $listId)
    {
        $accountList = $this->checkAccountList($accountId, $listId);

        $accountList->setRead(new \DateTime());

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
