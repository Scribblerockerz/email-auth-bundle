<?php

namespace Tests\Rockz\EmailAuthBundle\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Authentication\Token\PendingAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class PendingAuthenticationTokenTest extends TestCase
{
    public function testConstructor()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertTrue($token->isAuthenticated());

        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    public function testShouldNeverReturnAnyRoles()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertEmpty($token->getRoles());

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', [new Role('ROLE_ADMIN')]);
        $this->assertEmpty($token->getRoles());

        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', [new Role('ROLE_ADMIN'), new Role('ROLE_USER')]);
        $this->assertEmpty($token->getRoles());
    }

    public function testProviderKeyIsNotEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new PendingAuthenticationToken('', '', '', []);
    }

    public function testGetUserData()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertSame('foo', $token->getUsername());
        $this->assertSame('foo', $token->getUser());
    }

    public function testGetCredentials()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertSame('bar', $token->getCredentials());
    }
}