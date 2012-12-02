<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AccessTokenProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    private $encoderFactory;

    public function __construct(UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory)
    {
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {

            foreach ($user->getAccessTokens() as $accessToken) {

                $match = $accessToken->getId() == $token->getToken();
                $valid = $accessToken->getChallenge() === $token->getCredentials();
                $approved = $accessToken->getApproved();

                if ($match && $valid && $approved) {

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
