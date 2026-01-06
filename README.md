# Zvax/Framework

Opinionated tools for php website creation.

bootstrap.php, invoked from a public index.php probably

```php
<?php declare(strict_types=1);

use Userland\LoggedArea;
use Userland\Login;
use Userland\SomeSessionCheckMiddleware;
use Zvax\Framework\App;
use Zvax\Framework\Http\Request;
use Zvax\Framework\Http\Routes;
use Zvax\Framework\Http\Sapi;

$routes = new Routes();

$routes->addRequestMiddlewareGroup(function (Routes $routes) {
    $routes->get('/loggedArea', LoggedArea::class);
}, SomeSessionCheckMiddleware::class);

$routes->get('/', Login::class);

$app = new App($routes, $auryn);

$response = $app->run(Request::fromGlobals());

new Sapi()->emit($response);
```
