<?php

use Runalyze\Bundle\CoreBundle\Component\Configuration\ConfigurationList;

class ConfigurationListTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleConstructor()
    {
        $list = new ConfigurationList(['foo' => 'bar']);

        $this->assertEquals('bar', $list->get('foo'));
    }

    public function testMerging()
    {
        $list = new ConfigurationList([
            'foo' => 1,
            'bar' => 2
        ]);
        $list->mergeWith([
            'foo' => 42,
            'baz' => true
        ]);

        $this->assertEquals(42, $list->get('foo'));
        $this->assertEquals(2, $list->get('bar'));
        $this->assertEquals(true, $list->get('baz'));
    }
}
