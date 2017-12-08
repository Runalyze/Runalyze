<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParserWithSubParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Kml extends AbstractMultipleParserWithSubParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    public function parse()
    {
        if ($this->isTomTomFile()) {
            $this->useSubParser(new KmlExtended());
        } elseif ($this->isNamespacedFile()) {
            $this->useSubParser(new KmlGoogle('kml'));
        } elseif ($this->isDefaultFile()) {
            $this->useSubParser(new KmlGoogle());
        } else {
            throw new UnsupportedFileException('Kml file is not from TomTom or Google or does not contain any coordinates.');
        }
    }

    /**
     * @return bool
     */
    protected function isTomTomFile()
    {
        return strpos($this->FileContent, '<gx:Track') !== false;
    }

    /**
     * @return bool
     */
    protected function isNamespacedFile()
    {
        return strpos($this->FileContent, '<kml:coordinates') !== false;
    }

    /**
     * @return bool
     */
    protected function isDefaultFile()
    {
        return strpos($this->FileContent, '<coordinates') !== false;
    }
}
