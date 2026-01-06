<?php declare(strict_types=1);

namespace Zvax\Framework\Http\Sapi;

use Zvax\Framework\Http\Cookie;

class Service
{
    public function setCookie(Cookie $cookie): void
    {
        $options = array_merge([
            'expires' => $cookie->expires,
            'path' => '/',
            //'domain' => '',
            //'secure' => true,
            'httponly' => true,
            'samesite' => 'strict',
        ], $cookie->options);

        setcookie($cookie->name, $cookie->value, $options);
    }
}
