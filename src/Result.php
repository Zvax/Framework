<?php declare(strict_types=1);

namespace Zvax\Framework;

readonly class Result
{
    /**
     * @param array<int,string> $errors
     */
    private function __construct(
        public bool $isSuccess,
        public mixed $value,
        public array $errors,
    ) {}

    public static function success(mixed $value): self
    {
        return new self(true, $value, []);
    }

    public static function failure(string ...$errors): self
    {
        return new self(false, null, $errors);
    }
}
