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

    private $calendar;

    /**
     * @InjectParams({
     *     "userProvider" = @Inject(required = false),
     *     "manager" = @Inject("lightning.api_bundle.service.authentication_manager"),
     *     "calendar" = @Inject("lightning.api_bundle.service.calendar")
     * })
     */
    public function __construct(UserProviderInterface $userProvider, $manager, $calendar)
    {
        $this->userProvider = $userProvider;
        $this->manager = $manager;
        $this->calendar = $calendar;
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

                $expired = $accessToken->getCreated() < $this->calendar->createDateTime('-1 week');

                if (!$expired) {

                    $authenticatedToken = new AccessToken($user->getRoles());
                    $authenticatedToken->setUser($user);

                    return $authenticatedToken;
                }
            }
        }

        throw new AuthenticationException('The Access Token authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AccessToken;
    }
}
