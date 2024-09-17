<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

readonly class Response
{
    /**
     * @param array<string,string> $headers
     * @param array<int,Cookie> $cookies
     */
    public function __construct(
        public int $statusCode,
        public string $body = '',
        public array $headers = [],
        public array $cookies = [],
    ) {}
}
