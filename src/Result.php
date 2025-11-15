<?php declare(strict_types=1);

namespace Zvax\Framework;

/**
 * @template T
 */
readonly class Result
{
    /**
     * @param array<int,string> $errors
     */
    private function __construct(
        public bool $isSuccess,
        private mixed $value,
        public array $errors,
    ) {}

    /**
     * @return T
     */
    public function unwrap()
    {
        return $this->value;
    }

    /**
     * @param T $value
     * @return self<T>
     */
    public static function success($value): self
    {
        return new self(true, $value, []);
    }

    public static function failure(string ...$errors): self
    {
        return new self(false, null, $errors);
    }
}
