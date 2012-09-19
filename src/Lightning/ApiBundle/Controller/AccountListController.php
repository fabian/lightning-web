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
use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Service\CodeGenerator;

class AccountListController extends AbstractAccountController
{
    protected $router;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "router" = @Inject("router"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($doctrine, $router, $security)
    {
        parent::__construct($doctrine, $security);
        $this->router = $router;
    }

    /**
     * @Route("/accounts/{id}/lists.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View()
     */
    public function createAction($id, Request $request)
    {
        $account = $this->checkAccount($id);

        $list = new ItemList();
        $list->setTitle($request->request->get('title'));
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));

        $accountList = new AccountList($account, $list);
        $accountList->setPermission('owner');
        $accountList->setRead(new \DateTime('now'));
        $accountList->setPushed(new \DateTime('now'));
        $accountList->setCreated(new \DateTime('now'));
        $accountList->setModified(new \DateTime('now'));

        $em = $this->doctrine->getManager();
        $em->persist($list);
        $em->flush(); // make sure list has an ID
        $em->persist($accountList);
        $em->flush();

        $this->mergeList($accountList);

        return $accountList;
    }

    /**
     * @Route("/accounts/{id}/lists.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function indexAction($id)
    {
        $account = $this->checkAccount($id);

        $lists = $account->getLists();

        // merge list
        foreach ($lists as $list) {
            $this->mergeList($list);
        }

        return array('lists' => $lists);
    }

    protected function mergeList($accountList)
    {
        $list = $accountList->getList();
        $accountList->title = $list->getTitle();
        $accountList->url = $this->router->generate('lightning_api_list_show', array(
            'id' => $list->getId(),
        ), true);
    }
}
