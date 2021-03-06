<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Lightning\ApiBundle\Entity\Account;

/**
 * Service for account access.
 *
 * @Service
 */
class AccountManager
{
    const ID_SUBSCRIPTION_ONE_YEAR = 'ch.lightningapp.oneyear';

    protected $random;

    protected $doctrine;

    protected $security;

    protected $factory;

    protected $calendar;

    protected $appStore;

    /**
     * @InjectParams({
     *     "random" = @Inject("lightning.api_bundle.service.random"),
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context"),
     *     "factory" = @Inject("security.encoder_factory"),
     *     "calendar" = @Inject("lightning.api_bundle.service.calendar"),
     *     "appStore" = @Inject("lightning.api_bundle.service.app_store")
     * })
     */
    public function __construct($random, $doctrine, $security, $factory, $calendar, $appStore)
    {
        $this->random = $random;
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->factory = $factory;
        $this->calendar = $calendar;
        $this->appStore = $appStore;
    }

    /**
     * Returns the account for the ID if authenticated user has access to it, throws exceptions otherwise.
     *
     * @param mixed $id Account ID
     *
     * @return Account
     * @throws NotFoundHttpException     If the account was not found
     * @throws AccessDeniedHttpException If the authenticated account doesn't match
     */
    public function checkAccount($id)
    {
        if ($this->security->getToken()->getUser()->getUsername() !== (int) $id) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account.');
        }

        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($id);

        return $account;
    }

    public function createAccount()
    {
        $account = new Account();
        $account->setCreated($this->calendar->createDateTime('now'));
        $account->setModified($this->calendar->createDateTime('now'));
        $account->setExpiry($this->calendar->createDateTime('2020-01-01 00:00:00'));

        // generate access code
        $account->setCode($this->random->code());

        // encode random salt and secret as password
        $account->setSalt($this->random->secret());

        $secret = $this->random->secret();
        $account->revealed = $secret;

        $encoder = $this->factory->getEncoder($account);
        $password = $encoder->encodePassword($secret, $account->getSalt());
        $account->setSecret($password);

        $em = $this->doctrine->getManager();
        $em->persist($account);
        $em->flush();

        return $account;
    }

    public function updateExpiry($id, $receipt)
    {
        $account = $this->checkAccount($id);

        list($id, $purchased) = $this->appStore->verify($receipt);

        if ($id == self::ID_SUBSCRIPTION_ONE_YEAR) {

            $account->setExpiry($purchased);
        }
    }
}
