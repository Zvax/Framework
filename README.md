# Zvax/Framework

Opinionated tools for php website creation.

bootstrap.php, invoked from a public index.php probably

```php
<?php declare(strict_types=1);

$routes = new \Zvax\Framework\Http\Routes();

$routes->addRequestMiddlewareGroup(function (Routes $routes) {
    $routes->get('/loggedArea', LoggedArea::class);
}, SomeSessionCheckMiddleware::class);

$routes->get('/', Login::class);

$app = new \Zvax\Framework\App($routes, $auryn);

$response = $app->run(\Zvax\Framework\Http\Request::fromGlobals());

\Zvax\Framework\Http\Sapi::emit($response);
```
