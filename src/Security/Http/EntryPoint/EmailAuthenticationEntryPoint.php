<?php

namespace Rockz\EmailAuthBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    protected $httpUtils;
    protected $router;
    protected $redirectPath;

    public function __construct(HttpUtils $httpUtils, UrlGeneratorInterface $router, string $redirectPath)
    {
        $this->httpUtils = $httpUtils;
        $this->router = $router;
        $this->redirectPath = $redirectPath;
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *  A) For a form login, you might redirect to the login page
     *      return new RedirectResponse('/login');
     *  B) For an API token authentication system, you return a 401 response
     *      return new Response('Auth header required', 401);
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $path = $this->redirectPath;

        if (substr($path, 0, 4) != 'http' && substr($path, 0, 1) != '/') {
            $path = $this->router->generate($path);
        }

        return $this->httpUtils->createRedirectResponse($request, $path);
    }
}