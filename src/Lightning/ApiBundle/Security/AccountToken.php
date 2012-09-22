<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class AccountToken extends AbstractToken
{
    protected $secret;

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
}