<?php declare(strict_types=1);

namespace Zvax\Framework;

function typeImplements(string $type, string $targetInterface): bool
{
    return class_exists($type) && isset(class_implements($type)[$targetInterface]);
}
