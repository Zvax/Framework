<?php declare(strict_types=1);

namespace Zvax\Framework\Session\User;

readonly class Entity
{
    public function __construct(
        public int $id,
        public string $name,
        public string $identifier,
        public string $password,
    ) {}
}
