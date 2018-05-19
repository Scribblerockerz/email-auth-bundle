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


    public function testGetProviderKey()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertSame('baz', $token->getProviderKey());
    }

    public function testGetCredentials()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', []);
        $this->assertSame('bar', $token->getCredentials());
    }

    public function testSerializeToken()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['A']);

        $unserializedData = unserialize($token->serialize());

        // first offset must be the credentials
        $this->assertEquals('bar', $unserializedData[0]);

        // second offset must be the providerKey
        $this->assertEquals('baz', $unserializedData[1]);

        // Note: other offsets are handled by AbstractToken
    }

    public function testUnserializeToken()
    {
        $token = new EmailAuthenticationToken('foo', 'bar', 'baz', ['A']);

        $serializedData = $token->serialize();

        $loadedToken = new EmailAuthenticationToken('a','b', 'c', []);
        $loadedToken->unserialize($serializedData);

        $this->assertEquals('bar', $loadedToken->getCredentials());
        $this->assertEquals('baz', $loadedToken->getProviderKey());
        $this->assertEquals('foo', $loadedToken->getUser());
    }
}