<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

class AccountController extends FOSRestController
{
    /**
     * @Route("/a/{code}.{_format}", defaults={"_format" = "html"})
     * @View()
     */
    public function indexAction($code)
    {
        return array('name' => $code);
    }
}
