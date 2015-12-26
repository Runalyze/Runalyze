<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\Context;

class TypesTest extends \PHPUnit_Framework_TestCase
{
    public function testAllConstructors()
    {
        $context = new Context(0, 0);

        foreach (Types::getEnum() as $typeid) {
            Types::get($typeid, $context);
        }
    }
}
