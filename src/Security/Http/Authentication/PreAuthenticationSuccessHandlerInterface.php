<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface PreAuthenticationSuccessHandlerInterface
{
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
    public function onPreAuthenticationSuccess(Request $request, TokenInterface $token);
}