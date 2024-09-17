<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Http\UrlBuilder;

#[CoversClass(UrlBuilder::class)]
class UrlBuilderTest extends TestCase
{
    public function testBuildsWithArgument(): void
    {
        $urlBuilder = new UrlBuilder();
        $urlBuilder->add('url', '/fragment/{param}');

        $url = $urlBuilder->build('url', ['param' => 'value']);

        $this->assertSame('/fragment/value', $url);
    }

    public function testBuildsWithIntArgument(): void
    {
        $urlBuilder = new UrlBuilder();
        $urlBuilder->add('url', '/fragment/{param}');

        $url = $urlBuilder->build('url', ['param' => 123]);

        $this->assertSame('/fragment/123', $url);
    }
}
