<?php declare(strict_types=1);

namespace Zvax\Framework\Session\User;

use Zvax\Framework\Result;

interface UserStorageInterface
{
    public function fromId(int $id): ?Entity;

    /**
     * @return Result<Entity>
     */
    public function fromIdentifier(string $identifier): Result;
}
