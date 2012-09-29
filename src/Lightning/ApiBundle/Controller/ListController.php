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

class ListController extends AbstractListController
{
    protected $router;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($doctrine, $security, $router)
    {
        parent::__construct($doctrine, $security);
        $this->router = $router;
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
