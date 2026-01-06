<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Fake;

use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\RequestHandler;
use Zvax\Framework\Http\Response;

class PostHandler implements RequestHandler
{
    public function handle(Request $request): Response
    {
        return new Response(200, '', [], []);
    }
}
