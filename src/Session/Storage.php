<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use PDO;
use Zvax\Framework\Result;
use Zvax\Framework\Session\Entity as SessionEntity;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\Storage as UserStorage;

readonly class Storage
{
    public function __construct(
        private PDO         $pdo,
        private UserStorage $userStorage,
    ) {}

    /**
     * @return Result<SessionEntity>
     */
    public function findById(string $sessionId): Result
    {
        $getSessionRow =  $this->pdo->prepare('select * from zvax_sessions where id = :sessionId');
        $getSessionRow->execute([':sessionId' => $sessionId]);

        if ($getSessionRow->rowCount() === 0) {
            return Result::failure('Session not found');
        }

        return Result::success($this->fromRow($getSessionRow->fetch()));
    }

    private function fromRow(array $row): Entity
    {
        return new Entity(
            $row['id'],
            $this->userStorage->fromId($row['user_id']),
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['expires_at']),
        );
    }

    public function fromId(string $sessionId): ?Entity
    {
        $getSessionRow =  $this->pdo->prepare('select * from zvax_sessions where id = :sessionId');
        $getSessionRow->execute([':sessionId' => $sessionId]);

        if ($getSessionRow->rowCount() === 0) {
            return null;
        }

        return $this->fromRow($getSessionRow->fetch());
    }

    public function persistNewSession(string $sessionId, UserEntity $user): Entity
    {
        $insertSession = $this->pdo->prepare('
            insert into zvax_sessions(id, user_id, created_at, expires_at)
            values (:id, :user_id, :created_at, :expires_at)
        ');

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = $now->add(new \DateInterval('PT2H'));

        $insertSession->execute([
            ':id' => $sessionId,
            ':user_id' => $user->id,
            ':created_at' => $now->format('Y-m-d H:i:s'),
            ':expires_at' => $expires->format('Y-m-d H:i:s'),
        ]);

        return new Entity(
            $sessionId,
            $user,
            $now,
            $expires,
        );
    }

    public function setExpiration(Entity $session, \DateTimeImmutable $expires): void
    {
        $setExpiration = $this->pdo->prepare('update zvax_sessions set expires_at = :expires_at where id = :id');

        $setExpiration->execute([
            ':id'         => $session->id,
            ':expires_at' => $expires->format('Y-m-d H:i:s'),
        ]);
    }
}
