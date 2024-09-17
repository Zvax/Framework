<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

readonly class Route
{
    /**
     * @param array<int,string|RequestMiddleware> $requestProcessors
     * @param array<int,string|RequestMiddleware> $responseProcessors
     */
    public function __construct(
        public string $verb,
        public string $path,
        public string $handler,
        public string $name = '',
        public array  $requestProcessors = [],
        public array  $responseProcessors = [],
    ) {}
}
