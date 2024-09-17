<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

interface RequestMiddleware
{
    public function process(Request $request): Request|Response;
}
