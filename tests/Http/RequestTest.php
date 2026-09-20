<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Http\Request;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    public function testStacksAttributes(): void
    {
        $request = new Request('GET', '/', [], [], [], [], []);

        $request = $request->withAttributes(['attr1' => 'value1']);

        $this->assertCount(1, $request->attributes);

        $request = $request->withAttributes(['attr2' => 'value2']);

        $this->assertCount(2, $request->attributes);
    }

    public function testHasHeaderIsCaseInsensitive(): void
    {
        $request = new Request('GET', '/', [], ['Content-Type' => 'application/json'], [], [], []);

        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertTrue($request->hasHeader('CONTENT-TYPE'));
    }

    public function testGetHeaderIsCaseInsensitive(): void
    {
        $request = new Request('GET', '/', [], ['Content-Type' => 'application/json'], [], [], []);

        $this->assertSame('application/json', $request->getHeader('Content-Type'));
        $this->assertSame('application/json', $request->getHeader('content-type'));
        $this->assertSame('application/json', $request->getHeader('CONTENT-TYPE'));
    }
}
