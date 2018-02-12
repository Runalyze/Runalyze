<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;

class Sml extends XmlSuunto
{
    public function setFileContent($content)
    {
        $this->setFileContentInTrait($content);
    }

    public function parse()
    {
        if (!property_exists($this->Xml, 'DeviceLog')) {
            throw new UnsupportedFileException('Given XML object is not from Suunto. &lt;DeviceLog&gt;--tag could not be located.');
        }

        $this->Xml = $this->Xml->DeviceLog;

        parent::parse();
    }

    protected function setHeader()
    {
        $this->Header = $this->Xml->Header;
    }
}
