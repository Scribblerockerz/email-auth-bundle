<?php

namespace Tests\Rockz\EmailAuthBundle\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception\SkipAuthenticationException;
use Rockz\EmailAuthBundle\Security\Authentication\Token\PendingAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Firewall\EmailAuthenticationListener;
use Rockz\EmailAuthBundle\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\PreAuthenticationFailureHandlerInterface;
use Rockz\EmailAuthBundle\Security\Http\Authentication\PreAuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class EmailAuthenticationListenerTest extends TestCase
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $preAuthenticationSuccessHandler;
    protected $preAuthenticationFailureHandler;
    protected $authenticationSuccessHandler;
    protected $authenticationFailureHandler;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->preAuthenticationSuccessHandler = $this->createMock(PreAuthenticationSuccessHandlerInterface::class);
        $this->preAuthenticationFailureHandler = $this->createMock(PreAuthenticationFailureHandlerInterface::class);
        $this->authenticationSuccessHandler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
        $this->authenticationFailureHandler = $this->createMock(AuthenticationFailureHandlerInterface::class);

        $this->preAuthenticationSuccessHandler
            ->method('onPreAuthenticationSuccess')
            ->willReturn(new Response('pre_auth_success'));

        $this->preAuthenticationFailureHandler
            ->method('onPreAuthenticationFailure')
            ->willReturn(new Response('pre_auth_failure'));

        $this->authenticationSuccessHandler
            ->method('onAuthenticationSuccess')
            ->willReturn(new Response('success'));

        $this->authenticationFailureHandler
            ->method('onAuthenticationFailure')
            ->willReturn(new Response('failure'));
    }

    protected function createListener($providerKey = 'foo', $configEmailParameter = 'email_auth')
    {
        return new EmailAuthenticationListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $providerKey,
            $configEmailParameter,
            $this->preAuthenticationSuccessHandler,
            $this->preAuthenticationFailureHandler,
            $this->authenticationSuccessHandler,
            $this->authenticationFailureHandler
        );
    }

    public function testSetRememberMeService()
    {
        $listener = $this->createListener('foo');
        $listener->setRememberMeServices($this->createMock(RememberMeServicesInterface::class));
    }

    public function getRequestAuthorizationFromUserData()
    {
        return array(
            array('email_auth'),
            array('foo'),
            array('bar123'),
        );
    }

    /**
     * @dataProvider getRequestAuthorizationFromUserData()
     */
    public function testRequestAuthorizationFromUserByMail($emailFormParameter)
    {
        $listener = $this->createListener('foo', $emailFormParameter);

        $rememberMeService = $this->createMock(RememberMeServicesInterface::class);
        $rememberMeService->expects($this->once())
            ->method('loginSuccess');

        // Test with remember me service
        $listener->setRememberMeServices($rememberMeService);

        // fake authentication manager
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($this->createMock(TokenInterface::class));

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        $request->request
            ->expects($this->atLeastOnce())
            ->method('has')
            ->with($emailFormParameter)
            ->willReturn(true);

        $request->request
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with($emailFormParameter)
            ->willReturn('john@example.com');

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        // actual test starts here
        $listener->handle($event);

        $this->assertSame('pre_auth_success', $event->getResponse()->getContent());
    }

    public function testRequestAuthorizationFromUserByMailWithAuthenticationException()
    {
        $listener = $this->createListener('foo');

        $rememberMeService = $this->createMock(RememberMeServicesInterface::class);
        $rememberMeService->expects($this->once())
            ->method('loginFail');

        // Test with remember me service
        $listener->setRememberMeServices($rememberMeService);

        // fake authentication manager
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException( new AuthenticationException('Something went wrong!')));

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        $request->request
            ->expects($this->atLeastOnce())
            ->method('has')
            ->with('email_auth')
            ->willReturn(true);

        $request->request
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('email_auth')
            ->willReturn('john@example.com');

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        // actual test starts here
        $listener->handle($event);

        $this->assertSame('pre_auth_failure', $event->getResponse()->getContent());
    }

    public function testSkipAuthenticationIfNoTokenAvailable()
    {
        $listener = $this->createListener('foo');

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        $request->request
            ->expects($this->atLeastOnce())
            ->method('has')
            ->with('email_auth')
            ->willReturn(false);

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->handle($event), 'Should not return anything');
    }

    public function testSkipAuthenticationIfNotPendingToken()
    {
        $listener = $this->createListener('foo');

        // fake token storage
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->assertSame(null, $listener->handle($event), 'Should not return anything');
    }

    public function testSuccessfullyFullAuthentication()
    {
        $listener = $this->createListener('foo');

        // fake remember me service
        $rememberMeService = $this->createMock(RememberMeServicesInterface::class);
        $rememberMeService
            ->expects($this->once())
            ->method('loginSuccess');

        // Test with remember me service
        $listener->setRememberMeServices($rememberMeService);

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        // fake authentication manager
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(new PendingAuthenticationToken('john@example.com', 'bar', 'foo', []));

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken');

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener->handle($event);

        $this->assertSame('success', $event->getResponse()->getContent());
    }

    public function testSkipFullAuthentication()
    {
        $listener = $this->createListener('foo');

        // fake remember me service
        $rememberMeService = $this->createMock(RememberMeServicesInterface::class);
        $rememberMeService
            ->expects($this->never())
            ->method('loginSuccess');

        // Test with remember me service
        $listener->setRememberMeServices($rememberMeService);

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        // fake authentication manager
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException(new SkipAuthenticationException()));

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(new PendingAuthenticationToken('john@example.com', 'bar', 'foo', []));

        $this->tokenStorage
            ->expects($this->never())
            ->method('setToken');

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener->handle($event);

        $this->assertSame(null, $event->getResponse());
    }

    public function testFailFullAuthentication()
    {
        $listener = $this->createListener('foo');

        // fake remember me service
        $rememberMeService = $this->createMock(RememberMeServicesInterface::class);
        $rememberMeService
            ->expects($this->once())
            ->method('loginFail');

        // Test with remember me service
        $listener->setRememberMeServices($rememberMeService);

        // fake request
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);

        // fake authentication manager
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException(new AuthenticationException()));

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(new PendingAuthenticationToken('john@example.com', 'bar', 'foo', []));

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with(null);

        // build fake event
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener->handle($event);

        $this->assertSame('failure', $event->getResponse()->getContent());
    }
}