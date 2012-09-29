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
use Lightning\ApiBundle\Entity\AccessToken;

/**
 * Controller for access tokens.
 */
class AccessTokenController extends AbstractAccountController
{
    protected $router;

    /**
     * @InjectParams({
     *     "doctrine" = @Inject("doctrine"),
     *     "router" = @Inject("router"),
     *     "security" = @Inject("security.context"),
     * })
     */
    public function __construct($doctrine, $router, $security)
    {
        parent::__construct($doctrine, $security);
        $this->router = $router;
    }

    /**
     * @Route("/{id}/{code}.{_format}", requirements={"id" = "\d+"}, defaults={"_format" = "html"})
     * @Method("GET")
     * @View()
     */
    public function accessAction($id, $code)
    {
        return array('id' => $id, 'code' => $code);
    }

    /**
     * @Route("/{id}/{code}.{_format}", requirements={"id" = "\d+"}, defaults={"_format" = "json"})
     * @Method("POST")
     * @View()
     */
    public function accessTokenAction($id, $code)
    {
        $challenge = mt_rand(1111, 9999);

        $account = $this->doctrine
            ->getRepository('LightningApiBundle:Account')
            ->findOneBy(array('id' => $id, 'code' => $code));

        // note: for security reasons we keep secret if no account was found
        if ($account) {

            $token = new AccessToken($account);
            $token->setChallenge($challenge);
            $token->setCreated(new \DateTime('now'));

            $em = $this->doctrine->getManager();
            $em->persist($token);
            $em->flush();

            // TODO send push notification
        }

        return array('challenge' => $challenge);
    }

    /**
     * @Route("/accounts/{accountId}/access_tokens/{tokenId}.{_format}", 
     *     requirements={"accountId" = "\d+", "id" = "\d+"}, defaults={"_format" = "json"})
     * @Method("PUT")
     * @View(statusCode=204)
     */
    public function approveAction($accountId, $tokenId, Request $request)
    {
        $account = $this->checkAccount($accountId);

        $token = $this->doctrine
            ->getRepository('LightningApiBundle:AccessToken')
            ->find($tokenId);

        if (!$token) {
            throw new NotFoundHttpException('No token found for id ' . $tokenId . '.');
        }

        // once again we keep secret if the challenge doesn't match
        if ($request->get('challenge') == $token->getChallenge()) {

            $token->setApproved(true);

            $em = $this->doctrine->getManager();
            $em->flush();
        }
    }
}
