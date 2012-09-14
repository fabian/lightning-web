<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Service\CodeGenerator;

class AccountListController
{
    protected $doctrine;

    protected $router;

    protected $security;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "router" = @Inject("router"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($doctrine, $router, $security)
    {
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->security = $security;
    }

    /**
     * @Route("/accounts/{id}/lists.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($id);

        if (!$account) {
            throw new NotFoundHttpException('No account found for id ' . $id);
        }

        if ($this->security->getToken()->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account');
        }

        $lists = $account->getLists();

        // make sure original password gets returned
        foreach ($lists as $list) {
            $this->addUrl($list);
        }

        return $lists;
    }

    protected function addUrl($list, $secret = null)
    {
        $list->url = $this->router->generate('lightning_api_list_show', array(
            'id' => $list->getId(),
        ), true);
    }
}
