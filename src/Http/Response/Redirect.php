<?php declare(strict_types=1);

namespace Zvax\Framework\Http\Response;

use Zvax\Framework\Http\Response;

readonly class Redirect extends Response
{
    public function __construct(string $location, int $statusCode = 303, array $cookies = [])
    {
        parent::__construct(
            $statusCode,
            headers: ['Location' => $location],
            cookies: $cookies,
        );
    }
}
