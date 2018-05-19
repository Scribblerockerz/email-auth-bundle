<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface, PreAuthenticationFailureHandlerInterface
{
    protected $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    /**
     * This is called when an authentication attempt fails.
     * This is called by authentication listeners.
     * The system or the holder of the account denied this authentication.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null The response to return or null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->httpUtils->createRedirectResponse($request, '/#total_failure');
    }

    /**
     * This is called when an pre authentication attempt fails.
     * This is called by authentication listeners.
     *
     * An authorization request was denied by the system
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null The response to return or null
     */
    public function onPreAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->httpUtils->createRedirectResponse($request, '/access#partial_failure');
    }
}