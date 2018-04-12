<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface PreAuthenticationFailureHandlerInterface
{
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
    public function onPreAuthenticationFailure(Request $request, AuthenticationException $exception);
}