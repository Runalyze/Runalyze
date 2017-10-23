<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParserWithSubParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Xml extends AbstractMultipleParserWithSubParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    public function parse()
    {
        if ($this->isPolarFile()) {
            $this->useSubParser(new XmlPolar());
        } elseif ($this->isSuuntoFile()) {
            $this->useSubParser(new XmlSuunto());
        } elseif ($this->isRunningAheadFile()) {
            throw new UnsupportedFileException('Support for xml-files from RunningAHEAD has been dropped.');
        }

        throw new UnsupportedFileException();
    }

    /**
     * @return bool
     */
    protected function isRunningAheadFile()
    {
        return strpos($this->FileContent, '<RunningAHEADLog') !== false;
    }

    /**
     * @return bool
     */
    protected function isSuuntoFile()
    {
        return strpos($this->FileContent, '<header>') !== false;
    }

    /**
     * @return bool
     */
    protected function isPolarFile()
    {
        return strpos($this->FileContent, '<polar-exercise-data') !== false;
    }
}
