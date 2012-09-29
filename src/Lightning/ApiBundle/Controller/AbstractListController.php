<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\AccountList;

/**
 * Base controller for list access.
 */
abstract class AbstractListController
{
    protected $doctrine;

    protected $security;

    public function __construct($doctrine, $security)
    {
        $this->doctrine = $doctrine;
        $this->security = $security;
    }

    protected function checkList($id)
    {
        $list = $this->doctrine
            ->getRepository('LightningApiBundle:ItemList')
            ->find($id);

        if (!$list) {
            throw new NotFoundHttpException('No list found for id ' . $id . '.');
        }

        return $list;
    }

    protected function checkAccountList($list, $owner = false)
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
