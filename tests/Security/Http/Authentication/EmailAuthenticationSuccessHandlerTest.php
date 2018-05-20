<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\Authentication\EmailAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationSuccessHandlerTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils);

        $this->assertInstanceOf(EmailAuthenticationSuccessHandler::class, $successHandler);
    }

    public function testOnAuthenticationSuccess()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->willReturnCallback(function ($firstArg, $redirectPath) {
                // test the arguments only
                return $redirectPath;
            });

        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onAuthenticationSuccess($request, $this->createMock(TokenInterface::class));

        $this->assertSame('/', $responsePath);
    }

    public function testOnPreAuthenticationSuccess()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->willReturnCallback(function ($firstArg, $redirectPath) {
                // test the arguments only
                return $redirectPath;
            });

        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onPreAuthenticationSuccess($request, $this->createMock(TokenInterface::class));

        $this->assertSame('/waiting', $responsePath);
    }
}