<?php

namespace Tests\Rockz\EmailAuthBundle\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Authentication\Token\EmailAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class EmailAuthenticationTokenTest extends TestCase
{
    public function testConstructor()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['A', 'B']);
        $this->assertTrue($token->isAuthenticated());

        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    public function testProviderKeyIsNotEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new EmailAuthenticationToken('foo', 'bar', '', []);
    }

    public function testGetUserData()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['A', 'B']);
        $this->assertSame('foo', $token->getUsername());
        $this->assertSame('foo', $token->getUser());
    }

    public function testGetRealRoles()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['A', 'B']);
        $this->assertEquals([new Role('A'), new Role('B')], $token->getRoles());

        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['C']);
        $this->assertEquals([new Role('C')], $token->getRoles());

        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertEmpty($token->getRoles());
    }
}