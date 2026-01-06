<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Session;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Session\Entity;
use Zvax\Framework\Session\Storage;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\Storage as UserStorage;

#[CoversClass(Storage::class)]
class StorageTest extends TestCase
{
    private PDO|MockObject $pdo;
    private UserStorage|MockObject $userStorage;
    private Storage $storage;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->userStorage = $this->createMock(UserStorage::class);
        $this->storage = new Storage($this->pdo, $this->userStorage);
    }

    public function testFindByIdReturnsFailureWhenNotFound(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('rowCount')->willReturn(0);
        $statement->expects($this->once())->method('execute')->with([':sessionId' => 'missing']);

        $this->pdo->method('prepare')->willReturn($statement);

        $result = $this->storage->findById('missing');

        $this->assertFalse($result->isSuccess);
        $this->assertSame(['Session not found'], $result->errors);
    }

    public function testFindByIdReturnsSuccessWhenFound(): void
    {
        $sessionId = 'session-123';
        $userId = 456;
        $created = '2025-01-01 10:00:00';
        $expires = '2025-01-01 12:00:00';

        $statement = $this->createMock(PDOStatement::class);
        $statement->method('rowCount')->willReturn(1);
        $statement->method('fetch')->willReturn([
            'id' => $sessionId,
            'user_id' => $userId,
            'created_at' => $created,
            'expires_at' => $expires,
        ]);

        $this->pdo
            ->method('prepare')
            ->with($this->stringContains('select * from zvax_sessions where id = :sessionId'))
            ->willReturn($statement)
        ;

        $user = $this->createMock(UserEntity::class);
        $this->userStorage
            ->method('fromId')
            ->with($userId)
            ->willReturn($user)
        ;

        $result = $this->storage->findById($sessionId);

        $this->assertTrue($result->isSuccess);
        /** @var Entity $session */
        $session = $result->unwrap();
        $this->assertSame($sessionId, $session->id);
        $this->assertSame($user, $session->user);
        $this->assertSame($created, $session->created->format('Y-m-d H:i:s'));
        $this->assertSame($expires, $session->expires->format('Y-m-d H:i:s'));
    }

    public function testPersistNewSession(): void
    {
        $sessionId = 'new-session';

        $user = new UserEntity(
            1,
            'name',
            'identifier',
            'password hash',
        );

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params) use ($sessionId) {
                return $params[':id'] === $sessionId && $params[':user_id'] === 1;
            }));

        $this->pdo->method('prepare')->willReturn($statement);

        $session = $this->storage->persistNewSession($sessionId, $user);

        $this->assertSame($sessionId, $session->id);
        $this->assertSame($user, $session->user);
        $this->assertInstanceOf(\DateTimeImmutable::class, $session->created);
        $this->assertInstanceOf(\DateTimeImmutable::class, $session->expires);
    }
}
