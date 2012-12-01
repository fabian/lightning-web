<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lightning\ApiBundle\Security\AccountToken;

class AccountProvider implements AuthenticationProviderInterface
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

            if ($token instanceof AccessToken) {

                foreach ($user->getAccessTokens() as $accessToken) {

                    $match = $accessToken->getId() == $token->getToken();
                    $valid = $accessToken->getChallenge() === $token->getCredentials();
                    $approved = $accessToken->getApproved();

                    if ($match && $valid && $approved) {

                        $authenticatedToken = new AccountToken($user->getRoles());
                        $authenticatedToken->setUser($user);
                        
                        return $authenticatedToken;
                    }
                }
            }

            $valid = $this->encoderFactory->getEncoder($user)->isPasswordValid(
                $user->getPassword(),
                $token->getCredentials(),
                $user->getSalt()
            );

            if ($valid) {

                $authenticatedToken = new AccountToken($user->getRoles());
                $authenticatedToken->setUser($user);

                return $authenticatedToken;
            }
        }

        throw new AuthenticationException('The Account authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AccountToken || $token instanceof AccessToken;
    }
}
