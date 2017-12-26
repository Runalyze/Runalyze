<?php

namespace Runalyze\Parser\Activity\Common;

use Runalyze\Parser\Common\FileContentAwareParserInterface;

abstract class AbstractMultipleParserWithSubParser extends AbstractMultipleParser
{
    protected function useSubParser(ParserInterface $parser)
    {
        if (property_exists($this, 'FileContent') && $parser instanceof FileContentAwareParserInterface) {
            $parser->setFileContent($this->FileContent);
        }

        $parser->parse();

        $numContainer = $parser->getNumberOfActivities();

        for ($i = 0; $i < $numContainer; ++$i) {
            $this->Container[] = $parser->getActivityDataContainer($i);
        }
    }
}
