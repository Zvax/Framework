<?php declare(strict_types=1);

namespace Zvax\Framework\Session;

use Zvax\Framework\Session\User\Entity as UserEntity;

readonly class Entity
{
    public function __construct(
        public string $id,
        public UserEntity $user,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $expires,
    ) {}
}
