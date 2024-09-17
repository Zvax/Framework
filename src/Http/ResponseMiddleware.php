<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

interface ResponseMiddleware
{
    public function process(Response $response): Response;
}
