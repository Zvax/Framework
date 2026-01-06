<?php declare(strict_types=1);

namespace Zvax\Framework\Http\Response;

use Zvax\Framework\Http\Response;

readonly class Html extends Response
{
    public function __construct(string $content, int $statusCode = 200, array $headers = [], array $cookies = [])
    {
        parent::__construct(
            $statusCode,
            $content,
            array_merge($headers, ['Content-Type' => 'text/html; charset=utf-8']),
            $cookies,
        );
    }
}
