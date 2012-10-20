<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Item;
use Lightning\ApiBundle\Entity\AccountList;

/**
 * Controller for items.
 */
class ItemController
{
    protected $manager;

    protected $router;

    /**
     * @InjectParams({
     *     "manager" = @Inject("lightning.api_bundle.service.item_manager"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($manager, $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @Route("/lists/{id}/items.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function indexAction($id, Request $request)
    {
        $items = $this->manager->getItems($id);

        foreach ($items as $item) {
            $this->addUrl($item);
        }

        return array('items' => $item);
    }

    /**
     * @Route("/lists/{id}/items.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=201)
     */
    public function createAction($id, Request $request)
    {
        $value = $request->request->get('value');

        $item = $this->manager->createItem($id, $value);

        $this->addUrl($item);

        return $item;
    }

    /**
     * @Route("/items/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $item = $this->manager->checkItem($id);

        $this->addUrl($item);

        return $item;
    }

    /**
     * @Route("/items/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View()
     */
    public function updateAction($id, Request $request)
    {
        $modified = $request->get('modified');
        $value = $request->get('value');
        $done = ($request->get('done') === '1');

        $item = $this->manager->updateItem($id, $modified, $value, $done);

        $this->addUrl($item);

        return $item;
    }

    /**
     * @Route("/items/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("DELETE")
     * @View(statusCode=204)
     */
    public function deleteAction($id, Request $request)
    {
        $this->manager->deleteItem($id);
    }

    /**
     * @param Item $item
     */
    protected function addUrl($item)
    {
        $item->url = $this->router->generate('lightning_api_item_show', array('id' => $item->getId()), true);
    }
}
