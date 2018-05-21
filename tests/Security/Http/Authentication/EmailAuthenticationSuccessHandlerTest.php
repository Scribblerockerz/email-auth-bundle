<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Http\Authentication\EmailAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class EmailAuthenticationSuccessHandlerTest extends TestCase
{
    public function testInstantiation()
    {
        $httpUtils = $this->createMock(HttpUtils::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils, $router, '/');

        $this->assertInstanceOf(EmailAuthenticationSuccessHandler::class, $successHandler);
    }

    public function getRedirectConfiguration()
    {
        return array(
            array('/', '/', false),
            array('/waiting', '/waiting', false),
            array('/please-hold-the-lion', '/please-hold-the-lion', false),
            array('named_route', '***named_route***', true)
        );
    }

    /**
     * @dataProvider getRedirectConfiguration()
     */
    public function testOnAuthenticationSuccess($configuredRedirectPath, $expectedRedirectPath, $useRouteGenerator)
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

        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils, $router, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onAuthenticationSuccess($request, $this->createMock(TokenInterface::class));

        $this->assertSame($expectedRedirectPath, $responsePath);
    }

    /**
     * @dataProvider getRedirectConfiguration()
     */
    public function testOnPreAuthenticationSuccess($configuredRedirectPath, $expectedRedirectPath, $useRouteGenerator)
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

        $successHandler = new EmailAuthenticationSuccessHandler($httpUtils, $router, $configuredRedirectPath);
        $request = $this->createMock(Request::class);

        $responsePath = $successHandler->onPreAuthenticationSuccess($request, $this->createMock(TokenInterface::class));

        $this->assertSame($expectedRedirectPath, $responsePath);
    }
}