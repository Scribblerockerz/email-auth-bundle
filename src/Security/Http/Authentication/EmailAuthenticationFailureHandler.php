<?php

namespace Rockz\EmailAuthBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface, PreAuthenticationFailureHandlerInterface
{
    protected $httpUtils;
    protected $redirectPath;
    protected $router;

    public function __construct(HttpUtils $httpUtils, UrlGeneratorInterface $router, string $redirectPath)
    {
        $this->httpUtils = $httpUtils;
        $this->redirectPath = $redirectPath;
        $this->router = $router;
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
        $path = $this->redirectPath;

        if (substr($path, 0, 4) != 'http' && substr($path, 0, 1) != '/') {
            $path = $this->router->generate($path);
        }

        return $this->httpUtils->createRedirectResponse($request, $path);
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
        $path = $this->redirectPath;

        if (substr($path, 0, 4) != 'http' && substr($path, 0, 1) != '/') {
            $path = $this->router->generate($path);
        }

        return $this->httpUtils->createRedirectResponse($request, $path);
    }
}