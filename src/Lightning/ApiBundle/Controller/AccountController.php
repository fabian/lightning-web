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
class AccountController
{
    protected $manager;

    protected $airship;

    protected $router;

    /**
     * @InjectParams({
     *     "manager" = @Inject("lightning.api_bundle.service.account_manager"),
     *     "airship" = @Inject("lightning.api_bundle.service.urban_airship"),
     *     "router" = @Inject("router")
     * })
     */
    public function __construct($manager, $airship, $router)
    {
        $this->manager = $manager;
        $this->airship = $airship;
        $this->router = $router;
    }

    /**
     * @Route("/accounts.{_format}", defaults={"_format" = "json"})
     * @Method("POST")
     * @View(statusCode=201)
     */
    public function createAction(Request $request)
    {
        $account = $this->manager->createAccount();

        $this->addUrls($account);

        return $account;
    }

    /**
     * @Route("/accounts/{id}.{_format}", defaults={"_format" = "json"})
     * @Method("GET")
     * @View()
     */
    public function showAction($id)
    {
        $account = $this->manager->checkAccount($id);

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
        $account = $this->manager->checkAccount($id);

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
     * @Route("/accounts/{id}/receipt.{_format}", defaults={"_format" = "json"})
     * @Method("PUT")
     * @View(statusCode=204)
     */
    public function receiptAction($id, Request $request)
    {
        $this->manager->updateExpiry($id, $request->get('data'));
    }

    /**
     * @param Account $account
     */
    protected function addUrls($account)
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
        if ($account->revealed) {
            $account->account = $this->router->generate(
                'lightning_api_account_show',
                array(
                    'id' => $account->getId(),
                    'secret' => $account->revealed,
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
