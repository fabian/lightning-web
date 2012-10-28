<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\Account;

/**
 * Controller for lists linked to an account.
 */
class AccountListController
{
    protected $manager;

    protected $push;

    protected $router;

    /**
     * @InjectParams({
     *     "manager" = @Inject("lightning.api_bundle.service.account_list_manager"),
     *     "push" = @Inject("lightning.api_bundle.service.push"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($manager, $push, $router)
    {
        $this->manager = $manager;
        $this->push = $push;
        $this->router = $router;
    }

    /**
     * @Route("/accounts/{id}/lists.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=201)
     */
    public function createAction($id, Request $request)
    {
        $title = $request->request->get('title');

        $accountList = $this->manager->createList($id, $title);

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
        $lists = $this->manager->getLists($id);

        // merge list
        foreach ($lists as $list) {
            $this->mergeList($list);
        }

        return array('lists' => $lists);
    }

    /**
     * @Route("/accounts/{account}/lists/{list}.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View(statusCode=204)
     */
    public function joinAction($account, $list, Request $request)
    {
        $invitation = $request->request->get('invitation');

        $this->manager->joinList($account, $list, $invitation);
    }

    /**
     * @Route("/accounts/{account}/lists/{list}/push.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=204)
     */
    public function pushAction($account, $list)
    {
        $this->push->send($account, $list);
    }

    /**
     * @Route("/accounts/{account}/lists/{list}/read.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View(statusCode=204)
     */
    public function readAction($account, $list, Request $request)
    {
        $read = $request->request->get('read');
        $this->manager->readList($account, $list, $read);
    }

    /**
     * @param AccountList $accountList
     */
    protected function mergeList($accountList)
    {
        $list = $accountList->getList();
        $accountList->id = $list->getId();
        $accountList->title = $list->getTitle();
        $accountList->invitation = $list->getInvitation();
        $accountList->url = $this->router->generate(
            'lightning_api_list_show',
            array(
                'id' => $list->getId(),
            ),
            true
        );
    }
}
