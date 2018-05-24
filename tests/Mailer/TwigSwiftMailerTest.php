<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Mailer\TwigSwiftMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * TODO: This class needs an integration test with twig and swift mailer setup!
 */
class TwigSwiftMailerTest extends TestCase
{
    public function testInstantiation()
    {
        $mailer = $this->createMock(\Swift_Mailer::class);
        $twig = $this->createMock(\Twig_Environment::class);

        $twigSwiftMailer = new TwigSwiftMailer($mailer, $twig);

        $this->assertInstanceOf(TwigSwiftMailer::class, $twigSwiftMailer);
    }

    public function testSendMessageWithTemplate()
    {
        $this->markAsRisky();
    }
}