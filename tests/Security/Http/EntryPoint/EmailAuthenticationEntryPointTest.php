<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\EntryPoint\EmailAuthenticationEntryPoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationEntryPointTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $entryPoint = new EmailAuthenticationEntryPoint($httpUtils);

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

        $entryPoint = new EmailAuthenticationEntryPoint($httpUtils);
        $request = $this->createMock(Request::class);

        $responsePath = $entryPoint->start($request);

        $this->assertSame('/access', $responsePath);
    }
}