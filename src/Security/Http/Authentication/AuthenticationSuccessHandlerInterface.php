<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface AuthenticationSuccessHandlerInterface
{
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
    public function onAuthenticationSuccess(Request $request, TokenInterface $token);
}