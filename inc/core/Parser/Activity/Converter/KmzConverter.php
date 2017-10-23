<?php

namespace Runalyze\Parser\Activity\Converter;

class KmzConverter extends ZipConverter
{
    public function __construct()
    {
        parent::__construct(['kml']);
    }

    public function getConvertibleFileExtension()
    {
        return 'kmz';
    }

    protected function isFileExtensionAllowed($extension)
    {
        return 'kml' == mb_strtolower($extension);
    }
}
