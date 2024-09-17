<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Http;

use FastRoute\Dispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\Route;
use Zvax\Framework\Http\Routes;
use Zvax\Framework\Tests\Fake\FakeRequestMiddleware;
use Zvax\Framework\Tests\Fake\GetHandler;
use Zvax\Framework\Tests\Fake\PostHandler;

#[CoversClass(Routes::class)]
class RoutesTest extends TestCase
{
    public function testRequestProcessors(): void
    {
        $routes = new Routes();

        $routes->addRequestMiddlewareGroup(function (Routes $routes) {
            $routes->get('/', GetHandler::class);
        }, FakeRequestMiddleware::class);

        $request = new Request('GET', '/', [], [], [], [], []);

        $routeInfo = $routes->dispatch($request);

        [$status, $route, $vars] = $routeInfo;

        $this->assertSame(Dispatcher::FOUND, $status);
        $this->assertEquals(new Route('GET', '/', GetHandler::class, '', [FakeRequestMiddleware::class]), $route);
        $this->assertEmpty($vars);
    }

    public function testNestedRequestProcessors(): void
    {
        $routes = new Routes();

        $routes->addRequestMiddlewareGroup(function (Routes $routes) {
            $routes->addRequestMiddlewareGroup(function (Routes $routes) {
                $routes->post('/', PostHandler::class);
            }, FakeRequestMiddleware::class);

            $routes->get('/', GetHandler::class);
        }, FakeRequestMiddleware::class);

        $request = new Request('POST', '/', [], [], [], [], []);

        $routeInfo = $routes->dispatch($request);

        [$status, $route, $vars] = $routeInfo;

        $this->assertSame(Dispatcher::FOUND, $status);
        $this->assertEquals(new Route('POST', '/', PostHandler::class, '', [FakeRequestMiddleware::class, FakeRequestMiddleware::class]), $route);
        $this->assertEmpty($vars);

        $request = new Request('GET', '/', [], [], [], [], []);

        $routeInfo = $routes->dispatch($request);

        [$status, $route, $vars] = $routeInfo;

        $this->assertSame(Dispatcher::FOUND, $status);
        $this->assertEquals(new Route('GET', '/', GetHandler::class, '', [FakeRequestMiddleware::class]), $route);
        $this->assertEmpty($vars);
    }
}
