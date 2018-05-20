<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Mailer\TwigSwiftMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigSwiftMailerTest extends TestCase
{
    public function testInstantiation()
    {
        $mailer = $this->createMock(\Swift_Mailer::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $twig = $this->createMock(\Twig_Environment::class);

        $twigSwiftMailer = new TwigSwiftMailer($mailer, $router, $twig);

        $this->assertInstanceOf(TwigSwiftMailer::class, $twigSwiftMailer);
    }

    public function testSendAuthorizationRequestEmailMessage()
    {
        $that = $this;

        $mailer = $this->createMock(\Swift_Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function($message) use ($that) {

                /** @var \Swift_Message $message */

                // Test the actual message
                // TODO: make from email configurable!
                $that->assertEquals('fox.guard@example.com', $message->getHeaders()->get('from')->getFieldBody());
                $that->assertEquals('john@example.com', $message->getHeaders()->get('to')->getFieldBody());

            });

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router
            ->expects($this->at(0))
            ->method('generate')
            ->willReturnCallback(function($routeName) use ($that) {
                $that->assertSame(
                    'rockz_email_auth_authorization_authorize',
                    $routeName,
                    'Should generate a route to accept the authorization'
                );
            });

        $router
            ->expects($this->at(1))
            ->method('generate')
            ->willReturnCallback(function($routeName) use ($that) {
                $that->assertSame(
                    'rockz_email_auth_authorization_refuse',
                    $routeName,
                    'Should generate a route to refuse the authorization'
                );
            });

        $twig = $this->createMock(\Twig_Environment::class);
        $twig
            ->method('load')
            ->willReturn($this->createMock(\Twig_Template::class));

        $twigSwiftMailer = new TwigSwiftMailer($mailer, $router, $twig);

        $twigSwiftMailer->sendAuthorizationRequestEmailMessage('john@example.com', 'foo');
    }
}