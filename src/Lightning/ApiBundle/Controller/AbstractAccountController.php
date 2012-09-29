<?php

namespace Lightning\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\View;

use Lightning\ApiBundle\Entity\Account;

/**
 * Base controller for account access.
 */
abstract class AbstractAccountController
{
    protected $doctrine;

    protected $security;

    public function __construct($doctrine, $security)
    {
        $this->doctrine = $doctrine;
        $this->security = $security;
    }

    /**
     * Returns the account for the ID if authenticated user has access to it, throws exceptions otherwise.
     *
     * @param mixed $id Account ID
     *
     * @return Account
     * @throws NotFoundHttpException If the account was not found
     * @throws AccessDeniedHttpException If the authenticated account doesn't match
     */
    protected function checkAccount($id)
    {
        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->find($id);

        if (!$account) {
            throw new NotFoundHttpException('No account found for id ' . $id . '.');
        }

        if ($this->security->getToken()->getUser()->getUsername() !== $account->getUsername()) {
            throw new AccessDeniedHttpException('Account ' . $id . ' doesn\'t match authenticated account.');
        }

        return $account;
    }
}
