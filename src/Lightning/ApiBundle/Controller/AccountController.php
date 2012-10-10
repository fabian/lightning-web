<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Account;

/**
 * Controller for accounts.
 */
class AccountController extends AbstractAccountController
{
    protected $random;

    protected $airship;

    protected $router;

    protected $factory;

    /**
     * @InjectParams({
     *     "random" = @Inject("lightning.api_bundle.service.random"),
     *     "airship" = @Inject("lightning.api_bundle.service.urban_airship"),
     *     "doctrine" = @Inject("doctrine"),
     *     "history" = @Inject("lightning.api_bundle.service.history"),
     *     "router" = @Inject("router"),
     *     "security" = @Inject("security.context"),
     *     "factory" = @Inject("security.encoder_factory")
     * })
     */
    public function __construct($random, $airship, $doctrine, $history, $router, $security, $factory)
    {
        parent::__construct($doctrine, $history, $security);
        $this->random = $random;
        $this->airship = $airship;
        $this->router = $router;
        $this->factory = $factory;
    }

    /**
     * @Route("/accounts.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=201)
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

    /**
     * @Route("/accounts/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $account = $this->checkAccount($id);

        $this->addUrls($account);

        return $account;
    }

    /**
     * @Route("/accounts/{id}/device_tokens/{token}.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View(statusCode=204)
     */
    public function deviceTokenAction($id, $token)
    {
        $account = $this->checkAccount($id);

        $url = $this->router->generate(
            'lightning_api_account_show',
            array(
                'id' => $account->getId(),
            ),
            true
        );
        $this->airship->register($token, $url);
    }

    /**
     * @param Account     $account
     * @param string|null $secret
     */
    protected function addUrls($account, $secret = null)
    {
        // full account url
        $account->url = $this->router->generate(
            'lightning_api_account_show',
            array(
                'id' => $account->getId(),
            ),
            true
        );

        // short web url
        $account->shortUrl = $this->router->generate(
            'lightning_api_accesstoken_access',
            array(
                'id' => $account->getId(),
                'code' => $account->getCode(),
            ),
            true
        );

        // account url with secret
        if ($secret) {
            $account->account = $this->router->generate(
                'lightning_api_account_show',
                array(
                    'id' => $account->getId(),
                    'secret' => $secret,
                ),
                true
            );
        }

        // lists url
        $account->listsUrl = $this->router->generate(
            'lightning_api_accountlist_index',
            array(
                'id' => $account->getId(),
            ),
            true
        );
    }
}
