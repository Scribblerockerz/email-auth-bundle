<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\EntryPoint\EmailAuthenticationEntryPoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationEntryPointTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $entryPoint = new EmailAuthenticationEntryPoint($httpUtils, $router, '/');

        $this->assertInstanceOf(EmailAuthenticationEntryPoint::class, $entryPoint);
    }

    public function testStart()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->willReturnCallback(function ($firstArg, $redirectPath) {
                // test the arguments only
                return $redirectPath;
            });

        $router = $this->createMock(UrlGeneratorInterface::class);

        $entryPoint = new EmailAuthenticationEntryPoint($httpUtils, $router, '/foo');
        $request = $this->createMock(Request::class);

        $responsePath = $entryPoint->start($request);

        $this->assertSame('/foo', $responsePath);
    }
}