<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

readonly class Cookie
{
    /**
     * @param array<string,string> $options
     */
    public function __construct(
        public string $name,
        public string $value,
        public int    $expires,
        public array  $options = [],
    ) {}
}
