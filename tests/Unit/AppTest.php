<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit;

use Auryn\Injector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\App;
use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\Response;
use Zvax\Framework\Http\Routes;
use Zvax\Framework\Tests\Fake\FakeRequestMiddleware;
use Zvax\Framework\Tests\Fake\GetHandler;

#[CoversClass(App::class)]
class AppTest extends TestCase
{
    public function testinvokesRequestProcessors(): void
    {
        $routes = new Routes();

        $routes->addRequestMiddlewareGroup(function (Routes $routes) {
            $routes->get('/', GetHandler::class);
        }, FakeRequestMiddleware::class);

        $auryn    = $this->createMock(Injector::class);
        $app      = new App($routes, $auryn);
        $request  = new Request('GET', '/');
        $response = new Response(200, 'response body', [], []);

        $i = 0;

        $makeArguments = [
            FakeRequestMiddleware::class,
            GetHandler::class,
        ];

        $fakeRequestMiddleware = $this->createMock(FakeRequestMiddleware::class);
        $fakeRequestMiddleware
            ->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn($request)
        ;

        $fakeGetHandler = $this->createMock(GetHandler::class);
        $fakeGetHandler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response)
        ;

        $auryn
            ->expects($this->exactly(2))
            ->method('make')
            ->with($this->callback(function (string $argument) use ($makeArguments, &$i) {
                $this->assertSame($makeArguments[$i], $argument);
                $i++;
                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $fakeRequestMiddleware,
                $fakeGetHandler,
            )
        ;

        $this->assertSame($response, $app->run($request));
    }
}
