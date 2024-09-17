<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

class Sapi
{
    public static function emit(Response $response): void
    {
        http_response_code($response->statusCode);

        foreach ($response->headers as $header => $value) {
            header(sprintf('%s: %s', $header, $value));
        }

        foreach($response->cookies as $cookie) {
            $options = array_merge([
                //'expires' => 'never',
                'path' => '/',
                //'domain' => '',
                //'secure' => true,
                'httponly' => true,
                'samesite' => 'strict',
            ], $cookie->options);

            setcookie($cookie->name, $cookie->value, $options);
        }

        echo $response->body;
    }
}
