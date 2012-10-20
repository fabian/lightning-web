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
    protected $doctrine;

    protected $security;

    protected $random;

    protected $factory;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context"),
     *     "random" = @Inject("lightning.api_bundle.service.random"),
     *     "factory" = @Inject("security.encoder_factory")
     * })
     */
    public function __construct($doctrine, $security, $random, $factory)
    {
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->random = $random;
        $this->factory = $factory;
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
        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($id);

        if (!$account) {
            throw new NotFoundHttpException('No account found for id ' . $id . '.');
        }

        if ($this->security->getToken()->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account.');
        }

        return $account;
    }

    public function createAccount()
    {
        $account = new Account();
        $account->setCreated(new \DateTime('now'));
        $account->setModified(new \DateTime('now'));

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
}
