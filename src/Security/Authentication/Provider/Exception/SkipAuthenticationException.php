<?php

namespace Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception;

/**
 * This exception is thrown by the Authentication Provider to break the authentication process.
 *
 * Use case: remote authorization of the authentication is still pending
 *
 * Class SkipAuthenticationException
 * @package Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception
 */
class SkipAuthenticationException extends \Exception
{
}