<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
