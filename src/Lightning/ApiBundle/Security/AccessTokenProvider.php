<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service(public = false)
 */
class AccessTokenProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    private $manager;

    /**
     * @InjectParams({
     *     "userProvider" = @Inject(required = false),
     *     "manager" = @Inject("lightning.api_bundle.service.authentication_manager")
     * })
     */
    public function __construct(UserProviderInterface $userProvider, $manager)
    {
        $this->userProvider = $userProvider;
        $this->manager = $manager;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {

            $accessToken = $this->manager->getAccessToken(
                $user->getUsername(),
                $token->getToken(),
                $token->getCredentials()
            );

            if ($accessToken) {

                $authenticatedToken = new AccessToken($user->getRoles());
                $authenticatedToken->setUser($user);

                return $authenticatedToken;
            }
        }

        throw new AuthenticationException('The Access Token authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AccessToken;
    }
}
