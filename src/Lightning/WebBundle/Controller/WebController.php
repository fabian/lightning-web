<?php

namespace Lightning\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class WebController extends Controller
{
    /**
     * @Route("/{code}")
     * @Template()
     */
    public function indexAction($code)
    {
        return array('name' => $code);
    }
}
