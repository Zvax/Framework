<?php declare(strict_types=1);

namespace Zvax\Framework;

use Auryn\Injector;
use FastRoute\Dispatcher;
use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\Response;
use Zvax\Framework\Http\Route;
use Zvax\Framework\Http\Routes;

readonly class App
{
    public function __construct(private Routes $routes, private Injector $auryn) {}

    public function run(Request $request): Response
    {
        $routeInfo = $this->routes->dispatch($request);

        return match ($routeInfo[0]) {
            Dispatcher::FOUND => $this->foundRoute($routeInfo, $request),
            Dispatcher::METHOD_NOT_ALLOWED => new Response(405, 'Method Not Allowed', [], []),
            Dispatcher::NOT_FOUND => new Response(404, 'Not Found', [], []),
        };
    }

    private function foundRoute(array $routeInfo, Request $request): Response
    {
        /** @var Route $route */
        [, $route, $vars] = $routeInfo;

        $request = $request->withAttributes($vars);

        if (count($route->requestProcessors) > 0) {
            foreach ($route->requestProcessors as $processorClass) {
                $processor = $this->auryn->make($processorClass);

                $result = $processor->process($request);

                if ($result instanceof Response) {
                    return $result;
                }

                $request = $result;
            }
        }

        $handler = $this->auryn->make($route->handler);

        $response = $handler->handle($request);

        if (count($route->responseProcessors) > 0) {
            foreach ($route->responseProcessors as $processorClass) {
                $processor = $this->auryn->make($processorClass);

                $response = $processor->process($response);
            }
        }

        return $response;
    }
}
