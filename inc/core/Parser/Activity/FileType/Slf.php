<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParserWithSubParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Slf extends AbstractMultipleParserWithSubParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    public function parse()
    {
        if ($this->isSlf3()) {
            $this->useSubParser(new Slf3());
        } elseif ($this->isSlf4()) {
            $this->useSubParser(new Slf4());
        } else {
            throw new UnsupportedFileException();
        }
    }

    /**
     * @return bool
     */
    protected function isSlf3()
    {
        return strpos($this->FileContent, '<LogEntries') !== false;
    }

    /**
     * @return bool
     */
    protected function isSlf4()
    {
        return strpos($this->FileContent, '<Entries') !== false;
    }
}
