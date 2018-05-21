<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class EmailAuthenticationSuccessHandler
 *
 * TODO: split this class into pre- and authentication handler, since configuration is confusing
 *
 * @package Rockz\EmailAuthBundle\Security\Http\Authentication
 */
class EmailAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface, PreAuthenticationSuccessHandlerInterface
{
    protected $httpUtils;
    protected $redirectPath;

    public function __construct(HttpUtils $httpUtils, string $redirectPath)
    {
        $this->httpUtils = $httpUtils;
        $this->redirectPath = $redirectPath;
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
        return $this->httpUtils->createRedirectResponse($request, $this->redirectPath);
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
        return $this->httpUtils->createRedirectResponse($request, $this->redirectPath);
    }
}