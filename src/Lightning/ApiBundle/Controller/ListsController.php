<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;

class ListsController extends FOSRestController
{
    /**
     * @Route("/lists")
     * @View()
     */
    public function indexAction()
    {
        /*
        $list = new ItemList();
        $list->setTitle('Example');
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));
    
        $em = $this->getDoctrine()->getManager();
        $em->persist($list);
        $em->flush();
        */

        $lists = $product = $this->getDoctrine()
            ->getRepository('LightningApiBundle:ItemList')
            ->findAll();

        $data = array('lists' => $lists);

        return $data;
    }
}
