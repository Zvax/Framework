<?php declare(strict_types=1);

namespace Zvax\Framework\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zvax\Framework\Http\Cookie;
use Zvax\Framework\Http\Response\Html;
use Zvax\Framework\Http\Sapi;
use Zvax\Framework\Http\Sapi\Service;

#[CoversClass(Sapi::class)]
class SapiTest extends TestCase
{
    public function testSetsCookies(): void
    {
        $service  = $this->createMock(Service::class);
        $sapi     = new Sapi($service);
        $response = new Html('body', cookies: [new Cookie('cookieName', 'cookieValue', 2500)]);

        $service
            ->expects($this->once())
            ->method('setCookie')
            ->with($this->callback(function (Cookie $cookie) {
                $this->assertSame('cookieName', $cookie->name);
                $this->assertSame('cookieValue', $cookie->value);
                $this->assertSame(2500, $cookie->expires);
                $this->assertSame([], $cookie->options);
                return true;
            }))
        ;

        $this->expectOutputString('body');

        $sapi->emit($response);
    }
}
