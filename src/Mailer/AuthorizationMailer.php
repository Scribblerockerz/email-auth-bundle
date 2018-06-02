<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rockz\EmailAuthBundle\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthorizationMailer
{
    protected $templateMailer;
    protected $router;
    protected $options;

    protected $defaultOptions = array(
        'authorize_route' => 'rockz_email_auth_authorization_authorize',
        'refuse_route' => 'rockz_email_auth_authorization_refuse',
        'from_email' => 'changeme@example.com',
        'template_email_authorize_login' => '@RockzEmailAuth/emails/authorization/login.html.twig'
    );

    /**
     * AuthorizationMailer constructor.
     * @param TemplateAwareMailerInterface $templateMailer
     * @param UrlGeneratorInterface $router
     * @param array $options
     */
    public function __construct(TemplateAwareMailerInterface $templateMailer, UrlGeneratorInterface $router, array $options = [])
    {
        $this->templateMailer = $templateMailer;
        $this->router = $router;
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param string $email
     * @param string $authorizationHash
     */
    public function sendAuthorizationRequestEmailMessage(string $email, string $authorizationHash)
    {
        $authorizeUrl = $this->router->generate($this->options['authorize_route'], array('authorizationHash' => $authorizationHash), UrlGeneratorInterface::ABSOLUTE_URL);
        $refuseUrl = $this->router->generate($this->options['refuse_route'], array('authorizationHash' => $authorizationHash), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'authorizeUrl' => $authorizeUrl,
            'refuseUrl' => $refuseUrl,
        );

        $this->templateMailer->sendMessage($this->options['template_email_authorize_login'], $context, $this->options['from_email'], $email);
    }
}