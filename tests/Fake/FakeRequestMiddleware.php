<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Fake;

use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\RequestMiddleware;
use Zvax\Framework\Http\Response;

class FakeRequestMiddleware implements RequestMiddleware
{
    public function process(Request $request): Request|Response
    {
        return $request;
    }
}
