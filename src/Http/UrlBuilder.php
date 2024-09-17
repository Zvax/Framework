<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

class UrlBuilder
{
    /** @var array<string,string> */
    private array $urls = [];

    public function add(string $name, string $url): void
    {
        if (array_key_exists($name, $this->urls)) {
            throw new \RuntimeException(sprintf('Duplicate url name [%s]', $name));
        }

        $this->urls[$name] = $url;
    }
    public function build(string $name, array $arguments = [], array $queryParams = []): string
    {
        if (!array_key_exists($name, $this->urls)) {
            throw new \RuntimeException(sprintf('Unkown url [%s]', $name));
        }

        $url = $this->urls[$name];

        if (str_contains($url, '{')) {
            foreach ($arguments as $key => $value) {
                $key = sprintf('{%s}', $key);
                $url = str_replace($key, urlencode((string) $value), $url);
            }
        }

        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
}
