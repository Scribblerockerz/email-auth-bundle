<?php

namespace Rockz\EmailAuthBundle\Mailer;

/**
 * Interface TemplateAwareMailerInterface
 *
 * This interface is aware of template processing.
 *
 * @package Rockz\EmailAuthBundle\Mailer
 */
interface TemplateAwareMailerInterface
{
    /**
     * Render a template and send the message
     *
     * @param string $templateName
     * @param array $context
     * @param array $fromEmail
     * @param string $toEmail
     */
    public function sendMessage($templateName, $context, $fromEmail, $toEmail);
}