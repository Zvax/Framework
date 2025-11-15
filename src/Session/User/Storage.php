<?php declare(strict_types=1);

namespace Zvax\Framework\Session\User;

use PDO;
use Zvax\Framework\Result;

readonly class Storage
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function fromId(int $id): Result
    {
        $getUserRow = $this->pdo->prepare('select * from user where id=:id');
        $getUserRow->execute([':id' => $id]);

        if ($getUserRow->rowCount() === 0) {
            return Result::failure('User not found');
        }

        return Result::success($this->fromRow($getUserRow->fetch()));
    }

    /**
     * @return Result<Entity>
     */
    public function fromIdentifier(string $identifier): Result
    {
        $getUserRow = $this->pdo->prepare('select * from user where identifier=:identifier');
        $getUserRow->execute([':identifier' => $identifier]);

        if ($getUserRow->rowCount() === 0) {
            return Result::failure('User not found');
        }

        return Result::success($this->fromRow($getUserRow->fetch()));
    }

    private function fromRow(array $row): Entity
    {
        return new Entity(
            $row['id'],
            $row['name'],
            $row['identifier'],
            $row['password'],
        );
    }
}
