<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;

class ListsController extends FOSRestController
{
    /**
     * @Route("/lists", requirements={"_method" = "GET"})
     * @View()
     */
    public function indexAction()
    {
        // 23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz

        $lists = $product = $this->getDoctrine()
            ->getRepository('LightningApiBundle:ItemList')
            ->findAll();

        $data = array('lists' => $lists);

        return $data;
    }

    /**
     * @Route("/lists", requirements={"_method" = "POST"})
     * @View()
     */
    public function createAction(Request $request)
    {
        $list = new ItemList();
        $list->setTitle($request->request->get('title'));
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));
    
        $em = $this->getDoctrine()->getManager();
        $em->persist($list);
        $em->flush();

        return $list;
    }
}
