<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service(public = false)
 */
class AccountListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    /**
     * @InjectParams({
     *     "securityContext" = @Inject("security.context"),
     *     "authenticationManager" = @Inject("security.authentication.manager")
     * })
     */
    public function __construct (
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $account = $request->headers->get('account', false);

        if (null !== $token = $this->securityContext->getToken()) {
            if ($token instanceof AccessToken && $token->isAuthenticated()) {
                return;
            }
        }

        if (false === $account) {
            throw new HttpException(401, 'Account header not found.');
        }

        $regex = '#http://.*/accounts/(.*)\?secret=(.*)#';
        if (preg_match($regex, $account, $matches)) {

            $token = new AccountToken();
            $token->setUser($matches[1]);
            $token->setSecret($matches[2]);

            try {
                $returnValue = $this->authenticationManager->authenticate($token);

                if ($returnValue instanceof TokenInterface) {
                    return $this->securityContext->setToken($returnValue);
                }
            } catch (AuthenticationException $e) {
                // throw exception below
            }
        }

        throw new AccessDeniedHttpException('Account header authentication failed.');
    }
}
