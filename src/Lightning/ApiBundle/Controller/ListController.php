<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\ItemList;

/**
 * Controller for lists.
 */
class ListController
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
     * @Route("/lists/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $list = $this->manager->checkList($id);

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
        $modified = $request->get('modified');
        $title = $request->get('title');

        $list = $this->manager->updateList($id, $modified, $title);

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
        $this->manager->deleteList($id);
    }

    /**
     * @param ItemList $list
     */
    protected function addUrl($list)
    {
        $list->url = $this->router->generate('lightning_api_list_show', array('id' => $list->getId()), true);
    }
}
