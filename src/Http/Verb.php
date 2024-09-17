<?php declare(strict_types=1);

namespace Zvax\Framework\Http;

enum Verb: string
{
    case Head = 'HEAD';
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
}
