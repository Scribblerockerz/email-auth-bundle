<?php

namespace Rockz\EmailAuthBundle\Security\Authentication\Provider;

use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizer;
use Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception\SkipAuthenticationException;
use Rockz\EmailAuthBundle\Security\Authentication\Token\EmailAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Authentication\Token\PendingAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Authentication\Token\TemporaryToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class EmailAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $providerKey;
    private $remoteAuthorizer;

    public function __construct(UserProviderInterface $userProvider, string $providerKey, RemoteAuthorizer $remoteAuthorizer)
    {
        $this->userProvider = $userProvider;
        $this->providerKey = $providerKey;
        $this->remoteAuthorizer = $remoteAuthorizer;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function authenticate(TokenInterface $token)
    {
        // Step 1.
        if ($token instanceof TemporaryToken) {
            $email = $token->getUsername();

            $user = $this->userProvider->loadUserByUsername($email);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationException('Found something, but not a real User.');
            }

            $clientHash = $this->remoteAuthorizer->requestAuthorization($user->getUsername());

            // Return a partially authenticated token
            return new PendingAuthenticationToken($user, $clientHash, $this->providerKey, []);
        }

        if ($token instanceof PendingAuthenticationToken) {

            $clientHash = $token->getCredentials();

            $status = $this->remoteAuthorizer->getAuthorizationStatusByClientHash($clientHash);

            // Authorization request is still pending
            if ($status === RemoteAuthorizer::STATUS_PENDING) {
                throw new SkipAuthenticationException('The authorization of this authentication is still pending');
            }

            // Authorization request was denied by user
            if ($status === RemoteAuthorizer::STATUS_REFUSED) {
                throw new AuthenticationException('The account holder refused this authentication');
            }

            // TODO: this is a hint for testing...
            if ($status !== RemoteAuthorizer::STATUS_AUTHORIZED) {
                throw new \LogicException('this should never be reached');
            }

            $email = $token->getUsername();

            // get the user from the user provider again, to get a fresh user
            $user = $this->userProvider->loadUserByUsername($email);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationException('Found something, but not a real User.');
            }

            return new EmailAuthenticationToken($user, $clientHash, $this->providerKey, $user->getRoles());
        }

        throw new \LogicException('this should never be reached');
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return (
            $token instanceof TemporaryToken || $token instanceof PendingAuthenticationToken
        ) && $this->providerKey === $token->getProviderKey();
    }
}