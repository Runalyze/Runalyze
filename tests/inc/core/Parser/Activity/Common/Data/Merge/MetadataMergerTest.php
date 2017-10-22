<?php

namespace Runalyze\Tests\Parser\Activity\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\Merge\MetadataMerger;
use Runalyze\Parser\Activity\Common\Data\Metadata;

class MetadataMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Metadata */
    protected $FirstMetadata;

    /** @var Metadata */
    protected $SecondMetadata;

    public function setUp()
    {
        $this->FirstMetadata = new Metadata();
        $this->SecondMetadata = new Metadata();
    }

    public function testThatMergeWorksWithEmptyObjects()
    {
        (new MetadataMerger($this->FirstMetadata, $this->SecondMetadata))->merge();
    }

    public function testThatTimestampIsNotOverridden()
    {
        $this->FirstMetadata->setTimestamp(123456789);
        $this->SecondMetadata->setTimestamp(987654321, 120);

        (new MetadataMerger($this->FirstMetadata, $this->SecondMetadata))->merge();

        $this->assertEquals(123456789, $this->FirstMetadata->getTimestamp());
    }

    public function testMergingCreatorDetails()
    {
        $this->FirstMetadata->setCreator('foo');
        $this->SecondMetadata->setCreator('', 'bar');

        (new MetadataMerger($this->FirstMetadata, $this->SecondMetadata))->merge();

        $this->assertEquals('foo', $this->FirstMetadata->getCreator());
        $this->assertEquals('bar', $this->FirstMetadata->getCreatorDetails());
    }

    public function testMergingEquipment()
    {
        $this->FirstMetadata->addEquipment('Foo');
        $this->SecondMetadata->addEquipment('Bar');

        (new MetadataMerger($this->FirstMetadata, $this->SecondMetadata))->merge();

        $this->assertEquals(['Foo', 'Bar'], $this->FirstMetadata->getEquipmentNames());
    }
}
