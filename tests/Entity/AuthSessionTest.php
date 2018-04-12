<?php

namespace Tests\Rockz\EmailAuthBundle\Entity;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Entity\AuthSession;

class AuthSessionTest extends TestCase
{
    public function testIsAbleToInstantiate()
    {
        $authSession = new AuthSession();

        $this->assertInstanceOf(AuthSession::class, $authSession, 'Should be an instance of AuthSession');
    }
}