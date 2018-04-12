<?php

namespace Rockz\EmailAuthBundle\RemoteAuthorization;

interface RemoteAuthorizerInterface
{
    /**
     * Request an authorization from the email provided
     * a clientHash should be returned
     *
     * @param string $email
     * @return string
     */
    public function requestAuthorization(string $email): string;

    /**
     * Find an authorize a request by an authorization hash
     *
     * if a request was successfully authorized,
     * true is returned.
     *
     * @param string $authorizationHash
     * @return bool
     */
    public function authorizeRequest(string $authorizationHash): bool;

    /**
     * Find an refuse the authorization request by authorization hash
     *
     * if the rejection was successful,
     * true is returned
     *
     * @param string $authorizationHash
     * @return bool
     */
    public function refuseRequest(string $authorizationHash): bool;

    /**
     * Find the authorization request and get it's state
     *
     * @param string $authorizationHash
     * @return string
     */
    public function getAuthorizationStatusByAuthorizationHash(string $authorizationHash): string;

    /**
     * Find the authorization request and get it's state
     *
     * @param string $clientHash
     * @return string
     */
    public function getAuthorizationStatusByClientHash(string $clientHash): string;
}