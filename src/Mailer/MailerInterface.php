<?php

namespace Rockz\EmailAuthBundle\Mailer;

interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param string $email
     * @param string $authorizationHash
     */
    public function sendAuthorizationRequestEmailMessage(string $email, string $authorizationHash);
}