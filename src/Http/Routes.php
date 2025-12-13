<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use function Zvax\Framework\typeImplements;

class Routes
{
    private RouteCollector $routeCollector;

    private UrlBuilder $urlBuilder;

    /** @var array<int,RequestMiddleware|string> */
    private array $requestMiddlewares = [];

    /** @var array<int,ResponseMiddleware|string> */
    private array $responseMiddlewares = [];

    /**
     * @param array<int,RequestMiddleware|string> $globalRequestMiddlewares
     * @param array<int,ResponseMiddleware|string> $globalResponseMiddlewares
     */
    public function __construct(
        ?RouteCollector        $routeCollector = null,
        ?UrlBuilder            $urlBuilder = null,
        private readonly array $globalRequestMiddlewares = [],
        private readonly array $globalResponseMiddlewares = [],
    ) {
        $this->routeCollector = $routeCollector ?? new RouteCollector(
            new RouteParser\Std(),
            new DataGenerator\GroupCountBased(),
        );

        $this->urlBuilder = $urlBuilder ?? new UrlBuilder();
    }

    public function buildUrl(string $name, array $arguments = [], $queryParams = []): string
    {
        return $this->urlBuilder->build($name, $arguments, $queryParams);
    }

    public function addRequestMiddlewareGroup(callable $definitions, RequestMiddleware|string ...$requestMiddlewares): self
    {
        $previousRequestMIddlewares = $this->requestMiddlewares;

        $this->requestMiddlewares = array_merge($previousRequestMIddlewares, $requestMiddlewares);

        $definitions($this);

        $this->requestMiddlewares = $previousRequestMIddlewares;

        return $this;
    }

    public function addResponseMiddlewareGroup(callable $definitions, ResponseMiddleware|string ...$responseMiddlewares): self
    {
        $previousResponseMiddlewares = $this->responseMiddlewares;

        $this->responseMiddlewares = array_merge($previousResponseMiddlewares, $responseMiddlewares);

        $definitions($this);

        $this->responseMiddlewares = $previousResponseMiddlewares;

        return $this;
    }

    private function addRoute(Route $route): self
    {
        if (!typeImplements($route->handler, RequestHandler::class)) {
            throw new \RuntimeException(
                sprintf('Type [ %s ] must implement [ %s ]', $route->handler, RequestHandler::class),
            );
        }

        if ($route->name !== '') {
            $this->urlBuilder->add($route->name, $route->path);
        }

        $this->routeCollector->addRoute($route->verb, $route->path, $route);

        return $this;
    }

    /**
     * @param array<int,RequestMiddleware|string> $requestMiddlewares
     * @param array<int,ResponseMiddleware|string> $responseMiddlewares
     */
    public function get(string $path, string $handler, string $name = '', array $requestMiddlewares = [], array $responseMiddlewares = []): self
    {
        return $this->addRoute(new Route(
            'GET',
            $path,
            $handler,
            $name,
            array_merge($this->globalRequestMiddlewares, $this->requestMiddlewares, $requestMiddlewares),
            array_merge($this->globalResponseMiddlewares, $this->responseMiddlewares, $responseMiddlewares),
        ));
    }

    /**
     * @param array<int,RequestMiddleware|string> $requestMiddlewares
     * @param array<int,ResponseMiddleware|string> $responseMiddlewares
     */
    public function post(string $path, string $handler, string $name = '', array $requestMiddlewares = [], array $responseMiddlewares = []): self
    {
        return $this->addRoute(new Route(
            'POST',
            $path,
            $handler,
            $name,
            array_merge($this->globalRequestMiddlewares, $this->requestMiddlewares, $requestMiddlewares),
            array_merge($this->globalResponseMiddlewares, $this->responseMiddlewares, $responseMiddlewares),
        ));
    }

    /**
     * @param array<int,RequestMiddleware|string> $requestMiddlewares
     * @param array<int,ResponseMiddleware|string> $responseMiddlewares
     */
    public function put(string $path, string $handler, string $name = '', array $requestMiddlewares = [], array $responseMiddlewares = []): self
    {
        return $this->addRoute(new Route(
            'PUT',
            $path,
            $handler,
            $name,
            array_merge($this->globalRequestMiddlewares, $this->requestMiddlewares, $requestMiddlewares),
            array_merge($this->globalResponseMiddlewares, $this->responseMiddlewares, $responseMiddlewares),
        ));
    }

    /**
     * @param array<int,RequestMiddleware|string> $requestMiddlewares
     * @param array<int,ResponseMiddleware|string> $responseMiddlewares
     */
    public function patch(string $path, string $handler, string $name = '', array $requestMiddlewares = [], array $responseMiddlewares = []): self
    {
        return $this->addRoute(new Route(
            'PATCH',
            $path,
            $handler,
            $name,
            array_merge($this->globalRequestMiddlewares, $this->requestMiddlewares, $requestMiddlewares),
            array_merge($this->globalResponseMiddlewares, $this->responseMiddlewares, $responseMiddlewares),
        ));
    }

    /**
     * @param array<int,RequestMiddleware|string> $requestMiddlewares
     * @param array<int,ResponseMiddleware|string> $responseMiddlewares
     */
    public function delete(string $path, string $handler, string $name = '', array $requestMiddlewares = [], array $responseMiddlewares = []): self
    {
        return $this->addRoute(new Route(
            'DELETE',
            $path,
            $handler,
            $name,
            array_merge($this->globalRequestMiddlewares, $this->requestMiddlewares, $requestMiddlewares),
            array_merge($this->globalResponseMiddlewares, $this->responseMiddlewares, $responseMiddlewares),
        ));
    }

    public function dispatch(Request $request): array
    {
        return (new Dispatcher\GroupCountBased($this->routeCollector->getData()))
            ->dispatch($request->method, $request->uri)
        ;
    }
}
