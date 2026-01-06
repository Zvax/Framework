<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use Zvax\Framework\Result;
use Zvax\Framework\Session\User\Storage as UserStorage;

readonly class Service
{
    public function __construct(
        private Storage     $sessionStorage,
        private UserStorage $userStorage,
    ) {}

    /**
     * @return Result<Entity>
     */
    public function authenticate(string $identifier, #[\SensitiveParameter] string $password): Result
    {
        $userResult = $this->userStorage->fromIdentifier($identifier);

        if (!$userResult->isSuccess) {
            return Result::failure('Identification failure', ...$userResult->errors);
        }

        $user = $userResult->unwrap();

        if (!password_verify($password, $user->password)) {
            return Result::failure('Identification failure');
        }

        $session = $this->sessionStorage->persistNewSession($this->generateSessionId(), $user);

        return Result::success($session);
    }

    public function validate(string $sessionId): Result
    {
        $sessionResult = $this->sessionStorage->findById($sessionId);

        if (!$sessionResult->isSuccess) {
            return $sessionResult;
        }

        $this->bump($sessionResult->unwrap());

        return $sessionResult;
    }

    public function bump(Entity $session, string $timeInterval = 'PT2H'): Entity
    {
        $now     = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = $now->add(new \DateInterval($timeInterval));

        $this->sessionStorage->setExpiration($session, $expires);

        return new Entity(
            $session->id,
            $session->user,
            $session->created,
            $expires,
        );
    }

    public function closeSession(Entity $session): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $this->sessionStorage->setExpiration($session, $now);
    }

    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }
}
