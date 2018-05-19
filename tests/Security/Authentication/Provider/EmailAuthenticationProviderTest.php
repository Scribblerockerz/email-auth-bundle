<?php

namespace Tests\Rockz\EmailAuthBundle\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizer;
use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizerInterface;
use Rockz\EmailAuthBundle\Security\Authentication\Provider\EmailAuthenticationProvider;
use Rockz\EmailAuthBundle\Security\Authentication\Provider\Exception\SkipAuthenticationException;
use Rockz\EmailAuthBundle\Security\Authentication\Token\EmailAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Authentication\Token\PendingAuthenticationToken;
use Rockz\EmailAuthBundle\Security\Authentication\Token\TemporaryToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class EmailAuthenticationProviderTest extends TestCase
{
    private $userProvider;
    private $remoteAuthorizer;

    protected function setUp()
    {
        $this->userProvider = $this->createMock(UserProviderInterface::class);
        $this->remoteAuthorizer = $this->createMock(RemoteAuthorizerInterface::class);
    }

    protected function createProviderWithMocks($providerKey = 'foo')
    {
        return new EmailAuthenticationProvider($this->userProvider, $providerKey, $this->remoteAuthorizer);
    }

    public function testConstructor()
    {
        $provider = $this->createProviderWithMocks();

        $this->assertInstanceOf(EmailAuthenticationProvider::class, $provider);
    }

    /**
     * @dataProvider getSupportedTokensProvider
     */
    public function testSupportsTokens($supportedToken)
    {
        $provider = $this->createProviderWithMocks('bar');

        $this->assertTrue(
            $provider->supports($supportedToken),
            'The token ' . get_class($supportedToken) . ' must be supported'
        );
    }

    public function testUnsupportedTokens()
    {
        $provider = $this->createProviderWithMocks('bar');

        $this->assertFalse(
            $provider->supports($this->createMock(TokenInterface::class)),
            'The given token must be not supported'
        );
    }

    public function getSupportedTokensProvider()
    {
        return array(
            array(new TemporaryToken('foo', 'bar')),
            array(new PendingAuthenticationToken('foo', 'baz', 'bar', [])),
        );
    }

    public function testAuthenticateInvalidToken()
    {
        $provider = $this->createProviderWithMocks();

        $this->expectException(\LogicException::class);
        $provider->authenticate($this->createMock(TokenInterface::class));
    }

    public function testAuthenticateWithWrongUserProvided()
    {
        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn('wrong user object');

        $provider = $this->createProviderWithMocks('bar');

        $token = new TemporaryToken('foo', 'bar');

        $this->expectException(AuthenticationException::class);
        $provider->authenticate($token);
    }

    public function testAuthenticateTemporaryTokenSuccessfully()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUsername')->willReturn('foo');

        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $provider = $this->createProviderWithMocks('bar');

        $token = new TemporaryToken('foo', 'bar');

        $returnedToken = $provider->authenticate($token);

        $this->assertInstanceOf(PendingAuthenticationToken::class, $returnedToken);
    }

    public function testAuthenticatePendingAuthorizationStatus()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUsername')->willReturn('foo');

        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $this->remoteAuthorizer
            ->method('getAuthorizationStatusByClientHash')
            ->willReturn(RemoteAuthorizer::STATUS_PENDING);

        $provider = $this->createProviderWithMocks('bar');

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);

        $this->expectException(SkipAuthenticationException::class);
        $provider->authenticate($token);
    }

    public function testAuthenticateRefusedAuthorizationStatus()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUsername')->willReturn('foo');

        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $this->remoteAuthorizer
            ->method('getAuthorizationStatusByClientHash')
            ->willReturn(RemoteAuthorizer::STATUS_REFUSED);

        $provider = $this->createProviderWithMocks('bar');

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The account holder refused this authentication');
        $provider->authenticate($token);
    }

    public function testAuthenticateUnknownAuthorizationStatus()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUsername')->willReturn('foo');

        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $this->remoteAuthorizer
            ->method('getAuthorizationStatusByClientHash')
            ->willReturn('unknown authorization status');

        $provider = $this->createProviderWithMocks('bar');

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('this should never be reached');
        $provider->authenticate($token);
    }

    public function testAuthenticateAuthorizedAuthorizationStatusWithWrongUserProvided()
    {
        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn('wrong user object');

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $this->remoteAuthorizer
            ->method('getAuthorizationStatusByClientHash')
            ->willReturn(RemoteAuthorizer::STATUS_AUTHORIZED);

        $provider = $this->createProviderWithMocks('bar');

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Found something, but not a real User.');

        $provider->authenticate($token);
    }

    public function testAuthenticateAuthorizedAuthorizationStatus()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUsername')->willReturn('foo');
        $user->method('getRoles')->willReturn([]);

        $this->userProvider
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->remoteAuthorizer
            ->method('requestAuthorization')
            ->willReturn('abc');

        $this->remoteAuthorizer
            ->method('getAuthorizationStatusByClientHash')
            ->willReturn(RemoteAuthorizer::STATUS_AUTHORIZED);

        $provider = $this->createProviderWithMocks('bar');

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);

        $returnedToken = $provider->authenticate($token);

        $this->assertInstanceOf(EmailAuthenticationToken::class, $returnedToken);
    }
}