<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class AccessToken extends AbstractToken
{
    protected $token;
    protected $challenge;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        parent::setAuthenticated(count($roles) > 0);
    }

    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;
    }

    public function getCredentials()
    {
        return $this->challenge;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}
