<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Common\FileContentAwareParserInterface;

abstract class AbstractActivityParserTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityDataContainer */
    protected $Container;

    /**
     * @param FileContentAwareParserInterface $parser
     * @param string $file file path relative to 'testfiles/'
     * @param bool $completeAfterwards
     */
    protected function parseFile(FileContentAwareParserInterface $parser, $file, $completeAfterwards = true)
    {
        $parser->setFileContent(
            file_get_contents(__DIR__.'/../../../../../testfiles/'.$file)
        );
        $parser->parse();

        $this->Container = $parser->getActivityDataContainer();

        if ($completeAfterwards) {
            $this->Container->completeActivityData();
        }
    }
}
