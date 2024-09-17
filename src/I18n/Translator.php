<?php declare(strict_types=1);

namespace Zvax\Framework\I18n;

readonly class Translator
{
    public function __construct(
        private array $translations,
        private InterpolationType $interpolationType,
    ) {}

    public function translate(string $key, array $parameters = []): string
    {
        if (!array_key_exists($key, $this->translations)) {
            return $key;
        }

        return match ($this->interpolationType) {
            InterpolationType::Keyed => '',
            InterpolationType::Sprintf => sprintf($this->translations[$key], ...$parameters),
        };
    }
}
