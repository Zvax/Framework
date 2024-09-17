<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

readonly class Request
{
    /**
     * @param array<string,string> $queryParams
     * @param array<string,string> $headers
     * @param array<string,string> $cookies
     * @param array<string,string> $attributes
     * @param array<string,mixed> $parsedBody
     */
    public function __construct(
        public string $method = '',
        public string $uri = '',
        public array $queryParams = [],
        public array $headers = [],
        public array $cookies = [],
        public array $attributes = [],
        public array $parsedBody = [],
    ) {}

    public static function fromGlobals(array $server = [], array $headers = []): self
    {
        $server = array_merge($_SERVER, $server);
        $headers = array_merge(getallheaders(), $headers);

        if (str_contains($server['REQUEST_URI'], '?')) {
            [$uri, $queryString] = explode('?', $server['REQUEST_URI']);
        } else {
            $uri = $server['REQUEST_URI'];
        }

        parse_str($queryString ?? '', $queryParams);

        $method = $_SERVER['REQUEST_METHOD'];

        $parsedBody = match($method) {
            'PUT', 'PATCH' => self::parseBodyFromInput(),
            'POST' => $_POST,
            default => [],
        };

        return new self(
            $_SERVER['REQUEST_METHOD'],
            $uri,
            $queryParams,
            $headers,
            $_COOKIE,
            [],
            $parsedBody,
        );
    }

    public function hasHeader(string $header): bool
    {
        return array_key_exists($header, $this->headers);
    }

    public function getHeader(string $header): string
    {
        return $this->headers[$header];
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    private static function parseBodyFromInput(): array
    {
        parse_str(file_get_contents("php://input"), $data);

        return $data;
    }

    public function withAttributes(array $attributes): self
    {
        return new self(
            $this->method,
            $this->uri,
            $this->queryParams,
            $this->headers,
            $this->cookies,
            array_merge($this->attributes, $attributes),
            $this->parsedBody,
        );
    }
}
