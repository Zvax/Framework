<?php declare(strict_types=1);

namespace Zvax\Framework\I18n;

enum InterpolationType
{

    case Keyed; // "this is a {keyed} translation {string}"
    case Positional; // "this is a {} positional translation {} string" "this is {0} as well {2}"
    case Sprintf; // "this is an %s sprintf %d translation string"
}
