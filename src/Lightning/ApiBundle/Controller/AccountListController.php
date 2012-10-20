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

    protected $router;

    /**
     * @InjectParams({
     *     "manager" = @Inject("lightning.api_bundle.service.list_manager"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($manager, $router)
    {
        $this->manager = $manager;
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
     * @param AccountList $accountList
     */
    protected function mergeList($accountList)
    {
        $list = $accountList->getList();
        $accountList->id = $list->getId();
        $accountList->title = $list->getTitle();
        $accountList->url = $this->router->generate(
            'lightning_api_list_show',
            array(
                'id' => $list->getId(),
            ),
            true
        );
    }
}
