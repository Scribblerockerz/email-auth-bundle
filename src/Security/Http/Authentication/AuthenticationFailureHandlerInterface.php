<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface AuthenticationFailureHandlerInterface
{
    /**
     * This is called when an authentication attempt fails.
     * This is called by authentication listeners.

     * The system or the holder of the account denied this authentication.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null The response to return or null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception);
}