<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\ItemList;

class ListController extends Controller
{
    /**
     * @Route("/lists.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function indexAction()
    {
        $lists = $product = $this->getDoctrine()
            ->getRepository('LightningApiBundle:ItemList')
            ->findAll();

        foreach ($lists as $list) {
            $this->addUrl($list);
        }

        $data = array('lists' => $lists);

        return $data;
    }

    /**
     * @Route("/lists.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
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

    /**
     * @Route("/lists/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $list = $this->getDoctrine()
            ->getRepository('LightningApiBundle:ItemList')
            ->find($id);

        if (!$list) {
            throw new NotFoundHttpException('No list found for id ' . $id);
        }

        $this->addUrl($list);

        return $list;
    }

    protected function addUrl($list)
    {
        $list->url = $this->get('router')->generate('lightning_api_list_show', array('id' => $list->getId()), true);
    }
}
