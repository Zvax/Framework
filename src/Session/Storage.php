<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use PDO;
use Zvax\Framework\Result;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\Storage as UserStorage;

readonly class Storage
{
    public function __construct(
        private PDO         $pdo,
        private UserStorage $userStorage,
    ) {}

    public function findById(string $sessionId): Result
    {
        $getSessionRow =  $this->pdo->prepare('select * from zvax_sessions where id = :sessionId');
        $getSessionRow->execute([':sessionId' => $sessionId]);

        if ($getSessionRow->rowCount() === 0) {
            return Result::failure('Session not found');
        }

        $row = $getSessionRow->fetch();

        $userResult = $this->userStorage->fromId($row['user_id']);

        /**
         * We have a foreign key, so let's assume the result is success actually
         */

        $user = $userResult->unwrap();

        $session = new Entity(
            $row['id'],
            $user,
            new \DateTimeImmutable($row['created']),
            new \DateTimeImmutable($row['expires']),
        );

        return Result::success($session);
    }

    public function persistNewSession(string $sessionId, UserEntity $user): Entity
    {
        $insertSession = $this->pdo->prepare('
            insert into zvax_sessions(id, user_id, created, expires)
            values (:id, :user_id, :created, :expires)
        ');

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = $now->add(new \DateInterval('PT2H'));

        $insertSession->execute([
            ':id' => $sessionId,
            ':user_id' => $user->id,
            ':created' => $now->format('Y-m-d H:i:s'),
            ':expires' => $expires->format('Y-m-d H:i:s'),
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
        $setExpiration = $this->pdo->prepare('update zvax_sessions set expires = :expires where id = :id');

        $setExpiration->execute([
            ':id' => $session->id,
            ':expires' => $expires->format('Y-m-d H:i:s'),
        ]);
    }
}
