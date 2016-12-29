<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Selection;

use Runalyze\Bundle\CoreBundle\Services\Selection\Selection;

class SelectionTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptySelection()
    {
        $list = new Selection([]);

        $this->assertEquals([], $list->getList());
        $this->assertNull($list->getCurrentKey());
        $this->assertFalse($list->hasCurrentKey());
    }

    public function testSelectionWithoutCurrentKey()
    {
        $list = new Selection(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $list->getList());
        $this->assertNull($list->getCurrentKey());
        $this->assertFalse($list->hasCurrentKey());
        $this->assertEquals('foo', $list->getCurrentLabel());
    }

    public function testSelectionWithCurrentKey()
    {
        $list = new Selection(['foo', 'bar'], 1);

        $this->assertEquals(1, $list->getCurrentKey());
        $this->assertTrue($list->hasCurrentKey());
        $this->assertEquals('bar', $list->getCurrentLabel());
    }

    public function testUnknownCurrentKey()
    {
        $list = new Selection(['foo' => 'bar'], 'baz');

        $this->assertFalse($list->hasCurrentKey());
    }
}
