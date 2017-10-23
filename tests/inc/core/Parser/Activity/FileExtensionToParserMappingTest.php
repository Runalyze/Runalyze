<?php

namespace Runalyze\Tests\Parser\Activity\Data;

use Runalyze\Parser\Activity\Common\ParserInterface;
use Runalyze\Parser\Activity\FileExtensionToParserMapping;
use Runalyze\Parser\Activity\FileType\Tcx;

class FileExtensionToParserMappingTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileExtensionToParserMapping */
    protected $Mapping;

    public function setUp()
    {
        $this->Mapping = new FileExtensionToParserMapping();
    }

    public function testInvalidExtension()
    {
        $this->assertNull($this->Mapping->getParserClassFor('foobar'));
    }

    public function testThatMappingIsCaseInsensitive()
    {
        $this->assertEquals(Tcx::class, $this->Mapping->getParserClassFor('tcx'));
        $this->assertEquals(Tcx::class, $this->Mapping->getParserClassFor('TCX'));
    }

    public function testThatAllMappedParserExist()
    {
        foreach (FileExtensionToParserMapping::MAPPING as $extension => $class) {
            $this->assertInstanceOf(ParserInterface::class, new $class);
        }
    }
}
