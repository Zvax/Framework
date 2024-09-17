<?php declare(strict_types=1);

namespace Zvax\Framework\Http\Response;

use Zvax\Framework\Http\Response;

readonly class Html extends Response
{
    public function __construct(string $content, int $statusCode = 200, array $headers = [])
    {
        parent::__construct(
            statusCode: $statusCode,
            body: $content,
            headers: array_merge([
                'Content-Type' => 'text/html; charset=utf-8',
            ], $headers),
        );
    }
}
