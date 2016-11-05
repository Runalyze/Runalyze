<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component;

use Runalyze\Bundle\CoreBundle\Component\VariablesContainerTrait;

class VariablesContainerTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @var object */
    protected $Object;

    /** @var \ReflectionMethod */
    protected $GetMethod;

    /** @var \ReflectionMethod */
    protected $SetMethod;

    public function setUp()
    {
        $this->Object = $this->getObjectForTrait(VariablesContainerTrait::class);
        $this->GetMethod = new \ReflectionMethod($this->Object, 'get');
        $this->SetMethod = new \ReflectionMethod($this->Object, 'set');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGettingUnknownKey()
    {
        $this->GetMethod->invoke($this->Object, 'foobar');
    }

    public function testStoringValue()
    {
        $this->SetMethod->invoke($this->Object, 'foo', 'bar');

        $this->assertEquals('bar', $this->GetMethod->invoke($this->Object, 'foo'));
    }

    public function testOverwritingValue()
    {
        $this->SetMethod->invoke($this->Object, 'foo', 'bar');
        $this->SetMethod->invoke($this->Object, 'foo', 'baz');

        $this->assertEquals('baz', $this->GetMethod->invoke($this->Object, 'foo'));
    }
}
