<?php

namespace Rockz\EmailAuthBundle\Security\Firewall;

use Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception\SkipAuthenticationException;
use Rockz\EmailAuthBundle\Security\Authentication\Token\PendingAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Authentication\Token\TemporaryToken;
use Rockz\EmailAuthBundle\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\PreAuthenticationFailureHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\PreAuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class EmailAuthenticationListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $providerKey;
    protected $emailParameter;
    protected $preAuthenticationSuccessHandler;
    protected $preAuthenticationFailureHandler;
    protected $authenticationSuccessHandler;
    protected $authenticationFailureHandler;

    /** @var RememberMeServicesInterface */
    protected $rememberMeServices;

    /** @var CsrfTokenManagerInterface */
    protected $csrfTokenManager;

    protected $options = array();

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        string $providerKey,
        string $emailParameter,
        PreAuthenticationSuccessHandlerInterface $preAuthenticationSuccessHandler,
        PreAuthenticationFailureHandlerInterface $preAuthenticationFailureHandler,
        AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        AuthenticationFailureHandlerInterface $authenticationFailureHandler,
        array $options = array(),
        CsrfTokenManagerInterface $csrfTokenManager = null
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->emailParameter = $emailParameter;
        $this->preAuthenticationSuccessHandler = $preAuthenticationSuccessHandler;
        $this->preAuthenticationFailureHandler = $preAuthenticationFailureHandler;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
        $this->authenticationFailureHandler = $authenticationFailureHandler;
        $this->options = $options;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * Sets the RememberMeServices implementation to use.
     * @param RememberMeServicesInterface $rememberMeServices
     */
    public function setRememberMeServices(RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    public function handle(GetResponseEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        // Step 1. request authorization from user by mail
        if ($request->request->has($this->emailParameter)) {
            $email = $request->request->get($this->emailParameter);

            $response = null;

            // optional csrf protection
            if (null !== $this->csrfTokenManager) {
                $csrfToken = ParameterBagUtils::getRequestParameterValue($request, $this->options['csrf_parameter']);

                if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken($this->options['csrf_token_id'], $csrfToken))) {
                    throw new InvalidCsrfTokenException('Invalid CSRF token.');
                }
            }

            try {
                // requesting the authorization for that authentication
                $temporaryToken = $this->authenticationManager->authenticate(new TemporaryToken($email, $this->providerKey));

                $this->tokenStorage->setToken($temporaryToken);

                $response = $this->preAuthenticationSuccessHandler->onPreAuthenticationSuccess($request, $temporaryToken);

                if (null !== $this->rememberMeServices) {
                    $this->rememberMeServices->loginSuccess($request, $response, $temporaryToken);
                }
            } catch (AuthenticationException $e) {

                $this->tokenStorage->setToken(null);

                if (null !== $this->rememberMeServices) {
                    $this->rememberMeServices->loginFail($request, $e);
                }

                // remove token from session, or any other session based flags
                $response = $this->preAuthenticationFailureHandler->onPreAuthenticationFailure($request, $e);
            }

            $event->setResponse($response);

            return;
        }

        // Skip if no token available
        if (null === $currentToken = $this->tokenStorage->getToken()) {
            return;
        }

        // Current authentication is not our business
        if (!$currentToken instanceof PendingAuthenticationToken) {
            return;
        }

        // Step 2. check authorization status
        try {

            $authenticatedToken = $this->authenticationManager->authenticate($currentToken);

            $this->tokenStorage->setToken($authenticatedToken);

            $response = $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $authenticatedToken);

            if (null !== $this->rememberMeServices) {
                $this->rememberMeServices->loginSuccess($request, $response, $authenticatedToken);
            }

        } catch (SkipAuthenticationException $e) {
            // The authorization of this authentication is still pending
            return;

        } catch (AuthenticationException $e) {

            $this->tokenStorage->setToken(null);

            if (null !== $this->rememberMeServices) {
                $this->rememberMeServices->loginFail($request, $e);
            }

            // remove token from session, or any other session based flags
            $response = $this->authenticationFailureHandler->onAuthenticationFailure($request, $e);
        }

        $event->setResponse($response);

//      CHECKLIST:
//
//      - observe the request for a secret `email_auth` parameter
//      - if this is an email, retrieve the user by the configured user provider
//      - store clientToken to match it to the request
//      - request authorization from the user by an autorizationToken ...
//      - store the flag in session
//
//      - in the Check if authentication required step, by lookup in session.
//      - check database for authorization request by clientToken
//      - if yes, try to authenticate
//      - if pending, skip
//      - if no, add flash message for illegal authentication or throw exception

    }
}