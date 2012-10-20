<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

use Lightning\ApiBundle\Entity\AccessToken;

/**
 * Service for access tokens.
 *
 * @Service
 */
class AccessTokenManager
{
    protected $accountManager;

    protected $doctrine;

    protected $random;

    /**
     * @InjectParams({
     *     "accountManager" = @Inject("lightning.api_bundle.service.account_manager"),
     *     "doctrine" = @Inject("doctrine"),
     *     "random" = @Inject("lightning.api_bundle.service.random")
     * })
     */
    public function __construct($accountManager, $doctrine, $random)
    {
        $this->accountManager = $accountManager;
        $this->doctrine = $doctrine;
        $this->random = $random;
    }

    public function createAccessToken($accountId, $code)
    {
        $challenge = $this->random->challenge();

        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->findOneBy(array('id' => $accountId, 'code' => $code));

        // note: for security reasons we keep secret if no account was found
        if ($account) {

            $token = new AccessToken($account);
            $token->setChallenge($challenge);
            $token->setCreated(new \DateTime('now'));

            $em = $this->doctrine->getManager();
            $em->persist($token);
            $em->flush();

            // TODO send push notification
        }

        return $challenge;
    }

    public function approveAccessToken($accountId, $tokenId, $challenge)
    {
        $this->accountManager->checkAccount($accountId);

        $token = $this->doctrine
            ->getRepository('LightningApiBundle:AccessToken')
            ->find($tokenId);

        if (!$token) {
            throw new NotFoundHttpException('No token found for id ' . $tokenId . '.');
        }

        // once again we keep secret if the challenge doesn't match
        if ($challenge == $token->getChallenge()) {

            $token->setApproved(true);

            $em = $this->doctrine->getManager();
            $em->flush();
        }
    }
}
