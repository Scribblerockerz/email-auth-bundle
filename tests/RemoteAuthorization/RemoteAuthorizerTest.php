<?php

namespace Tests\Rockz\EmailAuthBundle\RemoteAuthorization;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\Entity\AuthSession;
use Rockz\EmailAuthBundle\Mailer\TwigSwiftMailer;
use Rockz\EmailAuthBundle\RemoteAuthorization\Exception\SessionNotFoundException;
use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizer;
use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizerInterface;
use Rockz\EmailAuthBundle\Repository\AuthSessionRepository;

class RemoteAuthorizerTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $authSessionRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $twigSwiftMailer;

    /** @var RemoteAuthorizer */
    private $remoteAuthorizer;

    protected function setUp()
    {
        $this->authSessionRepository = $this->createMock(AuthSessionRepository::class);
        $this->twigSwiftMailer = $this->createMock(TwigSwiftMailer::class);

        $this->remoteAuthorizer = new RemoteAuthorizer($this->authSessionRepository, $this->twigSwiftMailer);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(RemoteAuthorizerInterface::class, $this->remoteAuthorizer);
    }

    public function testRequestAuthorization()
    {
        $that = $this;

        $this->authSessionRepository
            ->expects($this->once())
            ->method('save')
            ->will(
                $this->returnCallback(
                    function ($entity) use ($that) {

                        /** @var AuthSession $entity */

                        $that->assertInstanceOf(
                            AuthSession::class,
                            $entity,
                            'Must be called with an instance of AuthSession'
                        );

                        $that->assertSame(
                            64,
                            strlen($entity->getClientHash()),
                            'Generated clientHash must be 64 characters long'
                        );

                        $that->assertSame(
                            128,
                            strlen($entity->getAuthorizationHash()),
                            'Generated authorizationHash must be 128 characters long'
                        );

                        $that->assertSame(
                            RemoteAuthorizer::STATUS_PENDING,
                            $entity->getStatus(),
                            'Initial status must be pending'
                        );
                    }
                )
            );

        $this->twigSwiftMailer
            ->expects($this->once())
            ->method('sendAuthorizationRequestEmailMessage')
            ->will(
                $this->returnCallback(
                    function ($email, $authorizationHash) use ($that) {

                        $that->assertSame('john@example.com', $email);

                        $this->assertSame(
                            128,
                            strlen($authorizationHash),
                            'Generated authorizationHash must be 128 characters long'
                        );
                    }
                )
            );

        $clientHash = $this->remoteAuthorizer->requestAuthorization('john@example.com');
        $this->assertSame(64, strlen($clientHash));
    }

    public function testAuthorizeRequest()
    {
        $that = $this;

        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new AuthSession());

        $this->authSessionRepository
            ->expects($this->once())
            ->method('save')
            ->will(
                $this->returnCallback(
                    function ($entity) use ($that) {
                        /** @var AuthSession $entity */

                        $this->assertInstanceOf(
                            AuthSession::class,
                            $entity,
                            'Must be called with an instance of AuthSession'
                        );
                        $this->assertSame(
                            RemoteAuthorizer::STATUS_AUTHORIZED,
                            $entity->getStatus(),
                            'Status should be set to authorized'
                        );
                    }
                )
            );

        $this->remoteAuthorizer->authorizeRequest('123');
    }

    public function testAuthorizeRequestNotFoundByAuthorizationHash()
    {
        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(SessionNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the given session by '123'");

        $this->remoteAuthorizer->authorizeRequest('123');
    }

    public function testRefuseRequest()
    {
        $that = $this;

        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new AuthSession());

        $this->authSessionRepository
            ->expects($this->once())
            ->method('save')
            ->will(
                $this->returnCallback(
                    function ($entity) use ($that) {
                        /** @var AuthSession $entity */

                        $this->assertInstanceOf(
                            AuthSession::class,
                            $entity,
                            'Must be called with an instance of AuthSession'
                        );
                        $this->assertSame(
                            RemoteAuthorizer::STATUS_REFUSED,
                            $entity->getStatus(),
                            'Status should be set to refused'
                        );
                    }
                )
            );

        $this->remoteAuthorizer->refuseRequest('123');
    }

    public function testRefuseRequestNotFoundByAuthorizationHash()
    {
        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(SessionNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the given session by '123'");

        $this->remoteAuthorizer->refuseRequest('123');
    }

    public function testGetAuthorizationStatusByAuthorizationHash()
    {
        $authSession = new AuthSession();
        $authSession->setStatus('foo');

        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($authSession);

        $status = $this->remoteAuthorizer->getAuthorizationStatusByAuthorizationHash('123');
        $this->assertSame('foo', $status);
    }

    public function testGetAuthorizationStatusByAuthorizationHashNotFound()
    {
        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(SessionNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the given session by '123'");
        $this->remoteAuthorizer->getAuthorizationStatusByAuthorizationHash('123');
    }

    public function testGetAuthorizationStatusByClientHash()
    {
        $authSession = new AuthSession();
        $authSession->setStatus('foo');

        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($authSession);

        $status = $this->remoteAuthorizer->getAuthorizationStatusByClientHash('123');
        $this->assertSame('foo', $status);
    }

    public function testGetAuthorizationStatusByClientHashNotFound()
    {
        $this->authSessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(SessionNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the given session by clientHash '123'");
        $this->remoteAuthorizer->getAuthorizationStatusByClientHash('123');
    }
}