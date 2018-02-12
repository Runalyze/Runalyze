<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParserWithSubParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Csv extends AbstractMultipleParserWithSubParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    public function parse()
    {
        if ($this->isEpsonFile()) {
            $this->useSubParser(new CsvEpson());
        } elseif ($this->isWahooFile()) {
            $this->useSubParser(new CsvWahoo());
        } else {
            throw new UnsupportedFileException();
        }
    }

    /**
     * @return bool
     */
    protected function isEpsonFile()
    {
        return strpos($this->FileContent, '[[Training]]') !== false;
    }

    /**
     * @return bool
     */
    protected function isWahooFile()
    {
        return strpos($this->FileContent, 'File created by Wahoo Fitness iPhone App') !== false;
    }
}
