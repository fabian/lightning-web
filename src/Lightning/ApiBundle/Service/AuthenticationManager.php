<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 *
 * @Service
 */
class AuthenticationManager
{
    protected $doctrine;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine")
     * })
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param string|integer $accountId
     * @param string|integer $tokenId
     * @param string $challenge
     *
     * @return AccessToken|null
     */
    public function getAccessToken($accountId, $tokenId, $challenge)
    {
        $token = $this->doctrine
            ->getRepository('LightningApiBundle:AccessToken')
            ->findOneBy(
                array(
                    'id' => $tokenId,
                    'account' => $accountId,
                    'challenge' => $challenge,
                    'approved' => true,
                )
            );

        return $token;
    }
}
