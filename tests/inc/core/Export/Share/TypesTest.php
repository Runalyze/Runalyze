<?php

namespace Runalyze\Export\Share;

use Runalyze\View\Activity\Context;

/**
 * @group dependsOn
 * @group dependsOnOldFactory
 */
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
