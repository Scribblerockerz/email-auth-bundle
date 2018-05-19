<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface, PreAuthenticationSuccessHandlerInterface
{
    protected $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    /**
     * This is called when an authentication attempt succeeds.
     * This is called by authentication listeners.
     * The holder of the account authorized this authentication.
     *
     * The identity of the user is confirmed.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return $this->httpUtils->createRedirectResponse($request, '/');
    }

    /**
     * This is called when an pre authentication attempt succeeds. This
     * is called by authentication listeners.
     * The identity of the  user is not confirmed/nor denied at this point.
     *
     * An authorization request was only send out to the real holder of the account.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @return Response|null
     */
    public function onPreAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return $this->httpUtils->createRedirectResponse($request, '/waiting');
    }
}