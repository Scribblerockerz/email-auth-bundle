<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\Authentication\EmailAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationFailureHandlerTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $successHandler = new EmailAuthenticationFailureHandler($httpUtils, $router, '/');

        $this->assertInstanceOf(EmailAuthenticationFailureHandler::class, $successHandler);
    }

    public function getRedirectConfiguration()
    {
        return array(
            array('/', '/', false),
            array('/failed', '/failed', false),
            array('/not-working', '/not-working', false),
            array('named_route', '***named_route***', true),
        );
    }

    /**
     * @dataProvider getRedirectConfiguration
     */
    public function testOnAuthenticationFailure($configuredRedirectPath, $expectedRedirectPath, $useRouteGenerator)
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

        if ($useRouteGenerator) {
            $router
                ->expects($this->once())
                ->method('generate')
                ->willReturnCallback(function ($routeName) {
                    // fake route generation...
                    return '***'. $routeName . '***';
                });
        }

        $failureHandler = new EmailAuthenticationFailureHandler($httpUtils, $router, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $failureHandler->onAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame($expectedRedirectPath, $responsePath);
    }

    /**
     * @dataProvider getRedirectConfiguration
     */
    public function testOnPreAuthenticationFailure($configuredRedirectPath, $expectedRedirectPath, $useRouteGenerator)
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

        if ($useRouteGenerator) {
            $router
                ->expects($this->once())
                ->method('generate')
                ->willReturnCallback(function ($routeName) {
                    // fake route generation...
                    return '***'. $routeName . '***';
                });
        }

        $failureHandler = new EmailAuthenticationFailureHandler($httpUtils, $router, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $failureHandler->onPreAuthenticationFailure($request, new AuthenticationException());

        $this->assertSame($expectedRedirectPath, $responsePath);
    }
}