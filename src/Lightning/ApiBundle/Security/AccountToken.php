<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class AccountToken extends AbstractToken
{
    protected $secret;
    protected $accessToken;
    protected $challenge;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        parent::setAuthenticated(count($roles) > 0);
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getCredentials()
    {
        return $this->secret;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;
    }

    public function getChallenge()
    {
        return $this->challenge;
    }
}
