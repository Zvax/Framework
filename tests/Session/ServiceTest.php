<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Result;
use Zvax\Framework\Session\Entity as SessionEntity;
use Zvax\Framework\Session\Service;
use Zvax\Framework\Session\SessionStorageInterface as SessionStorage;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\UserStorageInterface as UserStorage;

#[CoversClass(Service::class)]
class ServiceTest extends TestCase
{
    protected SessionStorage&MockObject $sessionStorage;
    protected UserStorage&MockObject $userStorage;

    protected Service $service;

    protected function setUp(): void
    {
        $this->sessionStorage = $this->createMock(SessionStorage::class);
        $this->userStorage    = $this->createMock(UserStorage::class);

        $this->service = new Service($this->sessionStorage, $this->userStorage);
    }

    public function testUnknownUser(): void
    {
        $this->userStorage
            ->expects($this->once())
            ->method('fromIdentifier')
            ->with('identifier')
            ->willReturn(Result::failure('User not found'))
        ;

        $result = $this->service->authenticate('identifier', 'password');

        $this->assertFalse($result->isSuccess);
        $this->assertCount(2, $result->errors);
        $this->assertSame(['Identification failure', 'User not found'], $result->errors);
    }

    public function testWrongPassword(): void
    {
        $this->userStorage
            ->expects($this->once())
            ->method('fromIdentifier')
            ->with('identifier')
            ->willReturn(Result::success(new UserEntity(1, 'name', 'identifier', password_hash('password', PASSWORD_DEFAULT))))
        ;

        $result = $this->service->authenticate('identifier', 'password123');

        $this->assertFalse($result->isSuccess);
        $this->assertCount(1, $result->errors);
        $this->assertSame(['Identification failure'], $result->errors);
    }

    public function testAuthenticateSuccess(): void
    {
        $user = new UserEntity(1, 'name', 'identifier', password_hash('password', PASSWORD_DEFAULT));

        $this->userStorage
            ->expects($this->once())
            ->method('fromIdentifier')
            ->with('identifier')
            ->willReturn(Result::success($user))
        ;

        $this->sessionStorage
            ->expects($this->once())
            ->method('persistNewSession')
            ->with(
                $this->callback(fn (string $sessionId) => strlen($sessionId) === 64),
                $user,
            )
        ;

        $result = $this->service->authenticate('identifier', 'password');

        $this->assertTrue($result->isSuccess);
        $this->assertCount(0, $result->errors);
        $this->assertInstanceOf(SessionEntity::class, $result->unwrap());
    }

    public function testValidateMissingSession(): void
    {
        $failure = Result::failure('Session not found');
        $this->sessionStorage->expects($this->once())->method('findById')->with('missing')->willReturn($failure);
        $this->sessionStorage->expects($this->never())->method('setExpiration');

        $this->assertSame($failure, $this->service->validate('missing'));
    }

    public function testValidateExpiredSessionDoesNotRenewIt(): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $session = new SessionEntity('expired', $this->createMock(UserEntity::class), $now->modify('-2 hours'), $now);
        $this->sessionStorage->expects($this->once())->method('findById')->with('expired')->willReturn(Result::success($session));
        $this->sessionStorage->expects($this->never())->method('setExpiration');

        $result = $this->service->validate('expired');

        $this->assertFalse($result->isSuccess);
        $this->assertSame(['Session expired'], $result->errors);
    }

    public function testValidateReturnsRenewedSession(): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $session = new SessionEntity('valid', $this->createMock(UserEntity::class), $now, $now->modify('+1 hour'));
        $persistedExpiration = null;
        $this->sessionStorage->expects($this->once())->method('findById')->with('valid')->willReturn(Result::success($session));
        $this->sessionStorage->expects($this->once())->method('setExpiration')
            ->with($this->identicalTo($session), $this->isInstanceOf(\DateTimeImmutable::class))
            ->willReturnCallback(function (SessionEntity $storedSession, \DateTimeImmutable $expires) use (&$persistedExpiration): void {
                $persistedExpiration = $expires;
            });

        $result = $this->service->validate('valid');

        $this->assertTrue($result->isSuccess);
        $updated = $result->unwrap();
        $this->assertSame($persistedExpiration, $updated->expires);
        $this->assertGreaterThan($session->expires, $updated->expires);
        $this->assertSame($session->id, $updated->id);
        $this->assertSame($session->user, $updated->user);
        $this->assertSame($session->created, $updated->created);
    }

    public function testCloseSession(): void
    {
        $this->sessionStorage
            ->expects($this->once())
            ->method('setExpiration')
            ->with(
                $this->callback(fn (SessionEntity $session) => $session->id === 'sessionId'),
                $this->isInstanceOf(\DateTimeImmutable::class),
            )
        ;

        $created = new \DateTimeImmutable('2025-01-01 00:00:00');
        $expires = new \DateTimeImmutable('2026-01-01 00:00:00');

        $this->service->closeSession(new SessionEntity(
            'sessionId',
            $this->createMock(UserEntity::class),
            $created,
            $expires,
        ));
    }

    public function testBumpExpiration(): void
    {
        $user       = $this->createMock(UserEntity::class);
        $created    = new \DateTimeImmutable('2025-01-01 10:00:00');
        $oldExpires = new \DateTimeImmutable('2025-01-01 11:00:00');
        $session    = new SessionEntity('sess-id', $user, $created, $oldExpires);

        $this->sessionStorage
            ->expects($this->once())
            ->method('setExpiration')
            ->with(
                $this->callback(fn (SessionEntity $session) => $session->id === 'sess-id'),
                $this->isInstanceOf(\DateTimeImmutable::class),
            )
        ;

        $updatedSession = $this->service->bump($session);

        $this->assertSame('sess-id', $updatedSession->id);
        $this->assertGreaterThan($oldExpires, $updatedSession->expires);
        $this->assertSame($created, $updatedSession->created);
    }
}
