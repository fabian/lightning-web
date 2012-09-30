<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
class ItemController extends AbstractListController
{
    protected $doctrine;

    protected $router;

    protected $security;

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
     * @Route("/lists/{id}/items.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=201)
     */
    public function createAction($id, Request $request)
    {
        $list = $this->checkList($id);
        $accountList = $this->checkAccountList($list);

        $item = new Item($list);
        $item->setValue($request->request->get('value'));
        $item->setCreated(new \DateTime('now'));
        $item->setModified(new \DateTime('now'));

        $em = $this->doctrine->getManager();
        $em->persist($item);
        $em->flush();

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
        $item = $this->checkItem($id);
        $list = $item->getList();

        $accountList = $this->checkAccountList($list);

        $this->addUrl($item);

        return $item;
    }

    protected function checkItem($id)
    {
        $item = $this->doctrine
            ->getRepository('LightningApiBundle:Item')
            ->find($id);

        if (!$item) {
            throw new NotFoundHttpException('No item found for id ' . $id . '.');
        }

        return $item;
    }

    /**
     * @param Item $item
     */
    protected function addUrl($item)
    {
        $item->url = $this->router->generate('lightning_api_item_show', array('id' => $item->getId()), true);
    }
}
