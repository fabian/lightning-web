<?php

namespace Lightning\ApiBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service(public = false)
 */
class AccessTokenListener implements ListenerInterface
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

        $accessToken = $request->headers->get('accesstoken', false);

        $regex = '#http://.*/accounts/(.*)/access_tokens/(.*)\?challenge=(.*)#';
        if (preg_match($regex, $accessToken, $matches)) {

            $token = new AccessToken();
            $token->setUser($matches[1]);
            $token->setToken($matches[2]);
            $token->setChallenge($matches[3]);

            try {
                $returnValue = $this->authenticationManager->authenticate($token);

                if ($returnValue instanceof TokenInterface) {
                    return $this->securityContext->setToken($returnValue);
                }
            } catch (AuthenticationException $e) {
                // ignore
            }
        }
    }
}
