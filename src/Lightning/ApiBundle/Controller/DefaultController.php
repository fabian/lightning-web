<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Lightning\ApiBundle\Entity\ItemList;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
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

        return array('name' => $name, 'lists' => $lists);
    }
}
