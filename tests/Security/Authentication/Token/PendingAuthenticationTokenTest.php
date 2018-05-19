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

    public function testGetProviderKey()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertSame('baz', $token->getProviderKey());
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

    public function testSerializeToken()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', ['A']);

        $unserializedData = unserialize($token->serialize());

        // first offset must be the credentials
        $this->assertEquals('bar', $unserializedData[0]);

        // second offset must be the providerKey
        $this->assertEquals('baz', $unserializedData[1]);

        // Note: other offsets are handled by AbstractToken
    }

    public function testUnserializeToken()
    {
        $token = new PendingAuthenticationToken('foo', 'bar', 'baz', ['A']);

        $serializedData = $token->serialize();

        $loadedToken = new PendingAuthenticationToken('a','b', 'c', []);
        $loadedToken->unserialize($serializedData);

        $this->assertEquals('bar', $loadedToken->getCredentials());
        $this->assertEquals('baz', $loadedToken->getProviderKey());
        $this->assertEquals('foo', $loadedToken->getUser());
    }
}