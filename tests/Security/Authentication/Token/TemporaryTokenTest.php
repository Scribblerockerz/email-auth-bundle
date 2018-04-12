<?php

namespace Tests\Rockz\EmailAuthBundle\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Security\Authentication\Token\TemporaryToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TemporaryTokenTest extends TestCase
{
    public function testConstructor()
    {
        $token = new TemporaryToken('john@example.com', 'provider_key');
        $this->assertFalse($token->isAuthenticated());

        $token = new TemporaryToken('john@example.com', 'provider_key');
        $this->assertEmpty($token->getRoles());

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->expectException(\InvalidArgumentException::class);
        new TemporaryToken('', '');
    }

    public function testGetProviderKey()
    {
        $token = new TemporaryToken('john@example.com', 'provider_key');

        $this->assertSame('provider_key', $token->getProviderKey());
    }

    public function testGetCredentials()
    {
        $token = new TemporaryToken('john@example.com', 'provider_key');

        $this->assertSame('', $token->getCredentials());
    }

    public function testGetUserData()
    {
        $token = new TemporaryToken('john@example.com', 'provider_key');
        $this->assertSame('john@example.com', $token->getUsername());

        $token = new TemporaryToken('john@example.com', 'provider_key');
        $this->assertSame('john@example.com', $token->getUser());
    }
}