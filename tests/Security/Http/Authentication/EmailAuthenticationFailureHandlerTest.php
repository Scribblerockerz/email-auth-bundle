<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\Authentication\EmailAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationFailureHandlerTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $successHandler = new EmailAuthenticationFailureHandler($httpUtils);

        $this->assertInstanceOf(EmailAuthenticationFailureHandler::class, $successHandler);
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

        $successHandler = new EmailAuthenticationFailureHandler($httpUtils);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame('/#total_failure', $responsePath);
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

        $successHandler = new EmailAuthenticationFailureHandler($httpUtils);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onPreAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame('/access#partial_failure', $responsePath);
    }
}