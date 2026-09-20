<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use Zvax\Framework\Result;
use Zvax\Framework\Session\Entity as SessionEntity;
use Zvax\Framework\Session\User\Entity as UserEntity;

interface SessionStorageInterface
{
    /**
     * @return Result<SessionEntity>
     */
    public function findById(string $sessionId): Result;

    /**
     * @throws \RuntimeException when no session exists for the id, use findById() to fail gracefully
     */
    public function fromId(string $sessionId): SessionEntity;

    public function persistNewSession(string $sessionId, UserEntity $user): SessionEntity;

    public function setExpiration(SessionEntity $session, \DateTimeImmutable $expires): void;
}
