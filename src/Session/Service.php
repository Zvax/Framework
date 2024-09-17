<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use PDO;
use Zvax\Framework\Result;
use Zvax\Framework\Session\User\Entity as UserEntity;
use Zvax\Framework\Session\User\Storage as UserStorage;

readonly class Service
{
    public function __construct(
        private Storage     $sessionStorage,
        private UserStorage $userStorage,
        private PDO         $pdo,
    ) {}

    /**
     * @return Result<Entity>
     */
    public function authenticate(string $identifier, #[\SensitiveParameter] string $password): Result
    {
        $userResult = $this->userStorage->fromIdentifier($identifier);

        if (!$userResult->isSuccess) {
            return Result::failure('Identification failure');
        }

        /** @var UserEntity $user */
        $user = $userResult->value;

        if (!password_verify($password, $user->password)) {
            return Result::failure('Identification failure');
        }

        $session = $this->sessionStorage->persistNewSession($this->generateSessionId(), $user);

        return Result::success($session);
    }

    public function closeSession(Entity $session): void
    {
        $updateExpiration = $this->pdo->prepare('update session set expires=:expires where id=:id');
        $updateExpiration->execute([
            ':id' => $session->id,
            ':expires' => new \DateTimeImmutable('UTC')->format('Y-m-d H:i:s'),
        ]);
    }

    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }
}
