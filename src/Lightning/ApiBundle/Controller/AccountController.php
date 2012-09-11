<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Service\CodeGenerator;

class AccountController
{
    protected $random;

    protected $doctrine;

    protected $router;

    protected $factory;

    /**
     * @DI\InjectParams({
     *     "random" = @DI\Inject("lightning.api.random"),
     *     "doctrine" = @DI\Inject("doctrine"),
     *     "router" = @DI\Inject("router"),
     *     "factory" = @DI\Inject("security.encoder_factory")
     * })
     */
    public function __construct($random, $doctrine, $router, $factory)
    {
        $this->random = $random;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->factory = $factory;
    }

    /**
     * @Route("/{id}/{code}.{_format}", requirements={"id" = "\d+"}, defaults={"_format" = "html"})
     * @Method("GET")
     * @View()
     */
    public function indexAction($id, $code)
    {
        return array('id' => $id, 'code' => $code);
    }

    /**
     * @Route("/{id}/{code}.{_format}", requirements={"id" = "\d+"}, defaults={"_format" = "json"})
     * @Method("POST")
     * @View()
     */
    public function accessAction($id, $code)
    {
        var_dump($code);
        return array();
    }

    /**
     * @Route("/accounts/{id}.{_format}", defaults={"_format" = "json"})
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

        if ($this->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account');
        }

        $this->addUrls($account);

        return $account;
    }

    /**
     * @Route("/accounts.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View()
     */
    public function createAction(Request $request)
    {
        $account = new Account();
        $account->setCreated(new \DateTime('now'));
        $account->setModified(new \DateTime('now'));

        // generate access code
        $account->setCode($this->random->code());

        // encode random salt and secret as password
        $account->setSalt($this->random->secret());
        $secret = $this->random->secret();
        $encoder = $this->factory->getEncoder($account);
        $password = $encoder->encodePassword($secret, $account->getSalt());
        $account->setSecret($password);

        $em = $this->doctrine->getManager();
        $em->persist($account);
        $em->flush();

        // make sure original password gets returned
        $this->addUrls($account, $secret);

        return $account;
    }

    protected function addUrls($account, $secret = null)
    {
        // full account url
        $account->url = $this->router->generate('lightning_api_account_show', array(
            'id' => $account->getId(),
        ), true);

        // short web url
        $account->short = $this->router->generate('lightning_api_account_index', array(
            'id' => $account->getId(),
            'code' => $account->getCode(),
        ), true);

        // account url with secret
        if ($secret) {
            $account->account = $this->router->generate('lightning_api_account_show', array(
                'id' => $account->getId(),
                'secret' => $secret,
            ), true);
        }
    }
}
