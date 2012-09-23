<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\Account;

class ListController
{
    protected $doctrine;

    protected $router;

    protected $security;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "router" = @Inject("router"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($doctrine, $router, $security)
    {
        $this->doctrine = $doctrine;
        $this->router = $router;
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

    /**
     * @Route("/lists/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $list = $this->checkList($id);

        $accountList = $this->checkAccountList($list);

        $this->addUrl($list);

        return $list;
    }

    /**
     * @Route("/lists/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View()
     */
    public function updateAction($id, Request $request)
    {
        $list = $this->checkList($id);
        $accountList = $this->checkAccountList($list, true);

        $modified = new \DateTime($request->get('modified'));
        if ($modified < $list->getModified()) {
            throw new HttpException(409, 'Conflict, list has later modification.');
        }

        $list->setTitle($request->get('title'));

        $em = $this->doctrine->getManager();
        $em->flush();

        $this->addUrl($list);

        return $list;
    }

    /**
     * @Route("/lists/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("DELETE")
     * @View(statusCode=204)
     */
    public function deleteAction($id, Request $request)
    {
        $list = $this->checkList($id);
        $accountList = $this->checkAccountList($list, true);

        $em = $this->doctrine->getManager();
        $em->remove($list);
        $em->flush();
    }

    protected function addUrl($list)
    {
        $list->url = $this->router->generate('lightning_api_list_show', array('id' => $list->getId()), true);
    }
}
