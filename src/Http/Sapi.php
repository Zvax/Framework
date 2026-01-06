<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

use Zvax\Framework\Http\Sapi\Service;

class Sapi
{
    private Service $cookieService;

    public function __construct(?Service $cookieService = null)
    {
        $this->cookieService = $cookieService ?? new Service();
    }

    public function emit(Response $response): void
    {
        http_response_code($response->statusCode);

        foreach ($response->headers as $header => $value) {
            header(sprintf('%s: %s', $header, $value));
        }

        array_map($this->cookieService->setCookie(...), $response->cookies);

        echo $response->body;
    }
}
