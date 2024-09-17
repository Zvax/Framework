<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Http;

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
}
