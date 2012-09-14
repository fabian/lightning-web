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

use Lightning\ApiBundle\Entity\ItemList;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Entity\Account;

class ListController
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
     * @Route("/lists.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function indexAction()
    {
        $lists = $this->doctrine
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
        $owner = $request->request->get('owner', 0);
        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($owner);

        if (!$account) {
            throw new NotFoundHttpException('No account found for owner ' . $owner);
        }

        if ($this->security->getToken()->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Owner ' . $id . ' doesn\'t match authenticated account');
        }

        $list = new ItemList();
        $list->setTitle($request->request->get('title'));
        $list->setCreated(new \DateTime('now'));
        $list->setModified(new \DateTime('now'));

        $accountList = new AccountList();
        $accountList->setPermission('owner');
        $accountList->setRead(new \DateTime('now'));
        $accountList->setPushed(new \DateTime('now'));
        $accountList->setCreated(new \DateTime('now'));
        $accountList->setModified(new \DateTime('now'));
        $accountList->setList($list);
        $accountList->setAccount($account);

        $em = $this->doctrine->getManager();
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
        $list = $this->doctrine
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
        $list->url = $this->router->generate('lightning_api_list_show', array('id' => $list->getId()), true);
    }
}
