<?php

namespace Rockz\EmailAuthBundle\RemoteAuthorization;

use Rockz\EmailAuthBundle\Entity\AuthSession;
use Rockz\EmailAuthBundle\Mailer\TwigSwiftMailer;
use Rockz\EmailAuthBundle\RemoteAuthorization\Exception\SessionNotFoundException;
use Rockz\EmailAuthBundle\Repository\AuthSessionRepository;

class RemoteAuthorizer implements RemoteAuthorizerInterface
{
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_REFUSED = 'refused';
    const STATUS_PENDING = 'pending';

    /**
     * @var AuthSessionRepository
     */
    protected $authSessionRepository;

    /**
     * @var TwigSwiftMailer
     */
    protected $mailer;

    public function __construct(AuthSessionRepository $authSessionRepository, TwigSwiftMailer $mailer)
    {
        $this->authSessionRepository = $authSessionRepository;
        $this->mailer = $mailer;
    }

    /**
     * Request an authorization from the email provided
     * a request should be returned
     *
     * @param string $email
     * @return string
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function requestAuthorization(string $email): string
    {
        $clientHash =  hash('sha256', uniqid($email, true));
        $authenticationHash = hash('sha512', uniqid($email, true));

        $authSession = new AuthSession();
        $authSession
            ->setClientHash($clientHash)
            ->setAuthorizationHash($authenticationHash)
            ->setStatus(self::STATUS_PENDING)
        ;

        $this->authSessionRepository->save($authSession);

        $this->mailer->sendAuthorizationRequestEmailMessage($email, $authenticationHash);

        return $clientHash;
    }

    /**
     * Find an authorize a request by an authorization hash
     *
     * if a request was successfully authorized,
     * true is returned.
     *
     * @param string $authorizationHash
     * @return bool
     * @throws SessionNotFoundException
     */
    public function authorizeRequest(string $authorizationHash): bool
    {
        /** @var AuthSession $authSession */
        $authSession = $this->getSessionByAuthorizationHash($authorizationHash);

        $authSession->setStatus(self::STATUS_AUTHORIZED);
        $this->authSessionRepository->save($authSession);

        return true;
    }

    /**
     * Find an refuse the authorization request by authorization hash
     *
     * if the rejection was successful,
     * true is returned
     *
     * @param string $authorizationHash
     * @return bool
     * @throws SessionNotFoundException
     */
    public function refuseRequest(string $authorizationHash): bool
    {
        /** @var AuthSession $authSession */
        $authSession = $this->getSessionByAuthorizationHash($authorizationHash);

        $authSession->setStatus(self::STATUS_REFUSED);
        $this->authSessionRepository->save($authSession);

        return true;
    }

    /**
     * Find the authorization request and get it's state
     *
     * @param string $authorizationHash
     * @return string
     * @throws SessionNotFoundException
     */
    public function getAuthorizationStatusByAuthorizationHash(string $authorizationHash): string
    {
        /** @var AuthSession $authSession */
        $authSession = $this->getSessionByAuthorizationHash($authorizationHash);

        return $authSession->getStatus();
    }

    /**
     * Find the authorization request and check it's current state
     *
     * @param string $clientHash
     * @return string
     * @throws SessionNotFoundException
     */
    public function getAuthorizationStatusByClientHash(string $clientHash): string
    {
        /** @var AuthSession $authSession */
        $authSession = $this->getSessionByClientHash($clientHash);

        return $authSession->getStatus();
    }

    /**
     * @param string $authorizationHash
     * @return AuthSession
     * @throws SessionNotFoundException
     */
    protected function getSessionByAuthorizationHash(string $authorizationHash)
    {
        /** @var AuthSession $authSession */
        $authSession = $this->authSessionRepository->findOneBy(['authorizationHash' => $authorizationHash]);

        if (!$authSession instanceof AuthSession) {
            throw new SessionNotFoundException("Unable to find the given session by '{$authorizationHash}'");
        }

        return $authSession;
    }

    /**
     * @param string $tokenHash
     * @return AuthSession
     * @throws SessionNotFoundException
     */
    protected function getSessionByClientHash(string $tokenHash)
    {
        /** @var AuthSession $authSession */
        $authSession = $this->authSessionRepository->findOneBy(['clientHash' => $tokenHash]);

        if (!$authSession instanceof AuthSession) {
            throw new SessionNotFoundException("Unable to find the given session by clientHash '{$tokenHash}'");
        }

        return $authSession;
    }
}