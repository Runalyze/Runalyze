<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResult;
use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResultCollection;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class FileImportResultCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayAccessAndIterator()
    {
        $collection = new FileImportResultCollection();
        $collection[] = new FileImportResult([], 'a.foo', 'a.foo');
        $collection[] = new FileImportResult([], 'b.foo', 'a.foo');
        $collection[] = new FileImportResult([], 'a.bar', 'b.bar');
        $collection[] = new FileImportResult([
            new ActivityDataContainer(), new ActivityDataContainer(), new ActivityDataContainer()
        ], 'b.bar', 'b.bar');
        $collection[] = new FileImportResult([], 'c.bar', 'c.bar');
        unset($collection[4]);

        $this->assertEquals(4, count($collection));
        $this->assertEquals(3, $collection->getTotalNumberOfActivities());

        foreach ($collection as $result) {
            $this->assertEquals('.', substr($result->getFileName(), 1, 1));
        }
    }

    public function testFileNames()
    {
        $collection = new FileImportResultCollection([
            new FileImportResult([], 'a.foo', 'a.foo'),
            new FileImportResult([], 'b.foo', 'a.foo'),
            new FileImportResult([], 'b.bar', 'c.bar'),
            new FileImportResult([], 'c.bar', 'c.bar')
        ]);

        $this->assertEquals(['b.foo', 'b.bar'], $collection->getAllConvertedFileNames());
        $this->assertEquals(['a.foo', 'c.bar'], $collection->getAllOriginalFileNames());
    }
}
