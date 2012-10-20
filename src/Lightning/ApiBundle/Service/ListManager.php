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
 * Service for list access.
 *
 * @Service
 */
class ListManager
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

    public function updateList($listId, $modified, $title)
    {
        $list = $this->checkList($listId, true);

        $modified = new \DateTime($modified);
        if ($modified < $list->getModified()) {
            throw new HttpException(409, 'Conflict, list has later modification.');
        }

        $list->setTitle($title);

        $em = $this->doctrine->getManager();
        $em->flush();

        return $list;
    }

    public function deleteList($listId)
    {
        $list = $this->checkList($listId, true);

        $em = $this->doctrine->getManager();
        $em->remove($list);
        $em->flush();
    }

    public function getLists($accountId)
    {
        $account = $this->accountManager->checkAccount($accountId);

        $lists = $account->getLists();

        return $lists;
    }

    /**
     * @param string|integer
     * @param boolean  $owner
     *
     * @return \Lightning\ApiBundle\Entity\ItemList
     */
    public function checkList($id, $owner = false)
    {
        $list = $this->doctrine
            ->getRepository('LightningApiBundle:ItemList')
            ->find($id);

        if (!$list) {
            throw new NotFoundHttpException('No list found for id ' . $id . '.');
        }

        $this->checkAccountList($list, $owner);

        return $list;
    }

    /**
     * @param ItemList $list
     * @param boolean  $owner
     *
     * @return AccountList
     */
    public function checkAccountList($list, $owner = false)
    {
        $account = $this->security->getToken()->getUser()->getUsername();
        $accountList = $this->doctrine
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('list' => $list->getId(), 'account' => $account, 'deleted' => false));

        if (!$accountList) {
            throw new AccessDeniedHttpException('Authenticated account ' . $account . ' has no access to list.');
        }

        if ($owner && $accountList->getPermission() != AccountList::PERMISSION_OWNER) {
            throw new AccessDeniedHttpException('Authenticated account ' . $account . ' is not owner of list.');
        }

        return $accountList;
    }
}
