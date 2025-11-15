<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Session;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Result;
use Zvax\Framework\Session\Entity as SessionEntity;
use Zvax\Framework\Session\Service;
use Zvax\Framework\Session\Storage as SessionStorage;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\Storage as UserStorage;

#[CoversClass(Service::class)]
class ServiceTest extends TestCase
{
    protected SessionStorage&MockObject $sessionStorage;
    protected UserStorage&MockObject $userStorage;
    protected PDO&MockObject $pdo;

    protected Service $service;

    protected function setUp(): void
    {
        $this->sessionStorage = $this->createMock(SessionStorage::class);
        $this->userStorage    = $this->createMock(UserStorage::class);
        $this->pdo            = $this->createMock(PDO::class);

        $this->service = new Service($this->sessionStorage, $this->userStorage, $this->pdo);
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

    public function testCloseSession(): void
    {
        $updateExpirationStatement = $this->createMock(PDOStatement::class);
        $updateExpirationStatement
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $data) {
                $expires = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data[':expires']);

                $this->assertSame('sessionId', $data[':id']);
                $this->assertSame($expires->format('Y-m-d H:i:s'), $data[':expires']);

                return true;
            }))
        ;

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('update session set expires=:expires where id=:id')
            ->willReturn($updateExpirationStatement)
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
}
