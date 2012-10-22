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
    protected $accountListManager;

    protected $doctrine;

    protected $security;

    /**
     * @InjectParams({
     *     "accountListManager" = @Inject("lightning.api_bundle.service.account_list_manager"),
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($accountListManager, $doctrine, $security)
    {
        $this->accountListManager = $accountListManager;
        $this->doctrine = $doctrine;
        $this->security = $security;
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

    /**
     * @param string|integer $listId
     * @param boolean        $owner
     *
     * @return \Lightning\ApiBundle\Entity\ItemList
     */
    public function checkList($listId, $owner = false)
    {
        $accountId = $this->security->getToken()->getUser()->getUsername();

        $accountList = $this->accountListManager->checkAccountList($accountId, $listId);

        if ($owner && $accountList->getPermission() != AccountList::PERMISSION_OWNER) {
            throw new AccessDeniedHttpException('Authenticated account ' . $accountId . ' is not owner of list.');
        }

        return $accountList->getList();
    }
}
