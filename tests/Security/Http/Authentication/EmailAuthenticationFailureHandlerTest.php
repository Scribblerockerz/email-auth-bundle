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
        $successHandler = new EmailAuthenticationFailureHandler($httpUtils, '/');

        $this->assertInstanceOf(EmailAuthenticationFailureHandler::class, $successHandler);
    }

    public function getRedirectConfiguration()
    {
        return array(
            array('/'),
            array('/failed'),
            array('/not-working'),
        );
    }

    /**
     * @dataProvider getRedirectConfiguration
     */
    public function testOnAuthenticationFailure($configuredRedirectPath)
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->willReturnCallback(function ($firstArg, $redirectPath) {
                // test the arguments only
                return $redirectPath;
            });

        $failureHandler = new EmailAuthenticationFailureHandler($httpUtils, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $failureHandler->onAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame($configuredRedirectPath, $responsePath);
    }

    /**
     * @dataProvider getRedirectConfiguration
     */
    public function testOnPreAuthenticationFailure($configuredRedirectPath)
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->willReturnCallback(function ($firstArg, $redirectPath) {
                // test the arguments only
                return $redirectPath;
            });

        $failureHandler = new EmailAuthenticationFailureHandler($httpUtils, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $failureHandler->onPreAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame($configuredRedirectPath, $responsePath);
    }
}