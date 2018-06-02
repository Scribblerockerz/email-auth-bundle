<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Mailer\AuthorizationMailer;
use Rockz\EmailAuthBundle\Mailer\TemplateAwareMailerInterface;
use Rockz\EmailAuthBundle\Mailer\TwigSwiftMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AuthorizationMailerTest
 *
 * TODO: configure AuthorizationMailer configuration on per firewall base somehow...
 *
 * @package Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory
 */
class AuthorizationMailerTest extends TestCase
{
    public function testInstantiation()
    {
        $mailer = $this->createMock(TemplateAwareMailerInterface::class);
        $router = $this->createMock(UrlGeneratorInterface::class);

        $authorizationMailer = new AuthorizationMailer($mailer, $router);

        $this->assertInstanceOf(AuthorizationMailer::class, $authorizationMailer);
    }

    public function getAuthorizationMailerConfiguration()
    {
        return array(

            // test default configuration
            array(
                array(),
                array(
                    'authorize_route' => 'rockz_email_auth_authorization_authorize',
                    'refuse_route' => 'rockz_email_auth_authorization_refuse',
                    'from_email' => 'changeme@example.com',
                    'template_email_authorize_login' => '@RockzEmailAuth/emails/authorization/login.html.twig',
                )
            ),

            // test custom authorization routes
            array(
                array(
                    'authorize_route' => 'yes',
                    'refuse_route' => 'no',
                    'from_email' => 'acme.corp@example.com',
                    'template_email_authorize_login' => 'foo.html.twig',
                ),
                array(
                    'authorize_route' => 'yes',
                    'refuse_route' => 'no',
                    'from_email' => 'acme.corp@example.com',
                    'template_email_authorize_login' => 'foo.html.twig',
                )
            ),
        );
    }

    /**
     * @dataProvider getAuthorizationMailerConfiguration
     */
    public function testSendAuthorizationRequestEmailMessage($config, $expectedValues)
    {
        $that = $this;

        $mailer = $this->createMock(TemplateAwareMailerInterface::class);
        $mailer
            ->expects($this->once())
            ->method('sendMessage')
            ->willReturnCallback(function($templateName, $context, $from, $to) use ($that, $expectedValues) {

                /** @var \Swift_Message $message */

                // Test the actual message
                $that->assertEquals($expectedValues['template_email_authorize_login'], $templateName);
                $that->assertEquals($expectedValues['from_email'], $from);
                $that->assertEquals('john@example.com', $to);
            });

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router
            ->expects($this->at(0))
            ->method('generate')
            ->willReturnCallback(function($routeName) use ($that, $expectedValues) {
                $that->assertSame(
                    $expectedValues['authorize_route'],
                    $routeName,
                    'Should generate a route to accept the authorization'
                );
            });

        $router
            ->expects($this->at(1))
            ->method('generate')
            ->willReturnCallback(function($routeName) use ($that, $expectedValues) {
                $that->assertSame(
                    $expectedValues['refuse_route'],
                    $routeName,
                    'Should generate a route to refuse the authorization'
                );
            });

        $authorizationMailer = new AuthorizationMailer($mailer, $router, $config);

        $authorizationMailer->sendAuthorizationRequestEmailMessage('john@example.com', 'foo');
    }
}