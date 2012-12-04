<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    protected $router;

    protected $airship;

    /**
     * @InjectParams({
     *     "accountManager" = @Inject("lightning.api_bundle.service.account_manager"),
     *     "random" = @Inject("lightning.api_bundle.service.random"),
     *     "doctrine" = @Inject("doctrine"),
     *     "router" = @Inject("router"),
     *     "airship" = @Inject("lightning.api_bundle.service.urban_airship")
     * })
     */
    public function __construct($accountManager, $random, $doctrine, $router, $airship)
    {
        $this->accountManager = $accountManager;
        $this->random = $random;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->airship = $airship;
    }

    public function createAccessToken($accountId, $code)
    {
        $challenge = $this->random->challenge();

        $token = new AccessToken();
        $token->setChallenge($challenge);
        $token->setCreated(new \DateTime('now'));

        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->findOneBy(array('id' => $accountId, 'code' => $code));

        // note: for security reasons we keep secret if no account was found
        if ($account) {

            $token->setAccount($account);
        }

        $em = $this->doctrine->getManager();
        $em->persist($token);
        $em->flush();

        if ($account) {

            $url = $this->router->generate(
                'lightning_api_account_show',
                array(
                    'id' => $account->getId(),
                ),
                true
            );

            $this->airship->push(array($url), null, 'Please approve access token.', null, $token->getId());
        }

        return $token;
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
