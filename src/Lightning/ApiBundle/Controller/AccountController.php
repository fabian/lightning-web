<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Account;

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

    /**
     * @Route("/accounts/{id}.{_format}", defaults={"_format" = "json"})
     * @View()
     */
    public function showAction($id)
    {
        $account = $this->getDoctrine()
            ->getRepository('LightningApiBundle:Account')
            ->find($id);

        if (!$account) {
            throw new NotFoundHttpException('No account found for id ' . $id);
        }

        if ($this->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account');
        }

        $this->addUrls($account);

        return $account;
    }

    /**
     * @Route("/accounts.{_format}", requirements={"_method" = "POST"}, defaults={"_format" = "json"})
     * @View()
     */
    public function createAction(Request $request)
    {
        // 23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz

        $account = new Account();
        $account->setCode('c6eh83qb');
        $account->setCreated(new \DateTime('now'));
        $account->setModified(new \DateTime('now'));

        $random = md5(uniqid(null, true));

        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($account);
        $password = $encoder->encodePassword($random, $account->getSalt());
        $account->setSecret($password);

        $em = $this->getDoctrine()->getManager();
        $em->persist($account);
        $em->flush();

        // make sure original password gets returned
        $this->addUrls($account, $random);

        return $account;
    }

    protected function addUrls($account, $secret = null)
    {
        $router = $this->get('router');
        $account->url = $router->generate('lightning_api_account_show', array('id' => $account->getId()), true);
        $account->urlShort = $router->generate('lightning_api_account_index', array('code' => $account->getCode()), true);

        if ($secret) {
            $account->urlSecret = $router->generate('lightning_api_account_show', array('id' => $account->getId(), 'secret' => $secret), true);
        }
    }
}
